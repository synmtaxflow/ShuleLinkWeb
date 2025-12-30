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

            $fee = Fee::create([
                'schoolID' => $schoolID,
                'classID' => $request->classID,
                'fee_type' => $request->fee_type,
                'fee_name' => $request->fee_name,
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
                'fee_name' => $request->fee_name,
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

        // Get available years from payments (based on created_at)
        $availableYears = Payment::where('schoolID', $schoolID)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Get current year
        $currentYear = date('Y');
        
        // Add current year if not in list
        if (!in_array($currentYear, $availableYears)) {
            $availableYears[] = $currentYear;
        }
        
        // Add past years (last 10 years) to show historical data option
        // This ensures users can filter by old years even if no payments exist yet
        for ($i = 1; $i <= 10; $i++) {
            $pastYear = $currentYear - $i;
            if (!in_array($pastYear, $availableYears)) {
                $availableYears[] = $pastYear;
            }
        }
        
        // Sort years in descending order (newest first)
        rsort($availableYears);

        return view('Admin.viewPayments', compact('school', 'availableYears', 'currentYear'));
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
            $search = $request->input('search', '');
            $feeType = $request->input('fee_type', '');
            $paymentStatus = $request->input('payment_status', '');
            $smsStatus = $request->input('sms_status', '');
            $year = $request->input('year', date('Y')); // Default to current year

            // Build query
            $query = Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with(['parent', 'subclass.class', 'payments' => function($q) use ($year) {
                    $q->whereYear('created_at', $year);
                }, 'payments.fee.installments', 'payments.fee.otherFeeDetails']);

            // Search by student name or admission number
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('admission_number', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('middle_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'like', '%' . $search . '%');
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
                $filteredPayments = $student->payments->filter(function($payment) use ($year, $feeType, $paymentStatus, $smsStatus) {
                    // Filter by year
                    if ($year && date('Y', strtotime($payment->created_at)) != $year) {
                        return false;
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

                    // Filter by SMS status
                    if (!empty($smsStatus) && $payment->sms_sent !== $smsStatus) {
                        return false;
                    }

                    return true;
                });

                // Skip if no payments match filters (unless no filters applied)
                if ($filteredPayments->isEmpty() && (!empty($feeType) || !empty($paymentStatus) || !empty($smsStatus))) {
                    continue;
                }

                // Format student data for JSON
                $studentImgPath = $student->photo
                    ? asset('userImages/' . $student->photo)
                    : ($student->gender == 'Female'
                        ? asset('images/female.png')
                        : asset('images/male.png'));
                
                $studentData = [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'photo' => $studentImgPath,
                    'parent' => $student->parent ? [
                        'first_name' => $student->parent->first_name,
                        'last_name' => $student->parent->last_name,
                        'phone' => $student->parent->phone,
                    ] : null,
                    'subclass' => $student->subclass ? [
                        'subclass_name' => $student->subclass->subclass_name,
                    ] : null,
                ];

                // Aggregate payments by fee type
                $tuitionPayments = $filteredPayments->where('fee_type', 'Tuition Fees');
                $otherFeePayments = $filteredPayments->where('fee_type', 'Other Fees');

                // Calculate totals for Tuition Fees
                $tuitionRequired = $tuitionPayments->sum('amount_required');
                // Calculate paid amount from payment_records
                $tuitionPaid = 0;
                foreach ($tuitionPayments as $payment) {
                    $paid = PaymentRecord::where('paymentID', $payment->paymentID)->sum('paid_amount');
                    $tuitionPaid += $paid ?: 0;
                }
                $tuitionBalance = $tuitionRequired - $tuitionPaid;

                // Calculate totals for Other Fees
                $otherRequired = $otherFeePayments->sum('amount_required');
                // Calculate paid amount from payment_records
                $otherPaid = 0;
                foreach ($otherFeePayments as $payment) {
                    $paid = PaymentRecord::where('paymentID', $payment->paymentID)->sum('paid_amount');
                    $otherPaid += $paid ?: 0;
                }
                $otherBalance = $otherRequired - $otherPaid;

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
                    $actualPaid = PaymentRecord::where('paymentID', $payment->paymentID)->sum('paid_amount');
                    $actualPaid = $actualPaid ?: 0;
                    $actualBalance = $payment->amount_required - $actualPaid;
                    
                    $paymentData = [
                        'paymentID' => $payment->paymentID,
                        'feeID' => $payment->feeID,
                        'fee_type' => $payment->fee_type,
                        'control_number' => $payment->control_number,
                        'amount_required' => (float) $payment->amount_required,
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

            return response()->json([
                'success' => true,
                'data' => $filteredData,
                'statistics' => [
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

            // Get all active students
            $students = Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
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
                    
                    // Check if student already has tuition payment
                    $existingTuitionPayment = Payment::where('studentID', $student->studentID)
                        ->where('fee_type', 'Tuition Fees')
                        ->where('payment_status', '!=', 'Paid')
                        ->first();

                    if (!$existingTuitionPayment) {
                        $controlNumber = $this->generateControlNumber($schoolID, $student->studentID, 'TUITION');
                        
                        Payment::create([
                            'schoolID' => $schoolID,
                            'studentID' => $student->studentID,
                            'feeID' => null, // Aggregate for all tuition fees
                            'fee_type' => 'Tuition Fees',
                            'control_number' => $controlNumber,
                            'amount_required' => $tuitionAmount,
                            'amount_paid' => 0,
                            'balance' => $tuitionAmount,
                            'payment_status' => 'Pending',
                            'sms_sent' => 'No',
                        ]);
                        $generated++;
                    }
                }

                // Generate control number for Other Fees if exists
                if ($otherFees->count() > 0) {
                    $otherAmount = $otherFees->sum('amount');
                    
                    // Check if student already has other fees payment
                    $existingOtherPayment = Payment::where('studentID', $student->studentID)
                        ->where('fee_type', 'Other Fees')
                        ->where('payment_status', '!=', 'Paid')
                        ->first();

                    if (!$existingOtherPayment) {
                        $controlNumber = $this->generateControlNumber($schoolID, $student->studentID, 'OTHER');
                        
                        Payment::create([
                            'schoolID' => $schoolID,
                            'studentID' => $student->studentID,
                            'feeID' => null, // Aggregate for all other fees
                            'fee_type' => 'Other Fees',
                            'control_number' => $controlNumber,
                            'amount_required' => $otherAmount,
                            'amount_paid' => 0,
                            'balance' => $otherAmount,
                            'payment_status' => 'Pending',
                            'sms_sent' => 'No',
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
     */
    private function generateControlNumber($schoolID, $studentID, $type = 'TUITION')
    {
        do {
            // Format: SCHOOLID-STUDENTID-TYPE-TIMESTAMP (last 6 digits)
            $timestamp = substr(time(), -6);
            $typeCode = $type === 'TUITION' ? 'T' : 'O';
            $controlNumber = $schoolID . str_pad($studentID, 4, '0', STR_PAD_LEFT) . $typeCode . $timestamp;
            
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

            // Get current year payments
            $currentYear = date('Y');
            $payments = $student->payments()
                ->whereYear('created_at', $currentYear)
                ->with(['fee.installments', 'fee.otherFeeDetails'])
                ->get();

            // Calculate totals
            $tuitionPayments = $payments->where('fee_type', 'Tuition Fees');
            $otherFeePayments = $payments->where('fee_type', 'Other Fees');

            $tuitionRequired = $tuitionPayments->sum('amount_required');
            $tuitionPaid = $tuitionPayments->sum('amount_paid');
            $tuitionBalance = $tuitionPayments->sum('balance');

            $otherRequired = $otherFeePayments->sum('amount_required');
            $otherPaid = $otherFeePayments->sum('amount_paid');
            $otherBalance = $otherFeePayments->sum('balance');

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
                'tuitionRequired' => $tuitionRequired,
                'tuitionPaid' => $tuitionPaid,
                'tuitionBalance' => $tuitionBalance,
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
            $validator = Validator::make($request->all(), [
                'paymentID' => 'required|exists:payments,paymentID',
                'paid_amount' => 'required|numeric|min:0.01',
                'reference_number' => 'required|string|unique:payment_records,reference_number',
                'payment_date' => 'required|date',
                'payment_source' => 'required|in:Cash,Bank',
                'notes' => 'nullable|string',
            ]);

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

            DB::beginTransaction();

            // Create payment record
            $paymentRecord = PaymentRecord::create([
                'paymentID' => $payment->paymentID,
                'paid_amount' => $request->paid_amount,
                'reference_number' => $request->reference_number,
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
                'notes' => $request->notes,
            ]);

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
}
