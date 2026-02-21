<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Combie;
use App\Models\Attendance;
use App\Models\School;
use App\Models\Fee;
use App\Models\FeeInstallment;
use App\Models\OtherFeeDetail;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\ParentModel;
use App\Models\StudentFeePayment;
use App\Services\SmsService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class FeesController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new SmsService();
    }

    /**
     * Get current academic year ID for a school
     * Returns the active academic year ID, or the current year's academic year ID if found
     */
    private function getCurrentAcademicYearID($schoolID, $requestedYear = null)
    {
        $yearToUse = $requestedYear ?: date('Y');

        // 1. Try to get the specific year record
        $academicYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('year', $yearToUse)
            ->first();

        if ($academicYear) {
            return $academicYear->academic_yearID;
        }

        // 2. If not found, try to get ANY active year
        $activeYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('year', 'desc')
            ->first();

        if ($activeYear) {
            return $activeYear->academic_yearID;
        }

        // 3. Fallback: Get most recent year regardless of status
        $mostRecent = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->orderBy('year', 'desc')
            ->first();

        if ($mostRecent) {
            return $mostRecent->academic_yearID;
        }

        // 4. Final resort: Create the year record automatically so the user is never blocked
        try {
            return DB::table('academic_years')->insertGetId([
                'schoolID' => $schoolID,
                'year' => $yearToUse,
                'year_name' => (string)$yearToUse,
                'start_date' => $yearToUse . '-01-01',
                'end_date' => $yearToUse . '-12-31',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-create academic year: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get previous year outstanding balance (debt) for a student
     * Returns array with 'school_fee_balance' and 'other_contribution_balance'
     */
    private function getPreviousYearBalance($studentID, $schoolID)
    {
        // Get the most recent closed academic year (regardless of current year)
        $closedAcademicYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Closed')
            ->orderBy('year', 'desc')
            ->first();

        if (!$closedAcademicYear) {
            // No closed academic year found, return zero balance
            return ['school_fee_balance' => 0, 'other_contribution_balance' => 0];
        }

        // Get payments from history for closed academic year
        $previousYearPayments = DB::table('payments_history')
            ->where('studentID', $studentID)
            ->where('academic_yearID', $closedAcademicYear->academic_yearID)
            ->get();

        // Calculate outstanding balances (only positive balances are debts)
        $schoolFeeBalance = 0;
        $otherContributionBalance = 0;

        foreach ($previousYearPayments as $payment) {
            $balance = (float) $payment->balance;

            // Only count positive balances as debt
            if ($balance > 0) {
                // In unified system, we aggregate all balance into schoolFeeBalance
                // unless we have a specific way to differentiate historical other fees
                $schoolFeeBalance += $balance;
            }
        }

        return [
            'school_fee_balance' => max(0, $schoolFeeBalance), // Ensure non-negative
            'other_contribution_balance' => max(0, $otherContributionBalance) // Ensure non-negative
        ];
    }
    public function manage_fees()
    {
      $userType = Session::get('user_type');
      $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get all classes for the school
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        // Get all fees with class information and relationships
        $fees = Fee::where('schoolID', $schoolID)
            ->with(['class', 'otherFeeDetails'])
            ->orderBy('classID')
            ->orderBy('display_order')
            ->orderBy('fee_name')
            ->get();

        // Group fees by class for widgets
        $feesByClass = $fees->groupBy('classID');

        return view('Admin.manageFees', compact('classes', 'fees', 'feesByClass'));
    }

    public function store_fee(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'classID' => 'required|exists:classes,classID',
            'duration' => 'required|in:Year,Month,Term,Semester,One-time',
            'fees' => 'required|array|min:1',
            'fees.*.name' => 'required|string|max:200',
            'fees.*.amount' => 'required|numeric|min:0',
            'fees.*.description' => 'nullable|string',
            'fees.*.must_start_pay' => 'nullable|boolean',
            'fees.*.deadline_amount' => 'nullable|numeric|min:0',
            'fees.*.deadline_date' => 'nullable|date',
            'allow_installments' => 'nullable|boolean',
            'default_installment_type' => 'nullable|in:Semester,Month,Two Months,Term,Quarter,One-time',
            'number_of_installments' => 'nullable|integer|min:1|max:12',
            'allow_partial_payment' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $createdFees = [];
            foreach ($request->fees as $index => $feeData) {
                $fee = Fee::create([
                    'schoolID' => $schoolID,
                    'classID' => $request->classID,
                    'fee_name' => $feeData['name'] ?? 'Fee',
                    'amount' => $feeData['amount'] ?? 0,
                    'must_start_pay' => isset($feeData['must_start_pay']) && $feeData['must_start_pay'] == 1,
                    'payment_deadline_amount' => $feeData['deadline_amount'] ?? null,
                    'payment_deadline_date' => $feeData['deadline_date'] ?? null,
                    'display_order' => $index,
                    'duration' => $request->duration,
                    'description' => $feeData['description'] ?? null,
                    'status' => 'Active',
                    'allow_installments' => $request->has('allow_installments'),
                    'default_installment_type' => $request->input('default_installment_type'),
                    'number_of_installments' => $request->input('number_of_installments'),
                    'allow_partial_payment' => $request->has('allow_partial_payment'),
                ]);

                // Create installments if allowed
                if ($request->has('allow_installments') && $request->input('number_of_installments')) {
                    $numInstallments = $request->input('number_of_installments');
                    $installmentType = $request->input('default_installment_type', 'Installment');
                    $amountPerInstallment = $fee->amount / $numInstallments;

                    for ($i = 1; $i <= $numInstallments; $i++) {
                        FeeInstallment::create([
                            'feeID' => $fee->feeID,
                            'installment_name' => $installmentType . ' ' . $i,
                            'installment_type' => $installmentType,
                            'installment_number' => $i,
                            'amount' => $amountPerInstallment,
                            'status' => 'Active',
                        ]);
                    }
                }
                $createdFees[] = $fee;
            }

            // Trigger Billing for all students in this class
            $this->generateBillsForClass($request->classID, $schoolID);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdFees) . ' Fees saved and assigned to students successfully.',
                'fees' => $createdFees
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee Assignment Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save fees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to generate/update billing for all students in a class
     */
    private function generateBillsForClass($classID, $schoolID)
    {
        $academicYearID = $this->getCurrentAcademicYearID($schoolID);
        if (!$academicYearID) return;

        $students = Student::where('schoolID', $schoolID)
            ->whereHas('subclass', function($q) use ($classID) {
                $q->where('classID', $classID);
            })
            ->where('status', 'Active')
            ->get();

        foreach ($students as $student) {
            $this->generateStudentBill($student, $academicYearID, $schoolID);
        }
    }

    /**
     * Helper to generate/update a specific student's bill (Single Control Number)
     */
    private function generateStudentBill($student, $academicYearID, $schoolID)
    {
        // 1. Get all active fees for student's class
        $fees = Fee::where('schoolID', $schoolID)
            ->where('classID', $student->subclass->classID)
            ->where('status', 'Active')
            ->orderBy('display_order')
            ->get();

        // 2. Calculate totals
        $totalAmount = $fees->sum('amount');
        $mustPayAmount = $fees->where('must_start_pay', true)->sum('amount');

        $previousYearBalance = $this->getPreviousYearBalance($student->studentID, $schoolID);
        $debt = $previousYearBalance['school_fee_balance'] + $previousYearBalance['other_contribution_balance'];

        $totalRequiredAmount = $totalAmount + $debt;

        // Apply sponsorship discount if applicable
        if ($student->sponsor_id && $student->sponsorship_percentage > 0) {
            $sponsorshipDiscount = ($totalRequiredAmount * $student->sponsorship_percentage) / 100;
            $totalRequiredAmount = $totalRequiredAmount - $sponsorshipDiscount;
        }

        // 3. Get existing control number or generate new one
        $payment = Payment::where('studentID', $student->studentID)
            ->where('academic_yearID', $academicYearID)
            ->first();

        if (!$payment) {
            // New Control Number Logic (Strictly numeric, starts with 3345)
            $controlNumber = $this->generateControlNumber($schoolID, $student->studentID);

            $payment = Payment::create([
                'schoolID' => $schoolID,
                'academic_yearID' => $academicYearID,
                'studentID' => $student->studentID,
                'control_number' => $controlNumber,
                'amount_required' => $totalRequiredAmount,
                'amount_paid' => 0,
                'balance' => $totalRequiredAmount,
                'debt' => $debt,
                'required_fees_amount' => $mustPayAmount,
                'required_fees_paid' => 0,
                'can_start_school' => $mustPayAmount <= 0,
                'payment_status' => 'Pending'
            ]);
        } else {
            // Update existing bill if fees changed
            $payment->update([
                'amount_required' => $totalRequiredAmount,
                'balance' => $totalRequiredAmount - $payment->amount_paid,
                'debt' => $debt,
                'required_fees_amount' => $mustPayAmount,
            ]);
        }

        // 4. Update individual fee payment tracking entries
        foreach ($fees as $fee) {
            $sfp = StudentFeePayment::where('studentID', $student->studentID)
                ->where('feeID', $fee->feeID)
                ->where('paymentID', $payment->paymentID)
                ->first();

            if (!$sfp) {
                StudentFeePayment::create([
                    'schoolID' => $schoolID,
                    'studentID' => $student->studentID,
                    'paymentID' => $payment->paymentID,
                    'feeID' => $fee->feeID,
                    'fee_name' => $fee->fee_name,
                    'fee_total_amount' => $fee->amount,
                    'amount_paid' => 0,
                    'balance' => $fee->amount,
                    'is_required' => $fee->must_start_pay,
                    'display_order' => $fee->display_order,
                ]);
            } else {
                $sfp->update([
                    'paymentID' => $payment->paymentID,
                    'fee_total_amount' => $fee->amount,
                    'balance' => $fee->amount - $sfp->amount_paid,
                    'is_required' => $fee->must_start_pay,
                    'display_order' => $fee->display_order,
                ]);
            }
        }
    }


    public function update_fee(Request $request, $feeID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $fee = Fee::where('feeID', $feeID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

    $validator = Validator::make($request->all(), [
        'classID' => 'required|exists:classes,classID',
        'fee_name' => 'required|string|max:200',
        'amount' => 'required|numeric|min:0',
        'must_start_pay' => 'nullable|boolean',
        'payment_deadline_amount' => 'nullable|numeric|min:0',
        'payment_deadline_date' => 'nullable|date',
        'duration' => 'required|in:Year,Month,Term,Semester,One-time',
        'description' => 'nullable|string',
        'allow_installments' => 'nullable|boolean',
        'default_installment_type' => 'nullable|in:Semester,Month,Two Months,Term,Quarter,One-time',
        'number_of_installments' => 'nullable|integer|min:1|max:12',
        'allow_partial_payment' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $fee->update([
            'classID' => $request->classID,
            'fee_name' => $request->fee_name,
            'amount' => $request->amount,
            'must_start_pay' => $request->has('must_start_pay'),
            'payment_deadline_amount' => $request->payment_deadline_amount,
            'payment_deadline_date' => $request->payment_deadline_date,
            'duration' => $request->duration,
            'description' => $request->description,
            'allow_installments' => $request->has('allow_installments'),
            'default_installment_type' => $request->input('default_installment_type'),
            'number_of_installments' => $request->input('number_of_installments'),
            'allow_partial_payment' => $request->has('allow_partial_payment'),
        ]);

        // Delete existing installments and create new ones if allowed
        FeeInstallment::where('feeID', $fee->feeID)->delete();

        if ($request->has('allow_installments') && $request->input('number_of_installments') && $request->input('default_installment_type')) {
            $installmentType = $request->input('default_installment_type');
            $numberOfInstallments = $request->input('number_of_installments');
            $amountPerInstallment = $request->amount / $numberOfInstallments;

            for ($i = 1; $i <= $numberOfInstallments; $i++) {
                FeeInstallment::create([
                    'feeID' => $fee->feeID,
                    'installment_name' => $installmentType . ' ' . $i,
                    'installment_type' => $installmentType,
                    'installment_number' => $i,
                    'amount' => $amountPerInstallment,
                    'status' => 'Active',
                ]);
            }
        }

        // Update bills for this class
        $this->generateBillsForClass($request->classID, $schoolID);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Fee updated successfully',
            'fee' => $fee->load(['class', 'installments'])
        ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update fee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_fee($feeID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $fee = Fee::where('feeID', $feeID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

        try {
            $fee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fee deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete fee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggle_fee_status($feeID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $fee = Fee::where('feeID', $feeID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

        try {
            $fee->status = $fee->status === 'Active' ? 'Inactive' : 'Active';
            $fee->save();

            return response()->json([
                'success' => true,
                'message' => 'Fee status updated successfully',
                'fee' => $fee
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update fee status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_fee($feeID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $fee = Fee::where('feeID', $feeID)
            ->where('schoolID', $schoolID)
            ->with(['class', 'installments', 'otherFeeDetails'])
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

        // Format other fee details for JSON response
        $fee->other_fee_details = $fee->otherFeeDetails->map(function($detail) {
            return [
                'detailID' => $detail->detailID,
                'fee_detail_name' => $detail->fee_detail_name,
                'amount' => (float) $detail->amount,
                'description' => $detail->description,
            ];
        });

        return response()->json([
            'success' => true,
            'fee' => $fee
        ], 200);
    }

    /**
     * View payments and control numbers
     */
    public function view_payments()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get school info
        $school = School::find($schoolID);

        // Get academic years from academic_years table (Active and Closed for dropdown)
        $academicYears = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->orderBy('year', 'desc')
            ->get();

        // Format academic years for dropdown (show both Active and Closed)
        $availableYears = [];
        foreach ($academicYears as $academicYear) {
            $availableYears[] = [
                'year' => $academicYear->year,
                'year_name' => $academicYear->year_name,
                'status' => $academicYear->status,
                'academic_yearID' => $academicYear->academic_yearID,
            ];
        }

        // Get current year and next year
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        // Default year: current calendar year
        $defaultYear = $currentYear;

        // Get all classes and subclasses for filters
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        $subclasses = Subclass::whereIn('classID', $classes->pluck('classID'))
            ->where('status', 'Active')
            ->orderBy('subclass_name')
            ->get();

        return view('Admin.viewPayments', compact('school', 'availableYears', 'defaultYear', 'currentYear', 'nextYear', 'classes', 'subclasses'));
    }

    /**
     * Get payments data via AJAX with filtering
     */
    public function get_payments_ajax(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Get filter parameters
            $classID = $request->input('class_id', '');
            $subclassID = $request->input('subclass_id', '');
            $studentStatus = $request->input('student_status', ''); // Active or Graduated
            $paymentStatus = $request->input('payment_status', '');
            $year = $request->input('year', date('Y')); // This is the year value from dropdown
            $searchStudentName = $request->input('search_student_name', '');
            $sponsorshipFilter = $request->input('sponsorship_filter', ''); // 'sponsored' or 'self'

            // Get academic_yearID for the selected year
            $academicYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('year', $year)
                ->first();

            $academicYearID = $academicYear ? $academicYear->academic_yearID : null;
            $isClosedYear = $academicYear && $academicYear->status === 'Closed';

            // If no academic year found for selected year, try to get active academic year
            if (!$academicYearID) {
                $activeAcademicYear = DB::table('academic_years')
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->orderBy('year', 'desc')
                    ->first();
                if ($activeAcademicYear) {
                    $academicYearID = $activeAcademicYear->academic_yearID;
                    $year = $activeAcademicYear->year; // Update year to match active academic year
                    $isClosedYear = false;
                }
            }

            // Build query - default to Active students only to match school expectations
            $statusFilter = ['Active'];
            if (!empty($studentStatus)) {
                $statusFilter = [$studentStatus];
            } else if ($studentStatus === 'All') {
                $statusFilter = ['Active', 'Graduated'];
            }

            $query = Student::where('schoolID', $schoolID)
                ->whereIn('status', $statusFilter)
                ->with(['parent', 'sponsor', 'subclass.class', 'payments' => function($q) use ($academicYearID, $year) {
                    if ($academicYearID) {
                        // Filter by academic_yearID if academic year exists
                        $q->where('academic_yearID', $academicYearID);
                    } else {
                        // Fallback to year filter if academic year doesn't exist
                        $q->whereYear('created_at', $year);
                    }
                }, 'payments.fee_payments', 'payments.academicYear', 'payments.paymentRecords']);

            // Filter by class (main class)
            if (!empty($classID)) {
                $query->whereHas('subclass', function($q) use ($classID) {
                    $q->where('classID', $classID);
                });
            }

            // Filter by subclass
            if (!empty($subclassID)) {
                $query->where('subclassID', $subclassID);
            }

            // Filter by sponsorship
            if ($sponsorshipFilter === 'sponsored') {
                $query->whereNotNull('sponsor_id')
                      ->where('sponsorship_percentage', '>', 0);
            } elseif ($sponsorshipFilter === 'self') {
                $query->where(function($q) {
                    $q->whereNull('sponsor_id')
                      ->orWhere('sponsorship_percentage', '<=', 0);
                });
            }

            // Filter by student name search
            if (!empty($searchStudentName)) {
                $query->where(function($q) use ($searchStudentName) {
                    $q->where('first_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhere('last_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhere('middle_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchStudentName . '%'])
                      ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ['%' . $searchStudentName . '%']);
                });
            }

            $students = $query->orderBy('first_name')->get();

            // Group payments by student and aggregate totals
            $filteredData = [];
            $index = 1; // Initialize index
            foreach ($students as $student) {
                // Get the unified payment record for this student and year
                $payment = $student->payments->first(); // Should be only one due to generateStudentBill logic

                if (!$payment && !empty($paymentStatus)) {
                    if ($paymentStatus !== "Pending") continue;
                }

                if ($payment && !empty($paymentStatus)) {
                    $status = $payment->payment_status;
                    if ($paymentStatus === 'Incomplete' || $paymentStatus === 'Partial' || $paymentStatus === 'Incomplete Payment') {
                        if ($status !== 'Incomplete Payment' && $status !== 'Partial') continue;
                    } elseif ($status !== $paymentStatus) {
                        continue;
                    }
                }

                // Calculate verification status
                $hasUnverified = $payment ? $payment->paymentRecords()->where('is_verified', false)->exists() : false;
                $hasVerified = $payment ? $payment->paymentRecords()->where('is_verified', true)->exists() : false;

                $verificationStatus = 'Unverified';
                if ($payment) {
                    if ($payment->amount_paid == 0) {
                        $verificationStatus = 'No Payment';
                    } elseif (!$hasUnverified && $hasVerified) {
                        $verificationStatus = 'Verified';
                    } elseif ($hasUnverified && $hasVerified) {
                        $verificationStatus = 'Partially Verified';
                    } else {
                        $verificationStatus = 'Pending Verification';
                    }
                }

                $studentData = [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'status' => $student->status,
                    'photo' => $student->photo ? asset('userImages/' . $student->photo) : null,
                    'parent' => $student->parent ? [
                        'first_name' => $student->parent->first_name,
                        'last_name' => $student->parent->last_name,
                        'phone' => $student->parent->phone,
                    ] : null,
                    'subclass' => [
                        'subclass_name' => $student->subclass->subclass_name ?? 'N/A',
                        'class_name' => $student->subclass->class->class_name ?? 'N/A',
                    ],
                    'sponsor' => $student->sponsor ? [
                        'sponsor_name' => $student->sponsor->sponsor_name,
                        'contact_person' => $student->sponsor->contact_person,
                        'phone' => $student->sponsor->phone,
                        'email' => $student->sponsor->email,
                        'percentage' => $student->sponsorship_percentage,
                    ] : null,
                ];

                $totalRequired = $payment ? $payment->amount_required : 0;
                $totalPaid = $payment ? $payment->amount_paid : 0;
                $totalBalance = $payment ? $payment->balance : 0;
                $requiredPaid = $payment ? $payment->required_fees_paid : 0;
                $requiredAmount = $payment ? $payment->required_fees_amount : 0;
                $canStartSchool = $payment ? $payment->can_start_school : false;

                // Calculate sponsorship amounts if applicable
                $originalTotal = $totalRequired;
                $sponsorAmount = 0;
                if ($payment && $student->sponsor_id && $student->sponsorship_percentage > 0) {
                    // Original total is the sum of fee amounts + debt
                    $baseFeesSum = $payment->fee_payments ? $payment->fee_payments->sum('fee_total_amount') : 0;
                    $originalTotal = $baseFeesSum + ($payment->debt ?? 0);
                    $sponsorAmount = $originalTotal - $totalRequired;
                }

                $filteredData[] = [
                    'index' => $index++,
                    'student' => $studentData,
                    'payment' => $payment,
                    'totals' => [
                        'total_required' => $totalRequired,
                        'total_paid' => $totalPaid,
                        'total_balance' => $totalBalance,
                        'required_paid' => $requiredPaid,
                        'required_amount' => $requiredAmount,
                        'can_start_school' => $canStartSchool,
                        'original_total' => $originalTotal,
                        'sponsor_amount' => $sponsorAmount,
                        'overall_status' => $payment ? $payment->payment_status : 'Pending',
                    ],
                    'payment_status' => $payment ? $payment->payment_status : 'Pending',
                    'verification_status' => $verificationStatus,
                    'academic_year' => $year,
                ];
            }
            $totalRequired = 0;
            $totalPaid = 0;
            $totalBalance = 0;
            $pendingPayments = 0;
            $incompletePayments = 0;
            $paidPayments = 0;
            $overpaidPayments = 0;

            foreach ($filteredData as $item) {
                if (isset($item['totals'])) {
                    $totals = $item['totals'];
                    $totalRequired += $totals['total_required'] ?? 0;
                    $totalPaid += $totals['total_paid'] ?? 0;
                    $totalBalance += $totals['total_balance'] ?? 0;

                    $status = $item['payment_status'] ?? 'Pending';
                    if ($status === 'Pending' || $status === 'No Billing') {
                        $pendingPayments++;
                    } elseif ($status === 'Incomplete Payment' || $status === 'Partial') {
                        $incompletePayments++;
                    } elseif ($status === 'Paid') {
                        $paidPayments++;
                    } elseif ($status === 'Overpaid') {
                        $overpaidPayments++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $filteredData,
                'academic_year' => $year,
                'statistics' => [
                    'total_students' => count($filteredData),
                    'total_payments' => count($filteredData),
                    'pending_payments' => $pendingPayments,
                    'incomplete_payments' => $incompletePayments,
                    'paid_payments' => $paidPayments,
                    'overpaid_payments' => $overpaidPayments,
                    'total_required' => $totalRequired,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting payments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate control numbers for all active students (separate for Tuition and Other Fees)
     */
    public function generate_control_numbers(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            DB::beginTransaction();

            $requestedYear = $request->input('year');
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);

            if (!$academicYearID) {
                return response()->json(['success' => false, 'message' => 'No academic year found. Please set an academic year first.'], 404);
            }

            // Get the year value for logging
            $academicYear = DB::table('academic_years')->where('academic_yearID', $academicYearID)->first();
            $yearValue = $academicYear ? $academicYear->year : 'N/A';

            // Get all active students
            $students = Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->get();

            if ($students->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No active students found.'], 404);
            }

            $generated = 0;
            $skipped = 0;
            foreach ($students as $student) {
                // Check if student already has a control number for this academic year
                $existingPayment = Payment::where('studentID', $student->studentID)
                    ->where('academic_yearID', $academicYearID)
                    ->whereNotNull('control_number')
                    ->where('control_number', '!=', '')
                    ->first();

                if (!$existingPayment) {
                    // generateStudentBill updates if exists, creates if not
                    $this->generateStudentBill($student, $academicYearID, $schoolID);
                    $generated++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            $msg = "Control numbers generated for {$generated} new students for year {$yearValue}!";
            if ($skipped > 0) {
                $msg .= " ({$skipped} students skipped as they already have them)";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'generated' => $generated,
                'skipped' => $skipped
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating control numbers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate unique control number
     * Format: 3345XXXXX (9 digits total, starts with 3345, only digits)
     */
    private function generateControlNumber($schoolID, $studentID)
    {
        do {
            // Generate 5 random digits (0-9)
            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Format: 3345 + 5 random digits = 9 digits total
            $controlNumber = '3345' . $randomDigits;

            // Check if control number already exists in active payments
            $existsActive = DB::table('payments')->where('control_number', $controlNumber)->exists();

            // Check if control number exists in history
            $existsHistory = DB::table('payments_history')->where('control_number', $controlNumber)->exists();

        } while ($existsActive || $existsHistory);

        return $controlNumber;
    }

    /**
     * Send control numbers to all parents via SMS
     */
    public function send_control_numbers_sms(Request $request)
    {
        set_time_limit(3600); // 60 minutes
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $school = School::find($schoolID);
            if (!$school) {
                return response()->json(['success' => false, 'message' => 'School not found'], 404);
            }

            // Get parameters
            $requestedYear = $request->input('year');
            $recipient = $request->input('recipient', 'parent'); // 'parent' | 'sponsor' | 'both'
            $targetStatus = $request->input('target_payment_status', ''); // '', 'no_payment', 'incomplete', 'overpaid', 'pending'
            $sponsorshipFilter = $request->input('sponsorship_filter', ''); // 'sponsored' | 'self' | ''

            // Resolve academic year
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);

            // Base query for payments
            $paymentsQuery = Payment::where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->with(['student.parent', 'student.sponsor', 'fee_payments']);

            // Default previous behavior: only send new and not fully paid, when no explicit target provided
            if (empty($targetStatus)) {
                $paymentsQuery->where('sms_sent', 'No')
                              ->where('payment_status', '!=', 'Paid');
            }

            // Filter by sponsorship if requested
            if ($sponsorshipFilter === 'sponsored') {
                $paymentsQuery->whereHas('student', function($q) {
                    $q->whereNotNull('sponsor_id')->where('sponsorship_percentage', '>', 0);
                });
            } elseif ($sponsorshipFilter === 'self') {
                $paymentsQuery->whereHas('student', function($q) {
                    $q->whereNull('sponsor_id')->orWhere('sponsorship_percentage', '<=', 0);
                });
            }

            $payments = $paymentsQuery->get();

            if ($payments->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No new control numbers to send.'], 404);
            }

            $sent    = 0;
            $failed  = 0;
            $results = [];

            foreach ($payments as $payment) {
                $student = $payment->student;
                if (!$student) { $failed++; continue; }

                // Apply target payment status filter if provided
                if (!empty($targetStatus)) {
                    $statusOk = true;
                    $amtRequired = (float) ($payment->amount_required ?? 0);
                    $amtPaid = (float) ($payment->amount_paid ?? 0);
                    $status = $payment->payment_status ?? 'Pending';

                    switch ($targetStatus) {
                        case 'no_payment':
                            $statusOk = ($amtPaid <= 0.0);
                            break;
                        case 'incomplete':
                            $statusOk = ($amtPaid > 0.0 && $amtPaid < $amtRequired) || in_array($status, ['Incomplete Payment', 'Partial']);
                            break;
                        case 'overpaid':
                            $statusOk = ($amtPaid > $amtRequired) || $status === 'Overpaid';
                            break;
                        case 'pending':
                            $statusOk = $status === 'Pending' || $status === 'No Billing';
                            break;
                        case 'paid':
                            $statusOk = $status === 'Paid';
                            break;
                        default:
                            $statusOk = true;
                    }
                    if (!$statusOk) { continue; }
                }

                $parent = $student->parent ?? null;
                $sponsor = $student->sponsor ?? null;

                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                $controlNumber = $payment->control_number;
                $requiredAmount = number_format($payment->required_fees_amount ?? 0, 0);

                // Determine recipients based on requested recipient mode
                $sendToSponsor = false;
                $sendToParent = false;
                if ($recipient === 'auto') {
                    $sendToSponsor = ($sponsor && !empty($sponsor->phone) && (float)($student->sponsorship_percentage ?? 0) > 0);
                    $sendToParent = !$sendToSponsor && ($parent && !empty($parent->phone));
                } elseif ($recipient === 'sponsor') {
                    $sendToSponsor = true;
                } elseif ($recipient === 'parent') {
                    $sendToParent = true;
                } elseif ($recipient === 'both') {
                    $sendToSponsor = true;
                    $sendToParent = true;
                }

                // Compute original total and sponsorship shares
                $baseFeesSum = $payment->fee_payments ? $payment->fee_payments->sum('fee_total_amount') : 0;
                $originalTotal = ($baseFeesSum + ($payment->debt ?? 0)) ?: ($payment->amount_required ?? 0);
                $p = (float) ($student->sponsorship_percentage ?? 0);
                $sponsorShareRaw = $p > 0 ? ($originalTotal * $p / 100.0) : 0;
                $sponsorShare = (int) round($sponsorShareRaw, 0);
                $parentShare = (int) max(0, round($originalTotal - $sponsorShare, 0));

                $totalAmountForMsg = number_format($originalTotal, 0);
                $parentShareMsg = number_format($parentShare, 0);
                $sponsorShareMsg = number_format($sponsorShare, 0);

                $sentAtLeastOne = false;
                $rowResult = [
                    'student_name' => 'Parent of ' . $studentName,
                    'phone'        => null,
                    'success'      => false,
                ];

                // Send to sponsor if requested and available
                if ($sendToSponsor && $sponsor && $sponsor->phone && $p > 0) {
                    $sponsorPct = (int)$p;
                    $messageSponsor = "Mdhamini wa {$studentName}, Control Number: {$controlNumber}. Lipa TZS {$sponsorShareMsg} ({$sponsorPct}% ya TZS {$totalAmountForMsg}). Lipia benki/mitandao.";
                    $res = $this->smsService->sendSms($sponsor->phone, $messageSponsor);
                    $rowResult['phone']   = $sponsor->phone;
                    $rowResult['success'] = $res['success'];
                    if ($res['success']) { $sent++; $sentAtLeastOne = true; } else { $failed++; }
                }

                // Send to parent if requested and available
                if ($sendToParent && $parent && $parent->phone) {
                    if ($p > 0) {
                        $messageParent = "Mzazi wa {$studentName}, CN: {$controlNumber}. Jumla TZS {$totalAmountForMsg}, ulipe TZS {$parentShareMsg} (mdhamini TZS {$sponsorShareMsg}). Kuanza: TZS {$requiredAmount}.";
                    } else {
                        $totalAmount = number_format($payment->amount_required ?? 0, 0);
                        $messageParent = "Mzazi wa {$studentName}, amepangiwa CN: {$controlNumber}. Kiasi: TZS {$totalAmount}, kuanza: TZS {$requiredAmount}. Lipa kuepuka usumbufu.";
                    }
                    $res = $this->smsService->sendSms($parent->phone, $messageParent);
                    $rowResult['phone']   = $parent->phone;
                    $rowResult['success'] = $res['success'];
                    if ($res['success']) { $sent++; $sentAtLeastOne = true; } else { $failed++; }
                }

                $results[] = $rowResult;

                if ($sentAtLeastOne) {
                    $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                } else {
                    // If neither parent nor sponsor had a phone, count as failed once
                    if (!(($parent && $parent->phone) || ($sponsor && $sponsor->phone))) {
                        $failed++;
                        $rowResult['success'] = false;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "SMS zimetumwa! Imefanikiwa: {$sent}, Imefeli: {$failed}",
                'sent'    => $sent,
                'failed'  => $failed,
                'results' => $results,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send debt reminders to all parents with outstanding balances
     */
    public function send_debt_reminders_sms(Request $request)
    {
        set_time_limit(3600); // 60 minutes
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $school = DB::table('schools')->where('schoolID', $schoolID)->first();
            if (!$school) {
                return response()->json(['success' => false, 'message' => 'School not found'], 404);
            }

            // Get requested year or active year
            $requestedYear = $request->input('year');
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);

            // Get all payments with balance > 0
            $payments = \App\Models\Payment::where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->where('balance', '>', 0)
                ->with(['student.parent'])
                ->get();

            if ($payments->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Hakuna mwanafunzi mwenye deni la kutumiwa ujumbe.'], 404);
            }

            $sent    = 0;
            $failed  = 0;
            $results = [];

            foreach ($payments as $payment) {
                $student = $payment->student;
                $parent = $student ? $student->parent : null;

                if (!$parent || !$parent->phone) {
                    $failed++;
                    continue;
                }

                $studentName   = ($student->first_name ?? '') . ' ' . ($student->last_name ?? '');
                $controlNumber = $payment->control_number;
                $balance       = number_format($payment->balance, 0);

                // Short Kiswahili Message for Debt Reminder (≤163 chars)
                $message = "Mzazi wa {$studentName} unakumbushwa kulipa deni, sh {$balance}, control number {$controlNumber}. Lipa kuepuka usumbufu.";

                // Send SMS using the service
                $smsResult = $this->smsService->sendSms($parent->phone, $message);

                $results[] = [
                    'student_name' => 'Parent of ' . $studentName,
                    'phone'        => $parent->phone,
                    'success'      => $smsResult['success'],
                ];

                if ($smsResult['success']) {
                    $sent++;
                } else {
                    $failed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "SMS za kukumbusha deni zimetumwa! Imefanikiwa: {$sent}, Imefeli: {$failed}",
                'sent'    => $sent,
                'failed'  => $failed,
                'results' => $results,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending debt reminders: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get list of recipients (parents/sponsors) for the fee SMS modal
     * type: 'control_number' or 'debt_reminder'
     */
    public function get_fee_sms_recipients(Request $request)
    {
        $schoolID  = Session::get('schoolID');
        $userType  = Session::get('user_type');
        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $type              = $request->input('type', 'control_number'); // 'control_number' | 'debt_reminder'
        $requestedYear     = $request->input('year');
        $sponsorshipFilter = $request->input('sponsorship_filter', '');

        try {
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);

            $query = Payment::where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->with(['student.parent', 'student.sponsor']);

            if ($type === 'debt_reminder') {
                $query->where('balance', '>', 0);
            }

            // Sponsorship filter
            if ($sponsorshipFilter === 'sponsored') {
                $query->whereHas('student', fn($q) => $q->whereNotNull('sponsor_id')->where('sponsorship_percentage', '>', 0));
            } elseif ($sponsorshipFilter === 'self') {
                $query->whereHas('student', fn($q) => $q->whereNull('sponsor_id')->orWhere('sponsorship_percentage', '<=', 0));
            }

            $payments = $query->get();

            $recipients = [];
            foreach ($payments as $payment) {
                $student = $payment->student;
                if (!$student) continue;

                $parent  = $student->parent  ?? null;
                $sponsor = $student->sponsor ?? null;
                $p       = (float)($student->sponsorship_percentage ?? 0);

                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                $balance     = $payment->balance ?? 0;
                $cn          = $payment->control_number ?? '';

                // Determine phone to display (sponsor first if auto)
                $phone = null;
                $recipientLabel = 'Parent of ' . $studentName;
                if ($p > 0 && $sponsor && $sponsor->phone) {
                    $phone          = $sponsor->phone;
                    $recipientLabel = 'Sponsor of ' . $studentName;
                } elseif ($parent && $parent->phone) {
                    $phone          = $parent->phone;
                    $recipientLabel = 'Parent of ' . $studentName;
                }

                $recipients[] = [
                    'paymentID'      => $payment->paymentID,
                    'recipientLabel' => $recipientLabel,
                    'studentName'    => $studentName,
                    'phone'          => $phone ?? '',
                    'balance'        => $balance,
                    'controlNumber'  => $cn,
                ];
            }

            return response()->json(['success' => true, 'recipients' => $recipients]);

        } catch (\Exception $e) {
            Log::error('get_fee_sms_recipients error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a single fee SMS (control number OR debt reminder) for one payment.
     * Called per-row by the JS async loop (same pattern as send_result_sms).
     */
    public function send_single_fee_sms(Request $request)
    {
        set_time_limit(120);
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');
        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $paymentID = $request->input('paymentID');
        $type      = $request->input('type', 'control_number'); // 'control_number' | 'debt_reminder'

        try {
            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->with(['student.parent', 'student.sponsor', 'school'])
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            $student = $payment->student;
            $parent  = $student ? $student->parent  : null;
            $sponsor = $student ? $student->sponsor : null;
            $school  = $payment->school;

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            $studentName    = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            $cn             = $payment->control_number ?? '';
            $p              = (float)($student->sponsorship_percentage ?? 0);
            $balance        = number_format($payment->balance ?? 0, 0);
            $totalAmount    = number_format($payment->amount_required ?? 0, 0);
            $requiredAmount = number_format($payment->required_fees_amount ?? 0, 0);

            // Determine phone (sponsor first if sponsored)
            $phone = null;
            if ($p > 0 && $sponsor && $sponsor->phone) {
                $phone = $sponsor->phone;
            } elseif ($parent && $parent->phone) {
                $phone = $parent->phone;
            }

            if (!$phone) {
                return response()->json(['success' => false, 'message' => 'No phone number found'], 404);
            }

            // Build message based on type
            if ($type === 'debt_reminder') {
                $message = "Mzazi wa {$studentName} unakumbushwa kulipa deni, sh {$balance}, control number {$cn}. Lipa kuepuka usumbufu.";
            } else {
                // control_number
                if ($p > 0) {
                    $sponsorShare  = number_format(round($payment->amount_required * $p / 100), 0);
                    $parentShare   = number_format(max(0, ($payment->amount_required ?? 0) - round(($payment->amount_required ?? 0) * $p / 100)), 0);
                    $message = "Mzazi wa {$studentName}, CN: {$cn}. Jumla TZS {$totalAmount}, ulipe TZS {$parentShare} (mdhamini TZS {$sponsorShare}). Kuanza: TZS {$requiredAmount}.";
                } else {
                    $message = "Mzazi wa {$studentName}, amepangiwa CN: {$cn}. Kiasi: TZS {$totalAmount}, kuanza: TZS {$requiredAmount}. Lipa kuepuka usumbufu.";
                }
            }

            $smsResult = $this->smsService->sendSms($phone, $message);

            if ($smsResult['success']) {
                $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                return response()->json(['success' => true, 'message' => 'SMS sent successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => $smsResult['message'] ?? 'SMS failed.']);
            }

        } catch (\Exception $e) {
            Log::error('send_single_fee_sms error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Resend control number to specific parent
     */
    public function resend_control_number($paymentID)
    {
        set_time_limit(3600); // 60 minutes
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->with(['student.parent', 'school'])
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            $student = $payment->student;
            $parent = $student ? $student->parent : null;
            $school = $payment->school;

            if (!$student || !$student->parent || !$student->parent->phone) {
                return response()->json(['success' => false, 'message' => 'Parent or phone number not found'], 404);
            }

            $studentName = ($student->first_name ?? '') . ' ' . ($student->last_name ?? '');
            $controlNumber = $payment->control_number;
            $totalAmount = number_format($payment->amount_required, 0);
            $requiredAmount = number_format($payment->required_fees_amount, 0);

            // Shortered Message for Control Number
            $message = "Mzazi wa {$studentName}, mwanafunzi amepangiwa Control Number: {$controlNumber}. Kiasi kuanza: sh {$requiredAmount}, Jumla: sh {$totalAmount}. Lipa kuepuka usumbufu.";

            $smsResult = $this->smsService->sendSms($parent->phone, $message);

            if ($smsResult['success']) {
                $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                return response()->json(['success' => true, 'message' => 'Control Number resent successfully.'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to send SMS: ' . $smsResult['message']], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error resending control number: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send single debt reminder
     */
    public function send_single_debt_reminder(Request $request)
    {
        set_time_limit(3600);
        $paymentID = $request->input('paymentID');
        $schoolID = Session::get('schoolID');

        if (!$paymentID || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Missing data'], 400);
        }

        try {
            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->with(['student.parent'])
                ->first();

            if (!$payment || !$payment->student || !$payment->student->parent || !$payment->student->parent->phone) {
                return response()->json(['success' => false, 'message' => 'Student or parent phone not found'], 404);
            }

            $student = $payment->student;
            $parent = $student->parent;
            $studentName = ($student->first_name ?? '') . ' ' . ($student->last_name ?? '');
            $balance = number_format($payment->balance, 0);
            $controlNumber = $payment->control_number;

            // Shortered Message for Debt Reminder
            $message = "Mzazi wa {$studentName} unakumbushwa kulipa deni, sh {$balance}, control number {$controlNumber}. Lipa kuepuka usumbufu.";

            $smsResult = $this->smsService->sendSms($parent->phone, $message);

            if ($smsResult['success']) {
                $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                return response()->json(['success' => true, 'message' => 'Debt reminder sent successfully.'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to send SMS: ' . $smsResult['message']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error sending single debt reminder: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update payment status (when payment is received from bank)
     */
    public function update_payment_status(Request $request, $paymentID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount_paid' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // Create a payment record
            $paymentRecord = PaymentRecord::create([
                'paymentID' => $payment->paymentID,
                'paid_amount' => $request->amount_paid,
                'payment_date' => now(),
                'payment_source' => 'Bank/System',
                'reference_number' => $request->payment_reference,
                'notes' => $request->notes,
                'is_verified' => true,
                'verified_at' => now(),
            ]);

            // Allocate payment across fees
            $this->allocatePayment($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment received and allocated successfully.',
                'payment' => $payment->fresh(['student'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export Payment Invoice PDF
     */
    public function exportPaymentInvoicePDF($studentID)
    {
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');

        if (!$schoolID || !in_array($userType, ['Admin', 'Teacher'])) {
            return redirect()->back()->with('error', 'Access denied');
        }

        try {
            // Get student with relationships
            $student = Student::with(['parent', 'subclass.class', 'payments.fee.installments', 'payments.fee.otherFeeDetails'])
                ->where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return redirect()->back()->with('error', 'Student not found');
            }

            // Get school information
            $school = School::find($schoolID);
            if (!$school) {
                return redirect()->back()->with('error', 'School not found');
            }

            // Get active academic year
            $activeAcademicYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('year', 'desc')
                ->first();

            $currentYear = $activeAcademicYear ? $activeAcademicYear->year : date('Y');
            $academicYearID = $activeAcademicYear ? $activeAcademicYear->academic_yearID : null;

            // Get payments for active academic year - filter out empty/null payments
            $payments = $student->payments()
                ->where(function($query) use ($academicYearID, $currentYear) {
                    if ($academicYearID) {
                        $query->where('academic_yearID', $academicYearID);
                    } else {
                        $query->whereYear('created_at', $currentYear);
                    }
                })
                ->whereNotNull('control_number')
                ->where('control_number', '!=', '')
                ->with(['fee.installments', 'fee.otherFeeDetails'])
                ->get();

            // Get previous year debt for this student (only for active students)
            $previousYearDebt = ['school_fee_balance' => 0, 'other_contribution_balance' => 0];
            if ($student->status === 'Active') {
                $previousYearDebt = $this->getPreviousYearBalance($student->studentID, $schoolID);
            }

            // Calculate totals
            $payment = $payments->first(); // Get the first (unified) payment record
            $totalRequired = $payment ? $payment->amount_required : 0;
            $totalPaid = $payment ? $payment->amount_paid : 0;
            $totalBalance = $payment ? $payment->balance : 0;
            $requiredPaid = $payment ? $payment->required_fees_paid : 0;
            $requiredAmount = $payment ? $payment->required_fees_amount : 0;

            // Get fee breakdown
            $feeBreakdown = $payment ? $payment->fee_payments()->orderBy('display_order')->get() : collect();

            // School logo path
            $schoolLogo = $school->school_logo ? public_path($school->school_logo) : null;

            // Prepare data for PDF
            $data = [
                'student' => $student,
                'school' => $school,
                'payment' => $payment,
                'feeBreakdown' => $feeBreakdown,
                'totalRequired' => $totalRequired,
                'totalPaid' => $totalPaid,
                'totalBalance' => $totalBalance,
                'requiredAmount' => $requiredAmount,
                'requiredPaid' => $requiredPaid,
                'previousYearDebt' => $previousYearDebt,
                'schoolLogo' => $schoolLogo,
                'year' => $currentYear,
            ];

            // Generate PDF - try multiple methods for compatibility
            try {
                // Method 1: Try using the facade
                $pdf = PDF::loadView('Admin.pdf.payment_invoice', $data);
                $pdf->setPaper('A4', 'portrait');

                $filename = 'Payment_Invoice_' . str_replace(' ', '_', $student->admission_number) . '_' . $currentYear . '.pdf';

                return $pdf->download($filename);
            } catch (\Exception $facadeError) {
                // Method 2: Fallback to direct Dompdf class
                try {
                    $dompdf = new \Dompdf\Dompdf();
                    $html = view('Admin.pdf.payment_invoice', $data)->render();

                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $filename = 'Payment_Invoice_' . str_replace(' ', '_', $student->admission_number) . '_' . $currentYear . '.pdf';

                    return response()->streamDownload(function() use ($dompdf) {
                        echo $dompdf->output();
                    }, $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                } catch (\Exception $directError) {
                    throw new \Exception('PDF generation failed: ' . $directError->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('Error generating payment invoice PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Record payment (Cash or Bank)
     */
    public function record_payment(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $rules = [
                'paymentID' => 'required|exists:payments,paymentID',
                'paid_amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_source' => 'required|in:Cash,Bank',
                'notes' => 'nullable|string',
            ];

            if ($request->payment_source === 'Bank') {
                $rules['reference_number'] = 'required|string';
                $rules['bank_name'] = 'required|string|max:200';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $payment = Payment::where('schoolID', $schoolID)
                ->where('paymentID', $request->paymentID)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment record not found.'], 404);
            }

            DB::beginTransaction();

            $paymentRecord = PaymentRecord::create([
                'paymentID' => $payment->paymentID,
                'paid_amount' => $request->paid_amount,
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
                'reference_number' => $request->reference_number,
                'bank_name' => $request->bank_name,
                'notes' => $request->notes,
            ]);

            // Allocate payment across fees
            $this->allocatePayment($payment);

            DB::commit();

            // --- SYNC TO INCOME TABLE ---
            // Only sync if payment is verified (for now, assume all are verified on record)
            // Avoid duplicate: Only add for this payment record (not for all history)
            try {
                $student = $payment->student;
                $payerName = $student ? ($student->first_name . ' ' . $student->last_name) : 'Student';
                \App\Models\Income::create([
                    'schoolID' => $payment->schoolID,
                    'receipt_number' => 'AUTO-FEE-' . $payment->paymentID . '-' . now()->format('YmdHis'),
                    'date' => $request->payment_date,
                    'income_category' => 'Tuition & Fees',
                    'description' => 'Student fee payment (auto-sync)',
                    'amount' => $request->paid_amount,
                    'payment_method' => $request->payment_source,
                    'payer_name' => $payerName,
                    'entered_by' => auth()->id() ?? 1,
                ]);
            } catch (\Exception $ex) {
                \Log::error('Failed to sync payment to Income: ' . $ex->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded and allocated.',
                'payment_record' => $paymentRecord
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording payment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Allocate all paid amounts for a payment across student fees based on priority.
     */
    private function allocatePayment($payment)
    {
        $totalPaid = PaymentRecord::where('paymentID', $payment->paymentID)
            ->where('is_verified', true)
            ->sum('paid_amount') ?? 0;
        $remainingToAllocate = $totalPaid;

        // Get all student fee payments, ordered by priority (must_start_pay) and display_order
        $fee_payments = StudentFeePayment::where('paymentID', $payment->paymentID)
            ->join('fees', 'student_fee_payments.feeID', '=', 'fees.feeID')
            ->select('student_fee_payments.*', 'fees.must_start_pay', 'fees.display_order')
            ->orderBy('fees.must_start_pay', 'desc')
            ->orderBy('fees.display_order', 'asc')
            ->get();

        foreach ($fee_payments as $feePayment) {
            $amountToAllocate = min($remainingToAllocate, $feePayment->fee_total_amount);
            $feePayment->update([
                'amount_paid' => $amountToAllocate,
                'balance' => $feePayment->fee_total_amount - $amountToAllocate
            ]);
            $remainingToAllocate -= $amountToAllocate;
        }

        // Update overall payment record totals
        $requiredPaid = StudentFeePayment::where('paymentID', $payment->paymentID)
            ->join('fees', 'student_fee_payments.feeID', '=', 'fees.feeID')
            ->where('fees.must_start_pay', 1)
            ->sum('student_fee_payments.amount_paid') ?? 0;

        $balance = $payment->amount_required - $totalPaid;
        $paymentStatus = 'Pending';
        if ($balance <= 0) {
            $paymentStatus = $totalPaid > $payment->amount_required ? 'Overpaid' : 'Paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'Incomplete Payment';
        }

        $canStartSchool = ($requiredPaid >= $payment->required_fees_amount);

        $payment->update([
            'amount_paid' => $totalPaid,
            'balance' => $balance,
            'required_fees_paid' => $requiredPaid,
            'can_start_school' => $canStartSchool,
            'payment_status' => $paymentStatus,
            'payment_date' => now(),
        ]);
    }

    /**
     * Get payment records for a specific payment
     */
    public function get_payment_records(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $paymentID = $request->input('paymentID');

            if (!$paymentID) {
                return response()->json(['success' => false, 'message' => 'Payment ID is required'], 400);
            }

            // Verify payment belongs to this school
            $payment = Payment::where('schoolID', $schoolID)
                ->where('paymentID', $paymentID)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // Get payment records
            $records = PaymentRecord::where('paymentID', $paymentID)
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $recordsData = $records->map(function($record) {
                return [
                    'recordID' => $record->recordID,
                    'paid_amount' => $record->paid_amount,
                    'reference_number' => $record->reference_number,
                    'payment_date' => $record->payment_date ? $record->payment_date->format('Y-m-d') : null,
                    'payment_source' => $record->payment_source,
                    'bank_name' => $record->bank_name,
                    'notes' => $record->notes,
                    'is_verified' => $record->is_verified,
                ];
            });

            return response()->json([
                'success' => true,
                'records' => $recordsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading payment records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment record
     */
    public function update_payment_record(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $rules = [
                'recordID' => 'required|exists:payment_records,recordID',
                'paymentID' => 'required|exists:payments,paymentID',
                'paid_amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_source' => 'required|in:Cash,Bank',
                'notes' => 'nullable|string',
            ];

            if ($request->payment_source === 'Bank') {
                $rules['reference_number'] = 'required|string';
                $rules['bank_name'] = 'required|string|max:200';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $payment = Payment::where('schoolID', $schoolID)->where('paymentID', $request->paymentID)->first();
            $paymentRecord = PaymentRecord::where('recordID', $request->recordID)->where('paymentID', $request->paymentID)->first();

            if (!$payment || !$paymentRecord) {
                return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
            }

            DB::beginTransaction();

            $paymentRecord->update([
                'paid_amount' => $request->paid_amount,
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
                'reference_number' => $request->reference_number,
                'bank_name' => $request->bank_name,
                'notes' => $request->notes,
            ]);

            $this->allocatePayment($payment);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Record updated.', 'payment_record' => $paymentRecord]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating record: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete payment record
     */
    public function delete_payment_record(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $recordID = $request->input('recordID');
            $paymentID = $request->input('paymentID');

            if (!$recordID || !$paymentID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record ID and Payment ID are required'
                ], 400);
            }

            // Verify payment belongs to this school
            $payment = Payment::where('schoolID', $schoolID)
                ->where('paymentID', $paymentID)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.'
                ], 404);
            }

            // Check if payment record belongs to this payment
            $paymentRecord = PaymentRecord::where('recordID', $recordID)
                ->where('paymentID', $paymentID)
                ->first();

            if (!$paymentRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.'
                ], 404);
            }

            DB::beginTransaction();

            $paymentRecord->delete();

            $this->allocatePayment($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment record deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payment record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export filtered payments as PDF
     */
    public function exportFilteredPaymentsPDF(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');

        if (!$schoolID || !in_array($userType, ['Admin', 'Teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            // Get filter parameters (same as get_payments_ajax)
            $classID = $request->input('class_id', '');
            $subclassID = $request->input('subclass_id', '');
            $studentStatus = $request->input('student_status', '');
            $paymentStatus = $request->input('payment_status', '');
            $year = $request->input('year', date('Y'));
            $searchStudentName = $request->input('search_student_name', '');

            // Get academic year
            $academicYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('year', $year)
                ->first();

            $academicYearID = $academicYear ? $academicYear->academic_yearID : null;
            $isClosedYear = $academicYear && $academicYear->status === 'Closed';

            if (!$academicYearID) {
                $activeAcademicYear = DB::table('academic_years')
                    ->where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->orderBy('year', 'desc')
                    ->first();
                if ($activeAcademicYear) {
                    $academicYearID = $activeAcademicYear->academic_yearID;
                    $year = $activeAcademicYear->year;
                    $isClosedYear = false;
                }
            }

            // Build query (same logic as get_payments_ajax)
            $statusFilter = ['Active', 'Graduated'];
            if (!empty($studentStatus)) {
                $statusFilter = [$studentStatus];
            }

            $query = Student::where('schoolID', $schoolID)
                ->whereIn('status', $statusFilter)
                ->with(['parent', 'subclass.class', 'payments' => function($q) use ($academicYearID, $year) {
                    if ($academicYearID) {
                        $q->where('academic_yearID', $academicYearID);
                    } else {
                        $q->whereYear('created_at', $year);
                    }
                }, 'payments.fee_payments', 'payments.academicYear']);

            if (!empty($classID)) {
                $query->whereHas('subclass', function($q) use ($classID) {
                    $q->where('classID', $classID);
                });
            }

            if (!empty($subclassID)) {
                $query->where('subclassID', $subclassID);
            }

            // Filter by student name search
            if (!empty($searchStudentName)) {
                $query->where(function($q) use ($searchStudentName) {
                    $q->where('first_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhere('last_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhere('middle_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchStudentName . '%'])
                      ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ['%' . $searchStudentName . '%']);
                });
            }

            $students = $query->orderBy('first_name')->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found matching the filters'
                ], 404);
            }

            // Get school info
            $school = School::find($schoolID);
            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'School not found'
                ], 404);
            }

            // Generate PDF for all filtered students
            $pdfData = [];

            foreach ($students as $student) {
                $payment = $student->payments->first(); // Unified payment

                if (!$payment && !empty($paymentStatus)) {
                    if ($paymentStatus !== 'Pending') continue;
                }
                if ($payment && !empty($paymentStatus)) {
                    $status = $payment->payment_status;
                    if ($paymentStatus === 'Incomplete' || $paymentStatus === 'Partial' || $paymentStatus === 'Incomplete Payment') {
                        if ($status !== 'Incomplete Payment' && $status !== 'Partial') continue;
                    } elseif ($status !== $paymentStatus) {
                        continue;
                    }
                }

                // Calculate totals
                $totalRequired = $payment ? $payment->amount_required : 0;
                $totalPaid = $payment ? $payment->amount_paid : 0;
                $totalBalance = $payment ? $payment->balance : 0;
                $requiredPaid = $payment ? $payment->required_fees_paid : 0;
                $requiredAmount = $payment ? $payment->required_fees_amount : 0;
                $canStartSchool = $payment ? $payment->can_start_school : false;

                // Get fee breakdown
                $feeBreakdown = $payment ? $payment->fee_payments()->orderBy('display_order')->get() : collect();

                // Format student class
                $studentClass = 'N/A';
                if ($student->subclass) {
                    $mainClass = $student->subclass->class->class_name ?? '';
                    $subclass = $student->subclass->subclass_name ?? '';
                    if ($mainClass && $subclass) {
                        $studentClass = $mainClass . ' ' . $subclass;
                    } else if ($subclass) {
                        $studentClass = $subclass;
                    } else if ($mainClass) {
                        $studentClass = $mainClass;
                    }
                }
                $pdfData[] = [
                    'student' => [
                        'studentID' => $student->studentID,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'admission_number' => $student->admission_number,
                        'class' => $studentClass,
                        'status' => $student->status,
                        'photo' => $student->photo ? asset('userImages/' . $student->photo) : null,
                    ],
                    'payment' => $payment,
                    'feeBreakdown' => $feeBreakdown,
                    'totalRequired' => $totalRequired,
                    'totalPaid' => $totalPaid,
                    'totalBalance' => $totalBalance,
                    'requiredAmount' => $requiredAmount,
                    'requiredPaid' => $requiredPaid,
                    'canStartSchool' => $canStartSchool,
                ];
            }

            if (empty($pdfData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payment data found matching the filters'
                ], 404);
            }

            // Generate combined PDF using existing template
            $schoolLogo = $school->school_logo ? public_path($school->school_logo) : null;
            $currentYear = $year;

            // For now, generate individual PDFs and combine them
            // Or create a new template for filtered payments list
            // Using the same template but for multiple students
            $data = [
                'school' => $school,
                'students' => $pdfData,
                'schoolLogo' => $schoolLogo,
                'year' => $currentYear,
                'filters' => [
                    'class' => $classID ? (ClassModel::find($classID)->class_name ?? '') : '',
                    'subclass' => $subclassID ? (Subclass::find($subclassID)->subclass_name ?? '') : '',
                    'status' => $studentStatus,
                    'payment_status' => $paymentStatus,
                ]
            ];

            // Generate PDF - create a simple list view for filtered payments
            try {
                // Use a simple view for filtered payments list
                $pdf = PDF::loadView('Admin.pdf.filtered_payments_list', $data);
                $pdf->setPaper('A4', 'portrait');

                $filename = 'Filtered_Payments_' . $year . '_' . date('YmdHis') . '.pdf';

                return $pdf->download($filename);
            } catch (\Exception $e) {
                // Fallback: generate individual PDFs
                Log::error('Error generating filtered payments PDF: ' . $e->getMessage());

                // For now, return first student's PDF as fallback
                if (!empty($pdfData)) {
                    $firstStudent = $pdfData[0];
                    return $this->exportPaymentInvoicePDF($firstStudent['student']['studentID']);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Error generating PDF: ' . $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error exporting filtered payments PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error exporting PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Verify a payment record
     */
    public function verify_payment(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $recordID = $request->input('recordID');
            $record = PaymentRecord::whereHas('payment', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })->where('recordID', $recordID)->first();

            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Record not found'], 404);
            }

            $record->update([
                'is_verified' => true,
                'verified_by' => Session::get('userID'),
                'verified_at' => now(),
            ]);

            // Recalculate payment totals after verification
            $this->allocatePayment($record->payment);

            return response()->json(['success' => true, 'message' => 'Payment verified!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Unverify a payment record
     */
    public function unverify_payment(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $recordID = $request->input('recordID');
            $record = PaymentRecord::whereHas('payment', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })->where('recordID', $recordID)->first();

            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Record not found'], 404);
            }

            $record->update([
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
            ]);

            // Recalculate payment totals after unverification
            $this->allocatePayment($record->payment);

            return response()->json(['success' => true, 'message' => 'Payment verification deleted!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
