<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\PaymentItem;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::with(['classroom', 'payments' => function ($q) {
            $q->whereIn('payment_type', ['Monthly', 'Admission'])
                ->select('student_id', 'month', 'fee_details', 'payment_type');
        }]);

        // Default: show only active students. show_inactive=1 shows only inactive.
        if ($request->input('show_inactive') == '1') {
            $query->where('is_active', false);
        } else {
            $query->where('is_active', true);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search by name or ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->get();
        $classrooms = Classroom::all();

        // Always count total active students (unaffected by current filters)
        $activeCount = Student::where('is_active', true)->count();

        if ($request->filled('class_id')) {
            $selectedClass = $classrooms->find($request->class_id);
            $sections = $selectedClass ? $selectedClass->sections : [];
        } else {
            // Get all unique sections from all classrooms
            $sections = $classrooms->pluck('sections')->flatten()->unique()->sort()->values()->all();
        }

        $showInactive = $request->input('show_inactive') == '1';
        return view('students.index', compact('students', 'classrooms', 'sections', 'showInactive', 'activeCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classrooms = Classroom::all();

        // Generate initial student ID (will be updated when class is selected)
        $student_id = $this->generateStudentIdInternal(null);

        // Pass classroom data (fees & sections) as JSON for JavaScript
        $classroomData = $classrooms->mapWithKeys(function ($classroom) {
            return [$classroom->id => [
                'fees' => $classroom->fees,
                'admission_fee' => $classroom->admission_fee,
                'monthly_fee' => $classroom->monthly_fee,
                'total_fee' => $classroom->total_fee,
                'sections' => $classroom->sections ?? []
            ]];
        });

        return view('students.create', compact('classrooms', 'student_id', 'classroomData'));
    }

    /**
     * AJAX endpoint to generate student ID for selected class
     */
    public function generateStudentId($classId)
    {
        $studentId = $this->generateStudentIdInternal($classId);
        return response()->json(['student_id' => $studentId]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();

        // Regenerate student ID to ensure it's correct and unique
        $validated['student_id'] = $this->generateStudentIdInternal($validated['class_id']);

        // Double-check uniqueness and increment if necessary
        $originalId = $validated['student_id'];
        $counter = 1;
        while (Student::where('student_id', $validated['student_id'])->exists()) {
            // If ID exists, increment the sequential number
            $validated['student_id'] = $originalId + $counter;
            $counter++;
        }

        // Process discounts from JSON if available, otherwise check old fee_discounts
        $discounts = [];
        if ($request->has('student_assigned_fees') && !empty($request->student_assigned_fees)) {
            $assignedFees = json_decode($request->student_assigned_fees, true);
            if (is_array($assignedFees)) {
                foreach ($assignedFees as $fee) {
                    if (!empty($fee['is_permanent']) && !empty($fee['discount'])) {
                        $discounts[$fee['name']] = [
                            'amount' => floatval($fee['discount']),
                            'permanent' => 1
                        ];
                    }
                }
            }
        } elseif ($request->has('fee_discounts')) {
            foreach ($request->fee_discounts as $feeName => $amount) {
                if ($amount > 0) {
                    $permanent = isset($request->fee_permanent[$feeName]) ? 1 : 0;
                    $discounts[$feeName] = [
                        'amount' => $amount,
                        'permanent' => $permanent
                    ];
                }
            }
        }
        $validated['discounts'] = $discounts;

        // Handle file upload
        if ($request->hasFile('nid_file')) {
            $validated['nid_file_path'] = $request->file('nid_file')->store('nid_files', 'public');
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('student_photos', 'public');
        }

        // Save selected fees (subscribed fees list)
        if ($request->has('student_assigned_fees') && !empty($request->student_assigned_fees)) {
            $validated['selected_fees'] = json_decode($request->student_assigned_fees, true);
        }

        // Convert program_type array to comma-separated string
        if (isset($validated['program_type']) && is_array($validated['program_type'])) {
            $validated['program_type'] = implode(', ', $validated['program_type']);
        }


        // Student will be created later after fee processing and ID generation


        // Create admission payment record
        // Build fee details array from ONLY the fees that were selected (checked) in the form
        $feeDetails = [];

        // The JavaScript collectAdmissionFees() function stores selected fees in 'selected_admission_fees' field
        if ($request->has('selected_admission_fees') && !empty($request->selected_admission_fees)) {
            $selectedFees = json_decode($request->selected_admission_fees, true);

            // Debug logging
            \Log::info('=== STUDENT CREATION DEBUG ===');
            \Log::info('Selected Admission Fees JSON:', ['data' => $request->selected_admission_fees]);
            \Log::info('Decoded Fees:', ['fees' => $selectedFees]);

            if (is_array($selectedFees)) {
                foreach ($selectedFees as $fee) {
                    $feeName = $fee['name'] ?? '';
                    $feeAmount = floatval($fee['amount'] ?? 0);
                    $feeType = $fee['type'] ?? 'Admission';
                    $feeMonth = $fee['month'] ?? null;
                    $originalAmount = floatval($fee['original_amount'] ?? $feeAmount);
                    $discount = floatval($fee['discount'] ?? 0);

                    \Log::info('Processing Fee:', [
                        'name' => $feeName,
                        'amount' => $feeAmount,
                        'original_amount' => $originalAmount,
                        'discount' => $discount
                    ]);

                    // The amount from JavaScript is already after discount, so use it directly
                    if ($feeAmount > 0 && !empty($feeName)) {
                        $feeDetail = [
                            'name' => $feeName,
                            'type' => $feeType,
                            'amount' => $feeAmount,
                            'original_amount' => $originalAmount,
                            'discount' => $discount
                        ];

                        // For Monthly/Quarterly/Half-Yearly fees, parse month and year from fee name
                        if (in_array($feeType, ['Monthly', 'Quarterly', 'Half Yearly', 'Half-Yearly', 'Half_Yearly'])) {
                            // Fee name format: "Monthly Tuition Fee - August, 26"
                            // Extract base name and month/year
                            if (preg_match('/^(.+?)\s*-\s*([A-Za-z]+),\s*(\d+)$/', $feeName, $matches)) {
                                $baseName = trim($matches[1]);
                                $monthName = trim($matches[2]);
                                $yearValue = trim($matches[3]);

                                $feeDetail['base_name'] = $baseName;
                                $feeDetail['month'] = $monthName;
                                $feeDetail['year'] = $yearValue;
                            } elseif (preg_match('/^(.+?)\s*-\s*(.+)$/', $feeName, $matches)) {
                                // Fallback for other formats like "Fee - 1st Half"
                                $baseName = trim($matches[1]);
                                $monthPart = trim($matches[2]);

                                $feeDetail['base_name'] = $baseName;
                                $feeDetail['month'] = $monthPart;
                            }
                        } else {
                            // For non-monthly fees, base_name = fee_name
                            $feeDetail['base_name'] = $feeName;
                        }

                        // Add month information if it was provided separately (legacy)
                        if ($feeMonth && !isset($feeDetail['month'])) {
                            $feeDetail['month'] = $feeMonth;
                        }

                        $feeDetails[] = $feeDetail;
                    }
                }
            }

            \Log::info('Final Fee Details:', ['fee_details' => $feeDetails]);
            \Log::info('=============================');
        }

        // Add first month fee to fee_details if selected (legacy support)
        if ($request->pay_first_month === 'yes' && $request->first_month_fee > 0) {
            $classroom = \App\Models\Classroom::find($validated['class_id']);
            if ($classroom && $classroom->fees) {
                // Add each monthly fee component for the first month
                foreach ($classroom->fees as $fee) {
                    if (isset($fee['type']) && $fee['type'] === 'Monthly') {
                        $baseName = $fee['name'] ?? 'Monthly Fee';
                        $feeDetails[] = [
                            'name' => $baseName . ' - ' . $request->first_month . ', ' . date('y'),
                            'base_name' => $baseName,
                            'type' => 'Monthly',
                            'amount' => $fee['amount'] ?? 0,
                            'month' => $request->first_month,
                            'year' => date('y')
                        ];
                    }
                }
            }
        }

        // Handle partial payment for admission fees ONLY (not monthly fees)
        $isPartialPayment = $request->has('is_partial_payment') && $request->is_partial_payment == '1';
        $partialAmount = $isPartialPayment ? floatval($request->partial_amount ?? 0) : 0;
        $totalAdmissionFee = floatval($request->total_admission_fee ?? 0);

        // Separate admission fees from monthly fees
        $admissionFeeDetails = [];
        $monthlyFeeDetails = [];
        $totalAdmissionAmount = 0;

        foreach ($feeDetails as $fee) {
            $feeType = $fee['type'] ?? 'Admission';
            if (in_array(strtolower($feeType), ['monthly', 'quarterly', 'half yearly', 'half-yearly', 'half_yearly'])) {
                $monthlyFeeDetails[] = $fee;
            } else {
                $admissionFeeDetails[] = $fee;
                $totalAdmissionAmount += $fee['amount'];
            }
        }

        $actualPaymentAmount = $totalAdmissionAmount;
        $finalFeeDetails = [];

        if ($isPartialPayment && $partialAmount > 0 && $partialAmount < $totalAdmissionFee) {
            // Calculate actual payment: partial admission + monthly fees + other one-time fees
            $otherOneTimeFees = 0;
            foreach ($admissionFeeDetails as $fee) {
                if ($fee['name'] !== 'Admission Fee') {
                    $otherOneTimeFees += $fee['amount'];
                }
            }
            $actualPaymentAmount = $partialAmount + array_sum(array_column($monthlyFeeDetails, 'amount')) + $otherOneTimeFees;

            $remainingAmount = $totalAdmissionFee - $partialAmount;

            // Store original fee details for partial_payments tracking
            $partialPaymentFees = [];

            foreach ($admissionFeeDetails as $fee) {
                if ($fee['name'] === 'Admission Fee') {
                    // Apply partial payment ONLY to Admission Fee
                    $finalFeeDetails[] = [
                        'name' => $fee['name'] . ' (Partial)',
                        'type' => $fee['type'],
                        'amount' => $partialAmount,
                        'original_amount' => $fee['amount'],
                        'discount' => $fee['discount'] ?? 0,
                        'month' => $fee['month'] ?? null
                    ];

                    // Track this fee for partial payments
                    $partialPaymentFees[$fee['name']] = [
                        'total' => $fee['amount'],
                        'paid' => $partialAmount,
                        'remaining' => $remainingAmount,
                        'payment_ids' => []
                    ];
                } else {
                    // Other one-time fees (Exam Fee, Year Fee, etc.) are paid in full
                    $finalFeeDetails[] = $fee;
                }
            }

            // Add monthly fees as-is (not affected by partial payment)
            $finalFeeDetails = array_merge($finalFeeDetails, $monthlyFeeDetails);

            // Store partial payment info in student record
            $validated['partial_payments'] = $partialPaymentFees;

            \Log::info('Partial Payment:', [
                'total_admission' => $totalAdmissionAmount,
                'paying_now' => $partialAmount,
                'remaining' => $remainingAmount,
                'fees' => $partialPaymentFees
            ]);
        } else {
            // No partial payment - use all fees as-is
            $finalFeeDetails = $feeDetails;
            $actualPaymentAmount = array_sum(array_column($feeDetails, 'amount'));
        }

        // Replace feeDetails with the final version
        $feeDetails = $finalFeeDetails;

        // Calculate sub_total and discount from fee details for payment record
        $paymentSubTotal = 0;
        $paymentDiscount = 0;
        foreach ($feeDetails as $fee) {
            $paymentSubTotal += floatval($fee['original_amount'] ?? $fee['amount'] ?? 0);
            $paymentDiscount += floatval($fee['discount'] ?? 0);
        }

        // Create the student with retry logic for duplicate ID
        $maxRetries = 5;
        $retryCount = 0;
        $student = null;
        $created = false;

        do {
            try {
                // Re-check uniqueness before attempting insert
                while (Student::where('student_id', $validated['student_id'])->exists()) {
                    $validated['student_id'] = $originalId + $counter;
                    $counter++;
                }

                $student = Student::create($validated);
                $created = true;
            } catch (\Illuminate\Database\QueryException $e) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1062) { // Duplicate entry
                    $retryCount++;
                    // Increment counter and loop again
                    $validated['student_id'] = $originalId + $counter;
                    $counter++;

                    if ($retryCount >= $maxRetries) {
                        throw $e; // Give up after max retries
                    }
                } else {
                    throw $e; // Throw other errors
                }
            }
        } while (!$created && $retryCount < $maxRetries);

        // Compute final_amount the same way PaymentController::store() does,
        // so the receipt's reconciliation logic works for admission payments.
        $paymentFinalAmount = max(0, $paymentSubTotal - $paymentDiscount);

        // Create admission payment record.
        // NOTE: column is `total_discount` (not `discount`), and `final_amount`
        // must be written so the receipt view can reconcile the totals.
        $admissionPayment = $student->payments()->create([
            'amount' => $actualPaymentAmount,
            'sub_total' => $paymentSubTotal,
            'total_discount' => $paymentDiscount,
            'final_amount' => $paymentFinalAmount,
            'payment_type' => 'Admission',
            'payment_mode' => $validated['payment_mode'],
            'month' => 'Admission',
            'note' => $validated['payment_note'] ?? null,
            'payment_date' => now(),
            'fee_details' => $feeDetails,
        ]);

        // Mirror PaymentController::store(): create one PaymentItem per fee
        // so the receipt (which reads $payment->payment_items) renders correctly.
        // NOTE: payment_items.student_id is the students.id PK (FK constraint),
        // NOT the students.student_id string — same convention PaymentController uses.
        if (!empty($feeDetails)) {
            foreach ($feeDetails as $fee) {
                PaymentItem::create([
                    'payment_id' => $admissionPayment->id,
                    'student_id' => $student->id,
                    'fee_name' => $fee['name'],
                    'base_fee_name' => $fee['base_name'] ?? $fee['name'],
                    'fee_type' => $fee['type'] ?? 'Other',
                    'month' => $fee['month'] ?? null,
                    'year' => $fee['year'] ?? null,
                    'amount' => $fee['amount'],
                    'original_amount' => $fee['original_amount'] ?? $fee['amount'],
                    'discount' => $fee['discount'] ?? 0,
                ]);
            }
        }

        // Mark months as paid in student_months table if monthly fees were included
        $monthlyFeesInPayment = collect($feeDetails)->filter(function ($fee) {
            return isset($fee['type']) && strtolower($fee['type']) === 'monthly' && isset($fee['month']) && isset($fee['year']);
        });

        if ($monthlyFeesInPayment->isNotEmpty()) {
            // Get student's assigned monthly fees
            $selectedFees = $student->selected_fees ?? [];
            $monthlyFees = collect($selectedFees)->filter(function ($fee) {
                return isset($fee['type']) && strtolower($fee['type']) === 'monthly';
            });
            $requiredMonthlyFeeNames = $monthlyFees->pluck('name')->unique()->toArray();

            // Get paid monthly fee names from payment
            $paidMonthlyFeeNames = [];
            foreach ($monthlyFeesInPayment as $fee) {
                // Use base_name field directly
                if (!empty($fee['base_name'])) {
                    $paidMonthlyFeeNames[] = $fee['base_name'];
                }
            }

            // Determine payment status based on fee coverage
            $paidFeeCount = count(array_intersect($requiredMonthlyFeeNames, $paidMonthlyFeeNames));
            $totalRequiredFees = count($requiredMonthlyFeeNames);

            if ($paidFeeCount > 0) {
                $paymentStatus = ($paidFeeCount >= $totalRequiredFees) ? 'paid' : 'partial';

                // Create StudentMonth records for each unique month
                $processedMonths = [];
                foreach ($monthlyFeesInPayment as $fee) {
                    $monthKey = $fee['month'] . ', ' . $fee['year'];

                    // Avoid duplicates
                    if (!in_array($monthKey, $processedMonths)) {
                        $processedMonths[] = $monthKey;

                        // Check if existing record exists
                        $existingRecord = \App\Models\StudentMonth::where('student_id', $student->id)
                            ->where('month_key', $monthKey)
                            ->first();

                        if ($existingRecord) {
                            // Update existing record - upgrade to paid if all fees now covered
                            if ($paymentStatus === 'paid') {
                                $existingRecord->update([
                                    'status' => 'paid',
                                    'payment_id' => $admissionPayment->id
                                ]);
                            }
                        } else {
                            // Create new record with calculated status
                            \App\Models\StudentMonth::create([
                                'student_id' => $student->id,
                                'month_key' => $monthKey,
                                'status' => $paymentStatus,
                                'payment_id' => $admissionPayment->id
                            ]);
                        }
                    }
                }
            }
        }

        // Update partial_payments with payment ID for each fee
        if ($isPartialPayment && $partialAmount > 0 && $partialAmount < $totalAdmissionAmount) {
            $partialPayments = $student->partial_payments ?? [];
            foreach ($partialPayments as $feeName => $feeData) {
                $partialPayments[$feeName]['payment_ids'][] = $admissionPayment->id;
            }
            $student->update(['partial_payments' => $partialPayments]);
        }

        // Redirect to receipt confirmation page with success message
        return redirect()->route('students.receipt.confirm', $student)
            ->with('success', 'Student created successfully! You can download the admission form and receipt below.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $student->load(['classroom', 'payments']);
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $classrooms = Classroom::all();

        // Pass classroom data (fees & sections) as JSON for JavaScript
        $classroomData = $classrooms->mapWithKeys(function ($classroom) {
            return [$classroom->id => [
                'fees' => $classroom->fees,
                'admission_fee' => $classroom->admission_fee,
                'total_fee' => $classroom->total_fee,
                'sections' => $classroom->sections ?? []
            ]];
        });

        // Auto-detect Admission Fee discount from payment history if not explicitly saved in student profile
        $existingDiscounts = $student->discounts ?? [];
        if (!isset($existingDiscounts['Admission Fee'])) {
            $admissionPayment = $student->payments()
                ->where('payment_type', 'Admission')
                ->latest()
                ->first();

            if ($admissionPayment && is_array($admissionPayment->fee_details)) {
                foreach ($admissionPayment->fee_details as $detail) {
                    if (($detail['name'] ?? '') === 'Admission Fee') {
                        $discount = floatval($detail['discount'] ?? 0);
                        if ($discount > 0) {
                            $existingDiscounts['Admission Fee'] = [
                                'amount' => $discount,
                                'permanent' => 0
                            ];
                            $student->discounts = $existingDiscounts;
                        }
                        break;
                    }
                }
            }
        }

        return view('students.edit', compact('student', 'classrooms', 'classroomData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        // Handle file upload
        if ($request->hasFile('nid_file')) {
            // Delete old file if exists
            if ($student->nid_file_path) {
                Storage::disk('public')->delete($student->nid_file_path);
            }
            $validated['nid_file_path'] = $request->file('nid_file')->store('nid_files', 'public');
        }

        // Process discounts and selected fees
        if ($request->has('student_assigned_fees') && !empty($request->student_assigned_fees)) {
            $discounts = [];
            $selectedFees = [];
            $assignedFees = json_decode($request->student_assigned_fees, true);
            $existingSelectedFees = $student->selected_fees ?? [];

            if (is_array($assignedFees)) {
                foreach ($assignedFees as $fee) {
                    $feeName = $fee['name'];

                    // Add to selected fees
                    $feeEntry = [
                        'name' => $feeName,
                        'type' => $fee['type'] ?? 'Other',
                        'amount' => floatval($fee['amount'] ?? 0)
                    ];

                    // Preserve or assign 'since' to track when each fee was added
                    $existingFee = collect($existingSelectedFees)->firstWhere('name', $feeName);
                    if ($existingFee && isset($existingFee['since'])) {
                        $feeEntry['since'] = $existingFee['since']; // Keep original start date
                    } elseif (!$existingFee) {
                        $feeEntry['since'] = date('Y-m'); // New fee: starts this month
                    }

                    $selectedFees[] = $feeEntry;

                    // Add to discounts if discount exists (even if 0)
                    if (isset($fee['discount'])) {
                        $discounts[$feeName] = [
                            'amount' => floatval($fee['discount']),
                            'permanent' => !empty($fee['is_permanent']) ? 1 : 0
                        ];
                    }
                }
            }
            $validated['selected_fees'] = $selectedFees;
            $validated['discounts'] = $discounts;
        } elseif ($request->has('fee_discounts')) {
            $discounts = [];
            foreach ($request->fee_discounts as $feeName => $amount) {
                if ($amount > 0) {
                    $permanent = isset($request->fee_permanent[$feeName]) ? 1 : 0;
                    $discounts[$feeName] = [
                        'amount' => $amount,
                        'permanent' => $permanent
                    ];
                }
            }
            $validated['discounts'] = $discounts;
        } else {
            // If neither is present, it might be the simple edit form.
            // We should preserve existing discounts unless explicitly cleared.
            // We don't set 'discounts' in $validated so it stays as is in the database.
            unset($validated['discounts']);
        }

        $student->update($validated);

        // Update admission payment record whenever fees are modified
        if ($request->has('student_assigned_fees') && !empty($request->student_assigned_fees)) {
            $this->updateAdmissionPayment($student, $request);
        }

        return redirect()->route('students.show', $student->id)->with('success', 'Student updated successfully.');
    }

    /**
     * Update the admission payment record when fees are modified
     */
    protected function updateAdmissionPayment(Student $student, $request)
    {
        // Find the existing admission payment
        $admissionPayment = $student->payments()
            ->where('payment_type', 'Admission')
            ->first();

        if (!$admissionPayment) {
            \Log::warning("No admission payment found for student {$student->id}");
            return;
        }

        // Build new fee details from student_assigned_fees
        $feeDetails = [];
        if ($request->has('student_assigned_fees') && !empty($request->student_assigned_fees)) {
            $assignedFees = json_decode($request->student_assigned_fees, true);

            \Log::info('Update Admission Payment - Received Fees:', ['fees' => $assignedFees]);

            if (is_array($assignedFees)) {
                foreach ($assignedFees as $fee) {
                    $feeName = $fee['name'] ?? '';
                    $baseAmount = floatval($fee['amount'] ?? 0); // This is the base amount from JavaScript
                    $feeType = $fee['type'] ?? 'Other';
                    $discount = floatval($fee['discount'] ?? 0);

                    // Calculate net amount after discount
                    $netAmount = max(0, $baseAmount - $discount);

                    if ($baseAmount > 0 && !empty($feeName)) {
                        // Include ALL fees from Student's Fees section (including monthly fees)
                        $feeDetails[] = [
                            'name' => $feeName,
                            'type' => $feeType,
                            'amount' => $netAmount,  // Net amount after discount
                            'original_amount' => $baseAmount,  // Base amount before discount
                            'discount' => $discount
                        ];
                    }
                }
            }

            \Log::info('Update Admission Payment - Processed Fee Details:', ['fee_details' => $feeDetails]);
        }

        // Calculate totals
        $subTotal = 0;
        $totalDiscount = 0;
        $amount = 0;

        foreach ($feeDetails as $fee) {
            $subTotal += floatval($fee['original_amount'] ?? $fee['amount']);
            $totalDiscount += floatval($fee['discount'] ?? 0);
            $amount += floatval($fee['amount']);
        }

        $finalAmount = max(0, $subTotal - $totalDiscount);

        // Update the payment record
        $admissionPayment->update([
            'amount' => $amount,
            'sub_total' => $subTotal,
            'total_discount' => $totalDiscount,
            'final_amount' => $finalAmount,
            'fee_details' => $feeDetails,
        ]);

        // Delete old payment items
        $admissionPayment->payment_items()->delete();

        // Create new payment items
        if (!empty($feeDetails)) {
            foreach ($feeDetails as $fee) {
                PaymentItem::create([
                    'payment_id' => $admissionPayment->id,
                    'student_id' => $student->id,
                    'fee_name' => $fee['name'],
                    'base_fee_name' => $fee['base_name'] ?? $fee['name'],
                    'fee_type' => $fee['type'] ?? 'Other',
                    'month' => $fee['month'] ?? null,
                    'year' => $fee['year'] ?? null,
                    'amount' => $fee['amount'],
                    'original_amount' => $fee['original_amount'] ?? $fee['amount'],
                    'discount' => $fee['discount'] ?? 0,
                ]);
            }
        }

        \Log::info("Updated admission payment for student {$student->id} due to fee changes", [
            'payment_id' => $admissionPayment->id,
            'new_amount' => $amount,
            'new_fee_details' => $feeDetails
        ]);
    }

    /**
     * Toggle student active/inactive status.
     */
    public function toggleStatus(Student $student)
    {
        $student->update(['is_active' => !$student->is_active]);
        return redirect()->back()->with('success', 'Student status updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        // Delete NID file if exists
        if ($student->nid_file_path) {
            Storage::disk('public')->delete($student->nid_file_path);
        }
        // Also delete photo if exists
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:students,id'
        ]);

        $students = Student::whereIn('id', $request->selected_ids)->get();

        foreach ($students as $student) {
            // Delete NID file if exists
            if ($student->nid_file_path) {
                Storage::disk('public')->delete($student->nid_file_path);
            }
            // Also delete photo if exists
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $student->delete();
        }

        return redirect()->route('students.index')
            ->with('success', count($request->selected_ids) . ' students deleted successfully.');
    }


    /**
     * Generate unique student ID
     * Format: YY + ClassID(3 digits) + Sequential(3 digits)
     * Examples: 25001001 (Year 2025, Class 001, 1st student), 25002001 (Year 2025, Class 002, 1st student)
     */
    private function generateStudentIdInternal($classId = null)
    {
        $year = date('y'); // Last 2 digits of year (e.g., 25 for 2025)

        if ($classId) {
            $classroom = Classroom::find($classId);

            // Use the class_id field (3 digits)
            $classIdNumber = $classroom->class_id ?? '000';

            // Count existing students in this class to get the next sequential number
            $count = Student::where('class_id', $classId)->count() + 1;

            // Format: YY + ClassID(3 digits) + Count(3 digits)
            // Example: 25 + 001 + 001 = 25001001
            return $year . $classIdNumber . str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        // Default ID when no class selected (use class 000)
        $totalCount = Student::count() + 1;
        return $year . '000' . str_pad($totalCount, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Show admission confirmation page (for new student creation)
     */
    public function admissionConfirm(Student $student)
    {
        $student->load(['classroom', 'payments']);
        return view('students.admission-confirm', compact('student'));
    }

    /**
     * Show receipt confirmation page (for payments)
     */
    public function receiptConfirm(Student $student)
    {
        $student->load(['classroom', 'payments']);

        // If flash data exists, save it to persistent session so it's available when viewing receipt
        if (session()->has('latest_payment_id')) {
            $paymentId = session('latest_payment_id');
            session(['latest_payment_id' => $paymentId]);
        }

        if (session()->has('payment_details')) {
            session(['payment_details' => session('payment_details')]);
        }

        if (session()->has('selected_months')) {
            session(['selected_months' => session('selected_months')]);
        }

        if (session()->has('added_fees')) {
            session(['added_fees' => session('added_fees')]);
        }

        return view('students.receipt-confirm', compact('student'));
    }


    /**
     * Convert number to words
     */
    private function numberToWords($number)
    {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = [
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'forty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        ];

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->numberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->numberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->numberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction) && (int)$fraction > 0) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    /**
     * Download student admission receipt as PDF
     */
    public function downloadReceipt(Student $student)
    {
        $student->load(['classroom', 'payments']);

        // Get the Admission payment (or fallback to latest)
        $admissionPayment = $student->payments()
            ->where('payment_type', 'Admission')
            ->latest('id')
            ->first() ?? $student->payments()->latest('id')->first();

        $amountInWords = $this->numberToWords(intval($admissionPayment->amount ?? 0));

        // Generate Receipt No
        $receiptNo = ($admissionPayment->payment_date ? \Carbon\Carbon::parse($admissionPayment->payment_date) : now())->format('ymd') . str_pad($admissionPayment->id, 3, '0', STR_PAD_LEFT);

        // Use the consolidated payments/receipt view
        // Map admission payment to payment variable for consistency
        $payment = $admissionPayment;

        $pdf = Pdf::loadView('payments.receipt', compact('student', 'payment', 'amountInWords', 'receiptNo', 'isPdf'))
            ->setPaper('a4', 'landscape');

        $filename = 'student_receipt_' . $student->student_id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * View student admission receipt in browser (for printing)
     */
    public function viewReceipt(Student $student)
    {
        $student->load(['classroom', 'payments']);

        // Get the Admission payment (or fallback to latest)
        $admissionPayment = $student->payments()
            ->where('payment_type', 'Admission')
            ->latest('id')
            ->first() ?? $student->payments()->latest('id')->first();

        $amountInWords = $this->numberToWords($admissionPayment->amount ?? 0);

        // Generate Receipt No
        $receiptNo = ($admissionPayment->payment_date ? \Carbon\Carbon::parse($admissionPayment->payment_date) : now())->format('ymd') . str_pad($admissionPayment->id, 3, '0', STR_PAD_LEFT);

        // Use the consolidated payments/receipt view
        // Map admission payment to payment variable for consistency
        $payment = $admissionPayment;

        // Return HTML view for printing instead of PDF
        return view('payments.receipt', compact('student', 'payment', 'amountInWords', 'receiptNo'));
    }

    /**
     * Download student admission form as PDF
     */
    public function downloadAdmissionForm(Student $student)
    {
        $student->load(['classroom']);

        $pdf = Pdf::loadView('students.admission-form', compact('student'))
            ->setPaper('a4', 'portrait');
        return $pdf->download($student->student_id . '_admission_form.pdf');
    }
}
