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
    private function getCurrentAcademicYearID($schoolID)
    {
        // First, try to get active academic year (prioritize active year)
        $activeAcademicYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('year', 'desc')
            ->first();
        
        if ($activeAcademicYear) {
            return $activeAcademicYear->academic_yearID;
        }
        
        // If no active year, try to get academic year for current calendar year
        $currentYear = date('Y');
        $academicYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('year', $currentYear)
            ->first();
        
        // If found, return its ID
        if ($academicYear) {
            return $academicYear->academic_yearID;
        }
        
        // If not found, return null
        return null;
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
                // Map fee types: 'Tuition Fees' -> 'School Fee', 'Other Fees' -> 'Other Contribution'
                if ($payment->fee_type === 'Tuition Fees') {
                    $schoolFeeBalance += $balance;
                } elseif ($payment->fee_type === 'Other Fees') {
                    $otherContributionBalance += $balance;
                }
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
            ->orderBy('fee_type')
            ->get();

        // Group fees by class for widgets
        $feesByClass = $fees->groupBy('classID');

        return view('Admin.manageFees', compact('classes', 'fees', 'feesByClass'));
    }

    public function store_fee(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        // Check if fee type already exists for this class
        $existingFee = Fee::where('schoolID', $schoolID)
            ->where('classID', $request->classID)
            ->where('fee_type', $request->fee_type)
            ->where('status', 'Active')
            ->first();

        if ($existingFee) {
            return response()->json([
                'success' => false,
                'message' => ucfirst(strtolower($request->fee_type)) . ' already exists for this class. Each class can only have one Tuition Fee and one Other Fee.',
            ], 422);
        }

        // Auto-generate fee_name based on fee_type if not provided
        $feeName = $request->fee_name;
        if (empty($feeName)) {
            $feeName = $request->fee_type === 'Tuition Fees' ? 'School Fee' : 'Other Contribution';
        }

        $validator = Validator::make($request->all(), [
            'classID' => 'required|exists:classes,classID',
            'fee_type' => 'required|in:Tuition Fees,Other Fees',
            'fee_name' => 'nullable|string|max:200',
            'amount' => 'required|numeric|min:0',
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

            $fee = Fee::create([
                'schoolID' => $schoolID,
                'classID' => $request->classID,
                'fee_type' => $request->fee_type,
                'fee_name' => $feeName,
                'amount' => $request->amount,
                'duration' => $request->duration,
                'description' => $request->description,
                'status' => 'Active',
                'allow_installments' => $request->input('allow_installments', false),
                'default_installment_type' => $request->input('default_installment_type'),
                'number_of_installments' => $request->input('number_of_installments'),
                'allow_partial_payment' => $request->input('allow_partial_payment', true),
            ]);

            // Create installments if allow_installments is true
            if ($request->input('allow_installments') && $request->input('number_of_installments') && $request->input('default_installment_type')) {
                $installmentType = $request->input('default_installment_type');
                $numberOfInstallments = $request->input('number_of_installments');
                $amountPerInstallment = $request->amount / $numberOfInstallments;

                $installmentNames = [
                    'Semester' => 'Semester',
                    'Month' => 'Month',
                    'Two Months' => 'Two Months',
                    'Term' => 'Term',
                    'Quarter' => 'Quarter',
                    'One-time' => 'One-time',
                ];

                $baseName = $installmentNames[$installmentType] ?? 'Installment';

                for ($i = 1; $i <= $numberOfInstallments; $i++) {
                    FeeInstallment::create([
                        'feeID' => $fee->feeID,
                        'installment_name' => $baseName . ' ' . $i,
                        'installment_type' => $installmentType,
                        'installment_number' => $i,
                        'amount' => $amountPerInstallment,
                        'status' => 'Active',
                    ]);
                }
            }

            // Create other fees details if fee type is Other Fees
            if ($request->fee_type === 'Other Fees') {
                // Check if other_fees_details is provided as JSON string or array
                $otherFeesDetails = null;
                
                // Try multiple ways to get the data
                if ($request->has('other_fees_details')) {
                    $otherFeesDetailsInput = $request->input('other_fees_details');
                    
                    // Try to decode if it's a JSON string
                    if (is_string($otherFeesDetailsInput)) {
                        // First try direct decode
                        $decoded = json_decode($otherFeesDetailsInput, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $otherFeesDetails = $decoded;
                        } else {
                            // Try URL decode first
                            $urlDecoded = urldecode($otherFeesDetailsInput);
                            $decoded = json_decode($urlDecoded, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $otherFeesDetails = $decoded;
                            }
                        }
                    } elseif (is_array($otherFeesDetailsInput)) {
                        $otherFeesDetails = $otherFeesDetailsInput;
                    }
                }
                
                // Also check raw input from request
                if (empty($otherFeesDetails)) {
                    $rawInput = $request->get('other_fees_details');
                    if (is_string($rawInput)) {
                        $decoded = json_decode(urldecode($rawInput), true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $otherFeesDetails = $decoded;
                        }
                    }
                }
                
                // Log for debugging
                \Log::info('Other Fees Details - Fee ID: ' . $fee->feeID, [
                    'fee_type' => $request->fee_type,
                    'has_input' => $request->has('other_fees_details'),
                    'input_raw' => $request->input('other_fees_details'),
                    'input_type' => gettype($request->input('other_fees_details')),
                    'decoded' => $otherFeesDetails,
                    'decoded_type' => gettype($otherFeesDetails),
                    'decoded_count' => is_array($otherFeesDetails) ? count($otherFeesDetails) : 0
                ]);
                
                if (is_array($otherFeesDetails) && count($otherFeesDetails) > 0) {
                    $createdCount = 0;
                    foreach ($otherFeesDetails as $index => $detail) {
                        // Validate detail
                        if (!empty($detail['name']) && isset($detail['amount']) && is_numeric($detail['amount']) && $detail['amount'] > 0) {
                            try {
                                OtherFeeDetail::create([
                                    'feeID' => $fee->feeID,
                                    'fee_detail_name' => trim($detail['name']),
                                    'amount' => (float) $detail['amount'],
                                    'description' => !empty($detail['description']) ? trim($detail['description']) : null,
                                    'status' => 'Active',
                                ]);
                                $createdCount++;
                                \Log::info("Other Fee Detail #{$index} Created:", [
                                    'feeID' => $fee->feeID,
                                    'name' => $detail['name'],
                                    'amount' => $detail['amount']
                                ]);
                            } catch (\Exception $e) {
                                \Log::error('Error creating other fee detail: ' . $e->getMessage(), [
                                    'detail' => $detail,
                                    'index' => $index,
                                    'trace' => $e->getTraceAsString()
                                ]);
                                DB::rollBack();
                                throw $e;
                            }
                        } else {
                            \Log::warning("Skipping invalid other fee detail #{$index}:", [
                                'detail' => $detail,
                                'name_empty' => empty($detail['name']),
                                'amount_set' => isset($detail['amount']),
                                'amount_numeric' => isset($detail['amount']) ? is_numeric($detail['amount']) : false,
                                'amount_positive' => isset($detail['amount']) && is_numeric($detail['amount']) ? $detail['amount'] > 0 : false
                            ]);
                        }
                    }
                    \Log::info("Created {$createdCount} other fee details for fee ID: {$fee->feeID}");
                } else {
                    \Log::warning('No other fees details to create', [
                        'otherFeesDetails' => $otherFeesDetails,
                        'is_array' => is_array($otherFeesDetails),
                        'count' => is_array($otherFeesDetails) ? count($otherFeesDetails) : 0,
                        'fee_type' => $request->fee_type
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fee assigned successfully',
                'fee' => $fee->load(['class', 'installments', 'otherFeeDetails'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign fee: ' . $e->getMessage()
            ], 500);
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

        // Check if fee type already exists for this class (excluding current fee)
        $existingFee = Fee::where('schoolID', $schoolID)
            ->where('classID', $request->classID)
            ->where('fee_type', $request->fee_type)
            ->where('feeID', '!=', $feeID)
            ->where('status', 'Active')
            ->first();

        if ($existingFee) {
            return response()->json([
                'success' => false,
                'message' => ucfirst(strtolower($request->fee_type)) . ' already exists for this class. Each class can only have one Tuition Fee and one Other Fee.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'classID' => 'required|exists:classes,classID',
            'fee_type' => 'required|in:Tuition Fees,Other Fees',
            'fee_name' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0',
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
                'fee_type' => $request->fee_type,
                'fee_name' => $feeName,
                'amount' => $request->amount,
                'duration' => $request->duration,
                'description' => $request->description,
                'allow_installments' => $request->input('allow_installments', false),
                'default_installment_type' => $request->input('default_installment_type'),
                'number_of_installments' => $request->input('number_of_installments'),
                'allow_partial_payment' => $request->input('allow_partial_payment', true),
            ]);

            // Delete existing installments and create new ones if allow_installments is true
            FeeInstallment::where('feeID', $fee->feeID)->delete();

            if ($request->input('allow_installments') && $request->input('number_of_installments') && $request->input('default_installment_type')) {
                $installmentType = $request->input('default_installment_type');
                $numberOfInstallments = $request->input('number_of_installments');
                $amountPerInstallment = $request->amount / $numberOfInstallments;

                $installmentNames = [
                    'Semester' => 'Semester',
                    'Month' => 'Month',
                    'Two Months' => 'Two Months',
                    'Term' => 'Term',
                    'Quarter' => 'Quarter',
                    'One-time' => 'One-time',
                ];

                $baseName = $installmentNames[$installmentType] ?? 'Installment';

                for ($i = 1; $i <= $numberOfInstallments; $i++) {
                    FeeInstallment::create([
                        'feeID' => $fee->feeID,
                        'installment_name' => $baseName . ' ' . $i,
                        'installment_type' => $installmentType,
                        'installment_number' => $i,
                        'amount' => $amountPerInstallment,
                        'status' => 'Active',
                    ]);
                }
            }

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
        
        // Default year: prioritize next year if it exists and is Active
        $defaultYear = $currentYear;
        $nextYearExists = false;
        foreach ($academicYears as $academicYear) {
            if ($academicYear->year == $nextYear && $academicYear->status == 'Active') {
                $defaultYear = $nextYear;
                $nextYearExists = true;
                break;
            }
        }
        
        // If next year doesn't exist, use the most recent Active year (first in the list since it's ordered desc)
        if (!$nextYearExists) {
            $activeYears = $academicYears->where('status', 'Active');
            if ($activeYears->count() > 0) {
                $defaultYear = $activeYears->first()->year;
            }
        }

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
            $feeType = $request->input('fee_type', '');
            $paymentStatus = $request->input('payment_status', '');
            $year = $request->input('year', date('Y')); // This is the year value from dropdown
            $searchStudentName = $request->input('search_student_name', '');

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

            // Build query - filter by student status
            $statusFilter = ['Active', 'Graduated'];
            if (!empty($studentStatus)) {
                $statusFilter = [$studentStatus];
            }
            
            $query = Student::where('schoolID', $schoolID)
                ->whereIn('status', $statusFilter)
                ->with(['parent', 'subclass.class', 'payments' => function($q) use ($academicYearID, $year) {
                    if ($academicYearID) {
                        // Filter by academic_yearID if academic year exists
                        $q->where('academic_yearID', $academicYearID);
                    } else {
                        // Fallback to year filter if academic year doesn't exist
                        $q->whereYear('created_at', $year);
                    }
                }, 'payments.fee.installments', 'payments.fee.otherFeeDetails', 'payments.academicYear']);

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
            $index = 1;

            foreach ($students as $student) {
                // Ensure fee relationships are loaded for all payments
                $student->load(['payments.fee.installments', 'payments.fee.otherFeeDetails']);
                
                // Filter payments based on criteria
                $filteredPayments = $student->payments->filter(function($payment) use ($academicYearID, $year, $feeType, $paymentStatus) {
                    // Filter by academic year ID if available, otherwise by created_at year
                    if ($academicYearID) {
                        if ($payment->academic_yearID != $academicYearID) {
                            return false;
                        }
                    } else {
                        // Fallback to created_at year if no academic_yearID
                        if ($year && date('Y', strtotime($payment->created_at)) != $year) {
                            return false;
                        }
                    }

                    // Filter by fee type
                    if (!empty($feeType) && $payment->fee_type !== $feeType) {
                        return false;
                    }

                    // Filter by payment status
                    if (!empty($paymentStatus)) {
                        $statusMap = ['Incomplete' => 'Incomplete Payment'];
                        $actualStatus = $statusMap[$paymentStatus] ?? $paymentStatus;
                        if ($payment->payment_status !== $actualStatus) {
                            return false;
                        }
                    }

                    return true;
                });

                // Skip if no payments match filters (unless no filters applied)
                if ($filteredPayments->isEmpty() && (!empty($feeType) || !empty($paymentStatus))) {
                    continue;
                }

                // Get academic year from payments (get first payment's academic year, or use selected year)
                $academicYearDisplay = $year; // Default to selected year
                if (!$filteredPayments->isEmpty()) {
                    $firstPayment = $filteredPayments->first();
                    if ($firstPayment->academic_yearID) {
                        $academicYear = DB::table('academic_years')
                            ->where('academic_yearID', $firstPayment->academic_yearID)
                            ->first();
                        if ($academicYear) {
                            $academicYearDisplay = $academicYear->year;
                        }
                    } else {
                        // Fallback to created_at year if academicYear relationship is null
                        $academicYearDisplay = date('Y', strtotime($firstPayment->created_at));
                    }
                }

                // For graduated students, get debts from past academic years (payments_history)
                $graduatedDebts = ['school_fee_balance' => 0, 'other_contribution_balance' => 0];
                if ($student->status === 'Graduated') {
                    // Get all closed academic years for this school
                    $closedAcademicYears = DB::table('academic_years')
                        ->where('schoolID', $schoolID)
                        ->where('status', 'Closed')
                        ->orderBy('year', 'desc')
                        ->get();

                    foreach ($closedAcademicYears as $closedYear) {
                        // Get payments from history for this graduated student
                        $historyPayments = DB::table('payments_history')
                            ->where('studentID', $student->studentID)
                            ->where('academic_yearID', $closedYear->academic_yearID)
                            ->get();

                        foreach ($historyPayments as $historyPayment) {
                            $balance = (float) $historyPayment->balance;
                            if ($historyPayment->fee_type === 'Tuition Fees') {
                                $graduatedDebts['school_fee_balance'] += $balance;
                            } elseif ($historyPayment->fee_type === 'Other Fees') {
                                $graduatedDebts['other_contribution_balance'] += $balance;
                            }
                        }
                    }

                    // Ensure non-negative
                    $graduatedDebts['school_fee_balance'] = max(0, $graduatedDebts['school_fee_balance']);
                    $graduatedDebts['other_contribution_balance'] = max(0, $graduatedDebts['other_contribution_balance']);
                }

                // Format student data for JSON
                $studentImgPath = $student->photo
                    ? asset('userImages/' . $student->photo)
                    : ($student->gender == 'Female'
                        ? asset('images/female.png')
                        : asset('images/male.png'));
                
                // Get student class information - use historical data for closed years
                $subclassName = null;
                $className = null;
                
                if ($isClosedYear && $academicYearID) {
                    // For closed years, get class from student_class_history
                    $studentClassHistory = DB::table('student_class_history')
                        ->where('studentID', $student->studentID)
                        ->where('academic_yearID', $academicYearID)
                        ->first();
                    
                    if ($studentClassHistory) {
                        // Get subclass name from history using original_subclassID
                        $subclassHistory = DB::table('subclasses_history')
                            ->where('academic_yearID', $academicYearID)
                            ->where('original_subclassID', $studentClassHistory->subclassID)
                            ->first();
                        
                        // Get class name from history using original_classID
                        $classHistory = DB::table('classes_history')
                            ->where('academic_yearID', $academicYearID)
                            ->where('original_classID', $studentClassHistory->classID)
                            ->first();
                        
                        if ($subclassHistory) {
                            $subclassName = $subclassHistory->subclass_name;
                        }
                        if ($classHistory) {
                            $className = $classHistory->class_name;
                        }
                    }
                } else {
                    // For active years, use current class
                    if ($student->subclass) {
                        $subclassName = $student->subclass->subclass_name;
                        $className = $student->subclass->class ? $student->subclass->class->class_name : null;
                    }
                }
                
                $studentData = [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'status' => $student->status, // Active or Graduated
                    'photo' => $studentImgPath,
                    'parent' => $student->parent ? [
                        'first_name' => $student->parent->first_name,
                        'last_name' => $student->parent->last_name,
                        'phone' => $student->parent->phone,
                    ] : null,
                    'subclass' => [
                        'subclass_name' => $subclassName,
                        'class_name' => $className,
                    ],
                ];

                // Get previous year debt for this student (only for active students, not graduated)
                $previousYearDebt = ['school_fee_balance' => 0, 'other_contribution_balance' => 0];
                if ($student->status === 'Active') {
                    $previousYearDebt = $this->getPreviousYearBalance($student->studentID, $schoolID);
                }
                
                // Get current fees for student's class to calculate base amounts
                $classID = $student->subclass->classID ?? null;
                $currentTuitionFees = 0;
                $currentOtherFees = 0;
                if ($classID) {
                    $tuitionFees = Fee::where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->where('fee_type', 'Tuition Fees')
                        ->where('status', 'Active')
                        ->get();
                    $currentTuitionFees = $tuitionFees->sum('amount');
                    
                    $otherFees = Fee::where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->where('fee_type', 'Other Fees')
                        ->where('status', 'Active')
                        ->get();
                    $currentOtherFees = $otherFees->sum('amount');
                }
                
                // Aggregate payments by fee type
                $tuitionPayments = $filteredPayments->where('fee_type', 'Tuition Fees');
                $otherFeePayments = $filteredPayments->where('fee_type', 'Other Fees');

                // Calculate totals for Tuition Fees
                // Use total_required from payment data (base + debt) if available, otherwise use amount_required
                $tuitionRequired = 0;
                foreach ($tuitionPayments as $payment) {
                    // amount_required already includes debt from generate_control_numbers
                    $tuitionRequired += $payment->amount_required;
                }
                
                // For active students in active years, add debt if not already included in amount_required
                // (This handles cases where payment was created before debt was added)
                // For closed years, don't add debts - data stays as it was originally
                if ($student->status === 'Active' && !$isClosedYear && $previousYearDebt['school_fee_balance'] > 0) {
                    // Check if debt is already included by checking if amount_required > currentTuitionFees
                    $totalFromPayments = $tuitionPayments->sum('amount_required');
                    if ($totalFromPayments <= $currentTuitionFees) {
                        // Debt not included, add it
                        $tuitionRequired += $previousYearDebt['school_fee_balance'];
                    }
                }
                
                // Calculate paid amount from payment_records
                $tuitionPaid = 0;
                foreach ($tuitionPayments as $payment) {
                    $paid = PaymentRecord::where('paymentID', $payment->paymentID)->sum('paid_amount');
                    $tuitionPaid += $paid ?: 0;
                }
                $tuitionBalance = $tuitionRequired - $tuitionPaid;

                // Calculate totals for Other Fees
                $otherRequired = 0;
                foreach ($otherFeePayments as $payment) {
                    // amount_required already includes debt from generate_control_numbers
                    $otherRequired += $payment->amount_required;
                }
                
                // For active students in active years, add debt if not already included
                // For closed years, don't add debts - data stays as it was originally
                if ($student->status === 'Active' && !$isClosedYear && $previousYearDebt['other_contribution_balance'] > 0) {
                    $totalFromPayments = $otherFeePayments->sum('amount_required');
                    if ($totalFromPayments <= $currentOtherFees) {
                        // Debt not included, add it
                        $otherRequired += $previousYearDebt['other_contribution_balance'];
                    }
                }
                
                // Calculate paid amount from payment_records
                $otherPaid = 0;
                foreach ($otherFeePayments as $payment) {
                    $paid = PaymentRecord::where('paymentID', $payment->paymentID)->sum('paid_amount');
                    $otherPaid += $paid ?: 0;
                }
                $otherBalance = $otherRequired - $otherPaid;

                // For graduated students, add debts from past academic years
                if ($student->status === 'Graduated') {
                    $tuitionRequired += $graduatedDebts['school_fee_balance'];
                    $tuitionBalance += $graduatedDebts['school_fee_balance'];
                    $otherRequired += $graduatedDebts['other_contribution_balance'];
                    $otherBalance += $graduatedDebts['other_contribution_balance'];
                }

                // Total amounts
                $totalRequired = $tuitionRequired + $otherRequired;
                $totalPaid = $tuitionPaid + $otherPaid;
                $totalBalance = $tuitionBalance + $otherBalance;

                // Determine overall payment status
                $overallStatus = 'Pending';
                if ($totalBalance <= 0 && $totalPaid > 0) {
                    $overallStatus = $totalPaid > $totalRequired ? 'Overpaid' : 'Paid';
                } elseif ($totalPaid > 0 && $totalBalance > 0) {
                    $overallStatus = 'Incomplete Payment';
                } elseif ($totalPaid > 0 && $totalPaid < $totalRequired) {
                    $overallStatus = 'Partial';
                }

                // Format all payments with installments and other fee details
                $allPayments = [];
                foreach ($filteredPayments as $payment) {
                    // Calculate actual paid amount from payment_records
                    // For closed years, show all payment records (historical data)
                    // For active years, only show payment records for this year's control numbers
                    $actualPaid = PaymentRecord::where('paymentID', $payment->paymentID)->sum('paid_amount');
                    $actualPaid = $actualPaid ?: 0;
                    
                    // Calculate debt for this payment type
                    // For closed years, don't show debts - data stays as it was originally
                    $debt = 0;
                    $baseAmountRequired = 0;
                    
                    if (!$isClosedYear) {
                        // Only calculate debts for active years
                        if ($payment->fee_type === 'Tuition Fees') {
                            $debt = $previousYearDebt['school_fee_balance'];
                            $baseAmountRequired = $currentTuitionFees; // Base fee for current year
                        } elseif ($payment->fee_type === 'Other Fees') {
                            $debt = $previousYearDebt['other_contribution_balance'];
                            $baseAmountRequired = $currentOtherFees; // Base fee for current year
                        }
                    } else {
                        // For closed years, use original amount_required as base (no debt calculation)
                        // Extract base amount from amount_required (which may have included debt when created)
                        // But for closed years, we show it as it was originally
                        $baseAmountRequired = $payment->amount_required;
                        $debt = 0; // No debt shown for closed years
                    }
                    
                    // Total required = base amount + debt (for active years)
                    // For closed years, total = base amount only (no debt)
                    $totalAmountRequired = $isClosedYear ? $baseAmountRequired : ($baseAmountRequired + $debt);
                    $actualBalance = $totalAmountRequired - $actualPaid;
                    
                    $paymentData = [
                        'paymentID' => $payment->paymentID,
                        'feeID' => $payment->feeID,
                        'fee_type' => $payment->fee_type,
                        'control_number' => $payment->control_number,
                        'amount_required' => (float) $baseAmountRequired, // Base amount without debt
                        'debt' => (float) $debt, // Debt from previous year
                        'total_required' => (float) $totalAmountRequired, // Total = base + debt
                        'amount_paid' => (float) $actualPaid,
                        'balance' => (float) $actualBalance,
                        'payment_status' => $payment->payment_status,
                        'sms_sent' => $payment->sms_sent,
                        'sms_sent_at' => $payment->sms_sent_at ? $payment->sms_sent_at->toDateTimeString() : null,
                        'payment_date' => $payment->payment_date ? $payment->payment_date->toDateTimeString() : null,
                        'payment_reference' => $payment->payment_reference,
                        'notes' => $payment->notes,
                    ];

                    // Add installments for Tuition Fees
                    if ($payment->fee && $payment->fee_type === 'Tuition Fees') {
                        // Load installments if not already loaded
                        if (!$payment->fee->relationLoaded('installments')) {
                            $payment->fee->load('installments');
                        }
                        
                        if ($payment->fee->installments && $payment->fee->installments->count() > 0) {
                            // Get all installments (both Active and Inactive for display)
                            $paymentData['installments'] = $payment->fee->installments->map(function($installment) {
                                return [
                                    'installmentID' => $installment->installmentID,
                                    'installment_name' => $installment->installment_name,
                                    'installment_type' => $installment->installment_type,
                                    'installment_number' => $installment->installment_number,
                                    'amount' => (float) $installment->amount,
                                    'due_date' => $installment->due_date ? $installment->due_date->format('Y-m-d') : null,
                                    'description' => $installment->description,
                                    'status' => $installment->status,
                                ];
                            })->toArray();
                        } else {
                            $paymentData['installments'] = [];
                        }
                    } else {
                        $paymentData['installments'] = [];
                    }
                    
                    // Debug: Log if fee is missing
                    if (!$payment->fee) {
                        \Log::warning('Payment ' . $payment->paymentID . ' has no fee relationship');
                    } elseif ($payment->fee_type === 'Tuition Fees' && (!$payment->fee->installments || $payment->fee->installments->count() === 0)) {
                        \Log::info('Payment ' . $payment->paymentID . ' (Tuition Fees) has no installments. Fee ID: ' . $payment->fee->feeID);
                    }

                    // Add other fee details for Other Fees
                    if ($payment->fee && $payment->fee_type === 'Other Fees') {
                        // Load otherFeeDetails if not already loaded
                        if (!$payment->fee->relationLoaded('otherFeeDetails')) {
                            $payment->fee->load('otherFeeDetails');
                        }
                        
                        if ($payment->fee->otherFeeDetails && $payment->fee->otherFeeDetails->count() > 0) {
                            // Get all other fee details (both Active and Inactive for display)
                            $paymentData['other_fee_details'] = $payment->fee->otherFeeDetails->map(function($detail) {
                                return [
                                    'detailID' => $detail->detailID,
                                    'fee_detail_name' => $detail->fee_detail_name,
                                    'amount' => (float) $detail->amount,
                                    'description' => $detail->description,
                                    'status' => $detail->status,
                                ];
                            })->toArray();
                        } else {
                            $paymentData['other_fee_details'] = [];
                        }
                    } else {
                        $paymentData['other_fee_details'] = [];
                    }
                    
                    // Debug: Log if fee is missing or has no details
                    if (!$payment->fee) {
                        \Log::warning('Payment ' . $payment->paymentID . ' has no fee relationship');
                    } elseif ($payment->fee_type === 'Other Fees' && (!$payment->fee->otherFeeDetails || $payment->fee->otherFeeDetails->count() === 0)) {
                        \Log::info('Payment ' . $payment->paymentID . ' (Other Fees) has no other fee details. Fee ID: ' . $payment->fee->feeID);
                    }

                    $allPayments[] = $paymentData;
                }

                // Add to filtered data (one row per student)
                $filteredData[] = [
                    'index' => $index++,
                    'student' => $studentData,
                    'academic_year' => $academicYearDisplay,
                    'is_graduated' => $student->status === 'Graduated',
                    'graduated_debts' => $graduatedDebts,
                    'payments' => $allPayments,
                    'totals' => [
                        'tuition_required' => $tuitionRequired,
                        'tuition_paid' => $tuitionPaid,
                        'tuition_balance' => $tuitionBalance,
                        'other_required' => $otherRequired,
                        'other_paid' => $otherPaid,
                        'other_balance' => $otherBalance,
                        'total_required' => $totalRequired,
                        'total_paid' => $totalPaid,
                        'total_balance' => $totalBalance,
                        'overall_status' => $overallStatus,
                    ]
                ];
            }

            // Calculate statistics
            $totalPayments = count($filteredData);
            $pendingPayments = 0;
            $incompletePayments = 0;
            $paidPayments = 0;
            $totalRequired = 0;
            $totalPaid = 0;
            $totalBalance = 0;

            foreach ($filteredData as $item) {
                if (isset($item['totals'])) {
                    $totals = $item['totals'];
                    $totalRequired += $totals['total_required'] ?? 0;
                    $totalPaid += $totals['total_paid'] ?? 0;
                    $totalBalance += $totals['total_balance'] ?? 0;

                    $overallStatus = $totals['overall_status'] ?? 'Pending';
                    if ($overallStatus === 'Pending') {
                        $pendingPayments++;
                    } elseif ($overallStatus === 'Incomplete Payment' || $overallStatus === 'Partial') {
                        $incompletePayments++;
                    } elseif ($overallStatus === 'Paid') {
                        $paidPayments++;
                    }
                }
            }

            // Calculate additional statistics
            $totalStudents = count($filteredData);
            $activeStudents = 0;
            $graduatedStudents = 0;
            $studentsWithDebt = 0;
            $studentsPaid = 0;
            
            foreach ($filteredData as $item) {
                $student = $item['student'];
                if ($student['status'] === 'Active') {
                    $activeStudents++;
                } elseif ($student['status'] === 'Graduated') {
                    $graduatedStudents++;
                }
                
                $totals = $item['totals'] ?? [];
                if (($totals['total_balance'] ?? 0) > 0) {
                    $studentsWithDebt++;
                }
                if (($totals['total_paid'] ?? 0) > 0) {
                    $studentsPaid++;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $filteredData,
                'is_closed_year' => $isClosedYear,
                'academic_year' => $academicYearDisplay,
                'statistics' => [
                    'total_students' => $totalStudents,
                    'active_students' => $activeStudents,
                    'graduated_students' => $graduatedStudents,
                    'students_with_debt' => $studentsWithDebt,
                    'students_paid' => $studentsPaid,
                    'total_payments' => $totalPayments,
                    'pending_payments' => $pendingPayments,
                    'incomplete_payments' => $incompletePayments,
                    'paid_payments' => $paidPayments,
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

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            DB::beginTransaction();

            // Get all active students only (exclude graduated students)
            $students = Student::where('schoolID', $schoolID)
                ->where('status', 'Active') // Only active students, excludes 'Graduated' status
                ->with(['subclass.class', 'parent'])
                ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active students found to generate control numbers'
                ], 404);
            }

            $generated = 0;
            $skipped = 0;

            foreach ($students as $student) {
                $classID = $student->subclass->classID ?? null;
                
                if (!$classID) {
                    $skipped++;
                    continue;
                }

                // Get fees by type for student's class
                $tuitionFees = Fee::where('schoolID', $schoolID)
                    ->where('classID', $classID)
                    ->where('fee_type', 'Tuition Fees')
                    ->where('status', 'Active')
                    ->get();

                $otherFees = Fee::where('schoolID', $schoolID)
                    ->where('classID', $classID)
                    ->where('fee_type', 'Other Fees')
                    ->where('status', 'Active')
                    ->get();

                // Generate control number for Tuition Fees if exists
                if ($tuitionFees->count() > 0) {
                    $tuitionAmount = $tuitionFees->sum('amount');
                    
                    // Get previous year balance (debt) for School Fee
                    $previousYearBalance = $this->getPreviousYearBalance($student->studentID, $schoolID);
                    $schoolFeeDebt = $previousYearBalance['school_fee_balance'];
                    
                    // Add previous year debt to current year amount
                    $totalTuitionAmount = $tuitionAmount + $schoolFeeDebt;
                    
                    // Check if student already has tuition payment for current academic year
                    $currentAcademicYearID = $this->getCurrentAcademicYearID($schoolID);
                    $existingTuitionPayment = Payment::where('studentID', $student->studentID)
                        ->where('fee_type', 'Tuition Fees')
                        ->where(function($query) use ($currentAcademicYearID) {
                            if ($currentAcademicYearID) {
                                $query->where('academic_yearID', $currentAcademicYearID);
                            } else {
                                // If no academic year, check by current year
                                $query->whereYear('created_at', date('Y'));
                            }
                        })
                        ->where('payment_status', '!=', 'Paid')
                        ->first();

                    if (!$existingTuitionPayment) {
                        $controlNumber = $this->generateControlNumber($schoolID, $student->studentID, 'TUITION');
                        
                        Payment::create([
                            'schoolID' => $schoolID,
                            'academic_yearID' => $this->getCurrentAcademicYearID($schoolID),
                            'studentID' => $student->studentID,
                            'feeID' => null, // Aggregate for all tuition fees
                            'fee_type' => 'Tuition Fees',
                            'control_number' => $controlNumber,
                            'amount_required' => $totalTuitionAmount,
                            'amount_paid' => 0,
                            'balance' => $totalTuitionAmount,
                            'payment_status' => 'Pending',
                            'sms_sent' => 'No',
                            'notes' => $schoolFeeDebt > 0 ? "Imeongezewa na deni la mwaka uliopita: " . number_format($schoolFeeDebt, 0) . " TZS" : null,
                        ]);
                        $generated++;
                    }
                }

                // Generate control number for Other Fees if exists
                if ($otherFees->count() > 0) {
                    $otherAmount = $otherFees->sum('amount');
                    
                    // Get previous year balance (debt) for Other Contribution
                    $previousYearBalance = $this->getPreviousYearBalance($student->studentID, $schoolID);
                    $otherContributionDebt = $previousYearBalance['other_contribution_balance'];
                    
                    // Add previous year debt to current year amount
                    $totalOtherAmount = $otherAmount + $otherContributionDebt;
                    
                    // Check if student already has other fees payment for current academic year
                    $currentAcademicYearID = $this->getCurrentAcademicYearID($schoolID);
                    $existingOtherPayment = Payment::where('studentID', $student->studentID)
                        ->where('fee_type', 'Other Fees')
                        ->where(function($query) use ($currentAcademicYearID) {
                            if ($currentAcademicYearID) {
                                $query->where('academic_yearID', $currentAcademicYearID);
                            } else {
                                // If no academic year, check by current year
                                $query->whereYear('created_at', date('Y'));
                            }
                        })
                        ->where('payment_status', '!=', 'Paid')
                        ->first();

                    if (!$existingOtherPayment) {
                        $controlNumber = $this->generateControlNumber($schoolID, $student->studentID, 'OTHER');
                        
                        Payment::create([
                            'schoolID' => $schoolID,
                            'academic_yearID' => $this->getCurrentAcademicYearID($schoolID),
                            'studentID' => $student->studentID,
                            'feeID' => null, // Aggregate for all other fees
                            'fee_type' => 'Other Fees',
                            'control_number' => $controlNumber,
                            'amount_required' => $totalOtherAmount,
                            'amount_paid' => 0,
                            'balance' => $totalOtherAmount,
                            'payment_status' => 'Pending',
                            'sms_sent' => 'No',
                            'notes' => $otherContributionDebt > 0 ? "Imeongezewa na deni la mwaka uliopita: " . number_format($otherContributionDebt, 0) . " TZS" : null,
                        ]);
                        $generated++;
                    }
                }

                if ($tuitionFees->count() == 0 && $otherFees->count() == 0) {
                    $skipped++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Control numbers generated successfully. Generated: {$generated}, Skipped: {$skipped}",
                'generated' => $generated,
                'skipped' => $skipped
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating control numbers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating control numbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique control number
     * Format: 3345XXXXX (9 digits total, starts with 3345, only digits)
     */
    private function generateControlNumber($schoolID, $studentID, $type = 'TUITION')
    {
        do {
            // Generate 5 random digits (0-9)
            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // Format: 3345 + 5 random digits = 9 digits total
            $controlNumber = '3345' . $randomDigits;
            
            // Check if control number already exists
            $exists = Payment::where('control_number', $controlNumber)->exists();
        } while ($exists);

        return $controlNumber;
    }

    /**
     * Send control numbers to all parents via SMS
     */
    public function send_control_numbers_sms(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Get school info
            $school = School::find($schoolID);
            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shule haijapatikana'
                ], 404);
            }

            // Get all payments with pending status and not sent
            $payments = Payment::where('schoolID', $schoolID)
                ->where('payment_status', '!=', 'Paid')
                ->where('sms_sent', 'No')
                ->with(['student.parent', 'student.subclass.class', 'fee.installments', 'fee.otherFeeDetails'])
                ->get();

            if ($payments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hakuna control numbers za kutuma. Tafadhali generate control numbers kwanza.'
                ], 404);
            }

            $sent = 0;
            $failed = 0;

            // Group payments by student to send one SMS per student with all control numbers
            $paymentsByStudent = $payments->groupBy('studentID');

            foreach ($paymentsByStudent as $studentID => $studentPayments) {
                $firstPayment = $studentPayments->first();
                
                if (!$firstPayment->student || !$firstPayment->student->parent || !$firstPayment->student->parent->phone) {
                    $failed += $studentPayments->count();
                    continue;
                }

                $parent = $firstPayment->student->parent;
                $student = $firstPayment->student;
                $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);
                $parentName = trim($parent->first_name . ' ' . ($parent->last_name ?? ''));

                // Build SMS message with all control numbers
                $message = "{$school->school_name}\nMzazi {$parentName}, mwanafunzi {$studentName} ana control numbers zifuatazo:\n\n";

                // Get Tuition Fees payment
                $tuitionPayment = $studentPayments->where('fee_type', 'Tuition Fees')->first();
                if ($tuitionPayment) {
                    $message .= "ADA ZA MASOMO (TUITION FEES)\n";
                    $message .= "Control Number: {$tuitionPayment->control_number}\n";
                    $message .= "Kiasi: TZS " . number_format($tuitionPayment->amount_required, 0) . "/=\n";
                    
                    // Get tuition fees for student's class
                    $tuitionFees = collect();
                    if ($tuitionPayment->fee) {
                        $tuitionFees = collect([$tuitionPayment->fee]);
                    } else {
                        $classID = $student->subclass ? $student->subclass->classID : null;
                        if ($classID) {
                            $tuitionFees = Fee::where('schoolID', $schoolID)
                                ->where('classID', $classID)
                                ->where('fee_type', 'Tuition Fees')
                                ->where('status', 'Active')
                                ->with(['installments'])
                                ->get();
                        }
                    }
                    
                    // Add installment information if available
                    $allInstallments = collect();
                    $allowPartialPayment = false;
                    foreach ($tuitionFees as $fee) {
                        if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                            $allInstallments = $allInstallments->merge($fee->installments->where('status', 'Active'));
                            if ($fee->allow_partial_payment) {
                                $allowPartialPayment = true;
                            }
                        }
                    }
                    
                    if ($allInstallments->count() > 0) {
                        $message .= "Awamu:\n";
                        foreach ($allInstallments as $installment) {
                            $message .= "- " . $installment->installment_name . " (TZS " . number_format($installment->amount, 0) . "/=)\n";
                        }
                        $message = rtrim($message, "\n");
                        if ($allowPartialPayment) {
                            $message .= "\nUnaweza kulipa nusu nusu kwa kila awamu.";
                        } else {
                            $message .= "\nLazima ulipe kiasi kamili cha kila awamu.";
                        }
                    }
                    $message .= "\n\n";
                }

                // Get Other Fees payment
                $otherFeePayment = $studentPayments->where('fee_type', 'Other Fees')->first();
                if ($otherFeePayment) {
                    $message .= "ADA ZINGINE (OTHER FEES)\n";
                    $message .= "Control Number: {$otherFeePayment->control_number}\n";
                    $message .= "Kiasi: TZS " . number_format($otherFeePayment->amount_required, 0) . "/=\n";
                    
                    // Get other fees for student's class
                    $otherFees = collect();
                    if ($otherFeePayment->fee) {
                        $otherFees = collect([$otherFeePayment->fee]);
                    } else {
                        $classID = $student->subclass ? $student->subclass->classID : null;
                        if ($classID) {
                            $otherFees = Fee::where('schoolID', $schoolID)
                                ->where('classID', $classID)
                                ->where('fee_type', 'Other Fees')
                                ->where('status', 'Active')
                                ->with(['otherFeeDetails', 'installments'])
                                ->get();
                        }
                    }
                    
                    // Add other fees details if available
                    $allOtherFeeDetails = collect();
                    foreach ($otherFees as $fee) {
                        if ($fee->otherFeeDetails && $fee->otherFeeDetails->count() > 0) {
                            $allOtherFeeDetails = $allOtherFeeDetails->merge($fee->otherFeeDetails->where('status', 'Active'));
                        }
                    }
                    
                    if ($allOtherFeeDetails->count() > 0) {
                        $message .= "Ada hii inajumuisha:\n";
                        foreach ($allOtherFeeDetails as $detail) {
                            $message .= "- " . $detail->fee_detail_name . " (TZS " . number_format($detail->amount, 0) . "/=)\n";
                        }
                        $message = rtrim($message, "\n");
                    }
                    
                    // Add installment information if available for Other Fees
                    $allInstallments = collect();
                    $allowPartialPayment = false;
                    foreach ($otherFees as $fee) {
                        if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                            $allInstallments = $allInstallments->merge($fee->installments->where('status', 'Active'));
                            if ($fee->allow_partial_payment) {
                                $allowPartialPayment = true;
                            }
                        }
                    }
                    
                    if ($allInstallments->count() > 0) {
                        $message .= "\nAwamu:\n";
                        foreach ($allInstallments as $installment) {
                            $message .= "- " . $installment->installment_name . " (TZS " . number_format($installment->amount, 0) . "/=)\n";
                        }
                        $message = rtrim($message, "\n");
                        if ($allowPartialPayment) {
                            $message .= "\nUnaweza kulipa nusu nusu kwa kila awamu.";
                        } else {
                            $message .= "\nLazima ulipe kiasi kamili cha kila awamu.";
                        }
                    }
                    $message .= "\n\n";
                }

                $message .= "Tafadhali lipa kwa kutumia control numbers hizi kwenye benki yoyote.";

                // Send SMS
                $smsResult = $this->smsService->sendSms($parent->phone, $message);

                if ($smsResult['success']) {
                    // Update SMS sent status for all payments of this student
                    foreach ($studentPayments as $payment) {
                        $payment->sms_sent = 'Yes';
                        $payment->sms_sent_at = now();
                        $payment->save();
                    }
                    $sent++;
                } else {
                    $failed++;
                    Log::error("Failed to send SMS to parent {$parent->parentID}: " . $smsResult['message']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "SMS zimetumwa kwa mafanikio. Zimetumwa: {$sent}, Zimeshindwa: {$failed}",
                'sent' => $sent,
                'failed' => $failed
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending control numbers SMS: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kosa la kutuma SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend control number to specific parent
     */
    public function resend_control_number($paymentID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->with(['student.parent', 'student.subclass.class', 'school', 'fee.installments', 'fee.otherFeeDetails'])
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment haijapatikana'
                ], 404);
            }

            if (!$payment->student || !$payment->student->parent || !$payment->student->parent->phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mwanafunzi au mzazi hajapatikana au hakuna namba ya simu'
                ], 404);
            }

            $parent = $payment->student->parent;
            $student = $payment->student;
            $school = $payment->school;
            $studentName = trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name);
            $parentName = trim($parent->first_name . ' ' . ($parent->last_name ?? ''));

            // Build SMS message based on fee type
            $message = "{$school->school_name}. Mzazi {$parentName}, mwanafunzi {$studentName} ana control number: {$payment->control_number} kwa ajili ya malipo ya ";
            
            if ($payment->fee_type == 'Tuition Fees') {
                // Tuition Fees message with installments
                $message .= "ada za masomo (Tuition Fees). Kiasi kinachohitajika: TZS " . number_format($payment->amount_required, 2);
                
                // Get tuition fees for student's class if payment has no specific fee
                $tuitionFees = collect();
                if ($payment->fee) {
                    $tuitionFees = collect([$payment->fee]);
                } else {
                    // Get all tuition fees for student's class
                    $classID = $student->subclass ? $student->subclass->classID : null;
                    if ($classID) {
                        $tuitionFees = Fee::where('schoolID', $schoolID)
                            ->where('classID', $classID)
                            ->where('fee_type', 'Tuition Fees')
                            ->where('status', 'Active')
                            ->with(['installments'])
                            ->get();
                    }
                }
                
                // Add installment information if available
                $allInstallments = collect();
                $allowPartialPayment = false;
                foreach ($tuitionFees as $fee) {
                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $allInstallments = $allInstallments->merge($fee->installments->where('status', 'Active'));
                        if ($fee->allow_partial_payment) {
                            $allowPartialPayment = true;
                        }
                    }
                }
                
                if ($allInstallments->count() > 0) {
                    $message .= "\nAda hii inaweza kulipwa kwa awamu " . $allInstallments->count() . ":\n";
                    foreach ($allInstallments as $installment) {
                        $message .= "- " . $installment->installment_name . " (TZS " . number_format($installment->amount, 0) . "/=)\n";
                    }
                    // Remove last newline
                    $message = rtrim($message, "\n");
                    
                    if ($allowPartialPayment) {
                        $message .= "\nUnaweza kulipa nusu nusu kwa kila awamu.";
                    } else {
                        $message .= "\nLazima ulipe kiasi kamili cha kila awamu.";
                    }
                }
            } else {
                // Other Fees message with details
                $message .= "ada zingine (Other Fees).\nKiasi kinachohitajika: TZS " . number_format($payment->amount_required, 0) . "/=";
                
                // Get other fees for student's class if payment has no specific fee
                $otherFees = collect();
                if ($payment->fee) {
                    $otherFees = collect([$payment->fee]);
                } else {
                    // Get all other fees for student's class
                    $classID = $student->subclass ? $student->subclass->classID : null;
                    if ($classID) {
                        $otherFees = Fee::where('schoolID', $schoolID)
                            ->where('classID', $classID)
                            ->where('fee_type', 'Other Fees')
                            ->where('status', 'Active')
                            ->with(['otherFeeDetails', 'installments'])
                            ->get();
                    }
                }
                
                // Add other fees details if available
                $allOtherFeeDetails = collect();
                foreach ($otherFees as $fee) {
                    if ($fee->otherFeeDetails && $fee->otherFeeDetails->count() > 0) {
                        $allOtherFeeDetails = $allOtherFeeDetails->merge($fee->otherFeeDetails->where('status', 'Active'));
                    }
                }
                
                if ($allOtherFeeDetails->count() > 0) {
                    $message .= ". Ada hii inajumuisha:\n";
                    foreach ($allOtherFeeDetails as $detail) {
                        $message .= "- " . $detail->fee_detail_name . " (TZS " . number_format($detail->amount, 0) . "/=)\n";
                    }
                    // Remove last newline
                    $message = rtrim($message, "\n");
                }
                
                // Add installment information if available for Other Fees
                $allInstallments = collect();
                $allowPartialPayment = false;
                foreach ($otherFees as $fee) {
                    if ($fee->allow_installments && $fee->installments && $fee->installments->count() > 0) {
                        $allInstallments = $allInstallments->merge($fee->installments->where('status', 'Active'));
                        if ($fee->allow_partial_payment) {
                            $allowPartialPayment = true;
                        }
                    }
                }
                
                if ($allInstallments->count() > 0) {
                    $message .= "\nAda hii inaweza kulipwa kwa awamu " . $allInstallments->count() . ":\n";
                    foreach ($allInstallments as $installment) {
                        $message .= "- " . $installment->installment_name . " (TZS " . number_format($installment->amount, 0) . "/=)\n";
                    }
                    // Remove last newline
                    $message = rtrim($message, "\n");
                    
                    if ($allowPartialPayment) {
                        $message .= "\nUnaweza kulipa nusu nusu kwa kila awamu.";
                    } else {
                        $message .= "\nLazima ulipe kiasi kamili cha kila awamu.";
                    }
                }
            }
            
            $message .= "\n\nTafadhali lipa kwa kutumia control number hii kwenye benki yoyote.";

            // Send SMS
            $smsResult = $this->smsService->sendSms($parent->phone, $message);

            if ($smsResult['success']) {
                $payment->sms_sent = 'Yes';
                $payment->sms_sent_at = now();
                $payment->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Control number imetumwa tena kwa mafanikio'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Kushindwa kutuma SMS: ' . $smsResult['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error resending control number: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kosa la kutuma SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment status (when payment is received from bank)
     */
    public function update_payment_status(Request $request, $paymentID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
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
            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment haijapatikana'
                ], 404);
            }

            $amountPaid = $request->amount_paid;
            $totalAmountPaid = $payment->amount_paid + $amountPaid;
            $newBalance = $payment->amount_required - $totalAmountPaid;
            
            // Update payment
            $payment->amount_paid = $totalAmountPaid;
            $payment->balance = $newBalance;
            $payment->payment_reference = $request->payment_reference ?? $payment->payment_reference;
            $payment->notes = $request->notes ?? $payment->notes;
            $payment->payment_date = now();

            // Update status
            if ($newBalance <= 0) {
                $payment->payment_status = $newBalance < 0 ? 'Overpaid' : 'Paid';
            } else if ($totalAmountPaid > 0 && $totalAmountPaid < $payment->amount_required) {
                $payment->payment_status = 'Incomplete Payment';
            } else {
                $payment->payment_status = 'Pending';
            }

            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment status imesasishwa kwa mafanikio',
                'payment' => $payment->load(['student', 'fee'])
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kosa la kusasisha payment: ' . $e->getMessage()
            ], 500);
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
            $tuitionPayments = $payments->where('fee_type', 'Tuition Fees');
            $otherFeePayments = $payments->where('fee_type', 'Other Fees');

            // Get debt amounts
            $tuitionDebt = $previousYearDebt['school_fee_balance'];
            $otherDebt = $previousYearDebt['other_contribution_balance'];
            
            // Calculate base amounts from fees table (not from payment amount_required which may include debt)
            // Get student's class
            $classID = $student->subclass->classID ?? null;
            $tuitionBaseRequired = 0;
            $otherBaseRequired = 0;
            
            if ($classID) {
                // Get current fees for student's class
                $tuitionFees = Fee::where('schoolID', $schoolID)
                    ->where('classID', $classID)
                    ->where('fee_type', 'Tuition Fees')
                    ->where('status', 'Active')
                    ->get();
                $tuitionBaseRequired = $tuitionFees->sum('amount');
                
                $otherFees = Fee::where('schoolID', $schoolID)
                    ->where('classID', $classID)
                    ->where('fee_type', 'Other Fees')
                    ->where('status', 'Active')
                    ->get();
                $otherBaseRequired = $otherFees->sum('amount');
            }
            
            // If no fees found, fallback to payment amount_required (but subtract debt if it was included)
            if ($tuitionBaseRequired == 0 && $tuitionPayments->count() > 0) {
                $paymentAmountRequired = $tuitionPayments->sum('amount_required');
                // If payment amount_required is greater than debt, assume debt was included
                if ($paymentAmountRequired > $tuitionDebt) {
                    $tuitionBaseRequired = $paymentAmountRequired - $tuitionDebt;
                } else {
                    $tuitionBaseRequired = $paymentAmountRequired;
                }
            }
            
            if ($otherBaseRequired == 0 && $otherFeePayments->count() > 0) {
                $paymentAmountRequired = $otherFeePayments->sum('amount_required');
                // If payment amount_required is greater than debt, assume debt was included
                if ($paymentAmountRequired > $otherDebt) {
                    $otherBaseRequired = $paymentAmountRequired - $otherDebt;
                } else {
                    $otherBaseRequired = $paymentAmountRequired;
                }
            }
            
            // Calculate total required (base + debt)
            $tuitionRequired = $tuitionBaseRequired + $tuitionDebt;
            $otherRequired = $otherBaseRequired + $otherDebt;
            
            // Get paid amounts
            $tuitionPaid = $tuitionPayments->sum('amount_paid');
            $otherPaid = $otherFeePayments->sum('amount_paid');
            
            // Calculate balances
            $tuitionBalance = $tuitionRequired - $tuitionPaid;
            $otherBalance = $otherRequired - $otherPaid;

            $totalRequired = $tuitionRequired + $otherRequired;
            $totalPaid = $tuitionPaid + $otherPaid;
            $totalBalance = $tuitionBalance + $otherBalance;

            // School logo path
            $schoolLogo = $school->school_logo ? public_path($school->school_logo) : null;

            // Prepare data for PDF
            $data = [
                'student' => $student,
                'school' => $school,
                'payments' => $payments,
                'tuitionPayments' => $tuitionPayments,
                'otherFeePayments' => $otherFeePayments,
                'tuitionBaseRequired' => $tuitionBaseRequired,
                'tuitionDebt' => $tuitionDebt,
                'tuitionRequired' => $tuitionRequired,
                'tuitionPaid' => $tuitionPaid,
                'tuitionBalance' => $tuitionBalance,
                'otherBaseRequired' => $otherBaseRequired,
                'otherDebt' => $otherDebt,
                'otherRequired' => $otherRequired,
                'otherPaid' => $otherPaid,
                'otherBalance' => $otherBalance,
                'totalRequired' => $totalRequired,
                'totalPaid' => $totalPaid,
                'totalBalance' => $totalBalance,
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
            // Build validation rules based on payment source
            $rules = [
                'paymentID' => 'required|exists:payments,paymentID',
                'paid_amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_source' => 'required|in:Cash,Bank',
                'notes' => 'nullable|string',
            ];
            
            // Reference number required only for Bank payments
            if ($request->payment_source === 'Bank') {
                $rules['reference_number'] = 'required|string|unique:payment_records,reference_number';
                $rules['bank_name'] = 'required|string|max:200';
            } else {
                $rules['reference_number'] = 'nullable|string|unique:payment_records,reference_number';
                $rules['bank_name'] = 'nullable|string|max:200';
            }
            
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get payment
            $payment = Payment::where('schoolID', $schoolID)
                ->where('paymentID', $request->paymentID)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.'
                ], 404);
            }

            // Check if payment belongs to a closed academic year
            if ($payment->academic_yearID) {
                $academicYear = DB::table('academic_years')
                    ->where('academic_yearID', $payment->academic_yearID)
                    ->first();
                
                if ($academicYear && $academicYear->status === 'Closed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot record payment for a closed academic year. Please record payments only for the active academic year.'
                    ], 403);
                }
            }

            DB::beginTransaction();

            // Create payment record
            $paymentRecordData = [
                'paymentID' => $payment->paymentID,
                'paid_amount' => $request->paid_amount,
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
                'notes' => $request->notes,
            ];
            
            // Add reference number if provided
            if ($request->filled('reference_number')) {
                $paymentRecordData['reference_number'] = $request->reference_number;
            }
            
            // Add bank name if provided
            if ($request->filled('bank_name')) {
                $paymentRecordData['bank_name'] = $request->bank_name;
            }
            
            $paymentRecord = PaymentRecord::create($paymentRecordData);

            // Update payment totals from payment_records
            $totalPaid = PaymentRecord::where('paymentID', $payment->paymentID)
                ->sum('paid_amount');

            $balance = $payment->amount_required - $totalPaid;

            // Update payment status
            $paymentStatus = 'Pending';
            if ($balance <= 0) {
                $paymentStatus = $totalPaid > $payment->amount_required ? 'Overpaid' : 'Paid';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'Incomplete Payment';
            }

            $payment->update([
                'amount_paid' => $totalPaid,
                'balance' => $balance,
                'payment_status' => $paymentStatus,
                'payment_date' => $request->payment_date,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment_record' => $paymentRecord
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ], 500);
        }
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
            // Build validation rules based on payment source
            $rules = [
                'recordID' => 'required|exists:payment_records,recordID',
                'paymentID' => 'required|exists:payments,paymentID',
                'paid_amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_source' => 'required|in:Cash,Bank',
                'notes' => 'nullable|string',
            ];
            
            // Reference number required only for Bank payments
            if ($request->payment_source === 'Bank') {
                $rules['reference_number'] = 'required|string|unique:payment_records,reference_number,' . $request->recordID . ',recordID';
                $rules['bank_name'] = 'required|string|max:200';
            } else {
                $rules['reference_number'] = 'nullable|string|unique:payment_records,reference_number,' . $request->recordID . ',recordID';
                $rules['bank_name'] = 'nullable|string|max:200';
            }
            
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify payment belongs to this school
            $payment = Payment::where('schoolID', $schoolID)
                ->where('paymentID', $request->paymentID)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.'
                ], 404);
            }

            // Check if payment record belongs to this payment
            $paymentRecord = PaymentRecord::where('recordID', $request->recordID)
                ->where('paymentID', $request->paymentID)
                ->first();

            if (!$paymentRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.'
                ], 404);
            }

            DB::beginTransaction();

            // Update payment record
            $paymentRecordData = [
                'paid_amount' => $request->paid_amount,
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
                'notes' => $request->notes,
            ];
            
            // Add reference number if provided
            if ($request->filled('reference_number')) {
                $paymentRecordData['reference_number'] = $request->reference_number;
            } else {
                $paymentRecordData['reference_number'] = null;
            }
            
            // Add bank name if provided
            if ($request->filled('bank_name')) {
                $paymentRecordData['bank_name'] = $request->bank_name;
            } else {
                $paymentRecordData['bank_name'] = null;
            }
            
            $paymentRecord->update($paymentRecordData);

            // Update payment totals from payment_records
            $totalPaid = PaymentRecord::where('paymentID', $payment->paymentID)
                ->sum('paid_amount');

            $balance = $payment->amount_required - $totalPaid;

            // Update payment status
            $paymentStatus = 'Pending';
            if ($balance <= 0) {
                $paymentStatus = $totalPaid > $payment->amount_required ? 'Overpaid' : 'Paid';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'Incomplete Payment';
            }

            $payment->update([
                'amount_paid' => $totalPaid,
                'balance' => $balance,
                'payment_status' => $paymentStatus,
                'payment_date' => $request->payment_date,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment record updated successfully',
                'payment_record' => $paymentRecord
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating payment record: ' . $e->getMessage()
            ], 500);
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

            // Delete payment record
            $paymentRecord->delete();

            // Update payment totals from payment_records
            $totalPaid = PaymentRecord::where('paymentID', $payment->paymentID)
                ->sum('paid_amount');

            $balance = $payment->amount_required - $totalPaid;

            // Update payment status
            $paymentStatus = 'Pending';
            if ($balance <= 0) {
                $paymentStatus = $totalPaid > $payment->amount_required ? 'Overpaid' : 'Paid';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'Incomplete Payment';
            }

            $payment->update([
                'amount_paid' => $totalPaid,
                'balance' => $balance,
                'payment_status' => $paymentStatus,
            ]);

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
            $feeType = $request->input('fee_type', '');
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
                }, 'payments.fee.installments', 'payments.fee.otherFeeDetails', 'payments.academicYear']);

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
                // Get payments for this student
                $payments = $student->payments()
                    ->where(function($q) use ($academicYearID, $year) {
                        if ($academicYearID) {
                            $q->where('academic_yearID', $academicYearID);
                        } else {
                            $q->whereYear('created_at', $year);
                        }
                    })
                    ->with(['fee.installments', 'fee.otherFeeDetails'])
                    ->get()
                    ->filter(function($payment) use ($feeType, $paymentStatus) {
                        if (!empty($feeType) && $payment->fee_type !== $feeType) {
                            return false;
                        }
                        if (!empty($paymentStatus)) {
                            $statusMap = ['Incomplete' => 'Incomplete Payment'];
                            $actualStatus = $statusMap[$paymentStatus] ?? $paymentStatus;
                            if ($payment->payment_status !== $actualStatus) {
                                return false;
                            }
                        }
                        return true;
                    });

                if ($payments->isEmpty() && (!empty($feeType) || !empty($paymentStatus))) {
                    continue;
                }

                // Calculate totals
                $tuitionPayments = $payments->where('fee_type', 'Tuition Fees');
                $otherFeePayments = $payments->where('fee_type', 'Other Fees');

                $classID = $student->subclass->classID ?? null;
                $tuitionBaseRequired = 0;
                $otherBaseRequired = 0;
                
                if ($classID) {
                    $tuitionFees = Fee::where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->where('fee_type', 'Tuition Fees')
                        ->where('status', 'Active')
                        ->get();
                    $tuitionBaseRequired = $tuitionFees->sum('amount');
                    
                    $otherFees = Fee::where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->where('fee_type', 'Other Fees')
                        ->where('status', 'Active')
                        ->get();
                    $otherBaseRequired = $otherFees->sum('amount');
                }

                $tuitionDebt = 0;
                $otherDebt = 0;
                
                if (!$isClosedYear && $student->status === 'Active') {
                    $previousYearDebt = $this->getPreviousYearBalance($student->studentID, $schoolID);
                    $tuitionDebt = $previousYearDebt['school_fee_balance'];
                    $otherDebt = $previousYearDebt['other_contribution_balance'];
                }

                $tuitionRequired = $tuitionBaseRequired + $tuitionDebt;
                $otherRequired = $otherBaseRequired + $otherDebt;
                
                $tuitionPaid = $tuitionPayments->sum('amount_paid');
                $otherPaid = $otherFeePayments->sum('amount_paid');
                
                $tuitionBalance = $tuitionRequired - $tuitionPaid;
                $otherBalance = $otherRequired - $otherPaid;

                $totalRequired = $tuitionRequired + $otherRequired;
                $totalPaid = $tuitionPaid + $otherPaid;
                $totalBalance = $tuitionBalance + $otherBalance;

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
                    'tuitionPayments' => $tuitionPayments,
                    'otherFeePayments' => $otherFeePayments,
                    'tuitionBaseRequired' => $tuitionBaseRequired,
                    'tuitionDebt' => $tuitionDebt,
                    'tuitionRequired' => $tuitionRequired,
                    'tuitionPaid' => $tuitionPaid,
                    'tuitionBalance' => $tuitionBalance,
                    'otherBaseRequired' => $otherBaseRequired,
                    'otherDebt' => $otherDebt,
                    'otherRequired' => $otherRequired,
                    'otherPaid' => $otherPaid,
                    'otherBalance' => $otherBalance,
                    'totalRequired' => $totalRequired,
                    'totalPaid' => $totalPaid,
                    'totalBalance' => $totalBalance,
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
                    'fee_type' => $feeType,
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
}
