<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\StudentMonth;
use App\Models\Student;
use App\Models\Classroom;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource (Accounts/Earnings page)
     */
    public function index(Request $request)
    {
        $query = Payment::with(['student.classroom']);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('section', $request->section);
            });
        }

        // Filter by month
        if ($request->filled('month')) {
            $monthName = $request->month;
            try {
                $monthIndex = Carbon::parse($monthName)->month;
            } catch (\Exception $e) {
                $monthIndex = null;
            }

            $query->where(function ($q) use ($monthName, $monthIndex) {
                // 1. The payment is explicitly for this month (Billing Month)
                $q->where('month', $monthName);

                if ($monthIndex) {
                    // 2. OR The payment is an Admission fee (or similar non-standard month) PAID in this month
                    $q->orWhere(function ($sq) use ($monthIndex) {
                        $sq->where('month', 'Admission')
                            ->whereMonth('payment_date', $monthIndex);
                    });

                    // 3. OR The payment has payment_items for this month
                    $q->orWhereHas('payment_items', function ($pi) use ($monthName, $monthIndex) {
                        $pi->where('month', $monthName);
                    });
                }
            });
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('payment_date', $request->year);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        // Filter by student name or ID
        if ($request->filled('student_search')) {
            $search = $request->student_search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Filter by fee name (payment type or content in fee_details)
        if ($request->filled('fee_name')) {
            $feeNames = is_array($request->fee_name) ? $request->fee_name : [$request->fee_name];

            $query->where(function ($q) use ($feeNames) {
                foreach ($feeNames as $feeName) {
                    $q->orWhere('payment_type', 'like', "%{$feeName}%")
                        ->orWhere('fee_details', 'like', "%{$feeName}%");
                }
            });
        }

        $unpaidStudents = collect();
        $isUnpaidSearch = $request->status === 'unpaid';

        if ($isUnpaidSearch) {
            $query = Student::with('classroom')->where('is_active', true);

            // Filter by class
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            // Filter by section
            if ($request->filled('section')) {
                $query->where('section', $request->section);
            }

            // Filter by student name or ID
            if ($request->filled('student_search')) {
                $search = $request->student_search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                });
            }

            // Exclude students who have paid for the selected month (or current month if not specified)
            $month = $request->filled('month') ? $request->month : date('F');
            $year = $request->filled('year') ? $request->year : date('Y');

            // Only include students registered on or before the selected month
            $selectedMonthDate = \Carbon\Carbon::createFromDate($year, date('m', strtotime($month)), 1)->endOfMonth();
            $query->where('created_at', '<=', $selectedMonthDate);

            // Exclude students who have paid for the selected month
            $query->whereDoesntHave('payment_items', function ($q) use ($month, $year) {
                $q->where('fee_type', 'Monthly')
                    ->where('month', $month)
                    ->where('year', date('y', strtotime($year)));
            });

            // Exclude students whose first payment month is AFTER the filtered month
            // (e.g., student created in July but first payment is August, shouldn't show as unpaid for July)
            $filterMonthNumber = date('n', strtotime($month)); // 1-12
            $filterYear = date('y', strtotime($year)); // 2-digit year

            $query->where(function ($q) use ($filterMonthNumber, $filterYear) {
                // Either student has no monthly payments at all (should show as unpaid)
                $q->whereDoesntHave('payment_items', function ($subQ) {
                    $subQ->where('fee_type', 'Monthly');
                })
                // OR student's earliest payment month is on or before the filter month
                ->orWhereHas('payment_items', function ($subQ) use ($filterMonthNumber, $filterYear) {
                    $subQ->where('fee_type', 'Monthly')
                        ->where(function ($dateQ) use ($filterMonthNumber, $filterYear) {
                            // Convert month name to number for comparison using CASE
                            $dateQ->whereRaw("
                                (CAST(year AS UNSIGNED) < ? OR
                                (CAST(year AS UNSIGNED) = ? AND
                                CASE month
                                    WHEN 'January' THEN 1
                                    WHEN 'February' THEN 2
                                    WHEN 'March' THEN 3
                                    WHEN 'April' THEN 4
                                    WHEN 'May' THEN 5
                                    WHEN 'June' THEN 6
                                    WHEN 'July' THEN 7
                                    WHEN 'August' THEN 8
                                    WHEN 'September' THEN 9
                                    WHEN 'October' THEN 10
                                    WHEN 'November' THEN 11
                                    WHEN 'December' THEN 12
                                END <= ?))
                            ", [$filterYear, $filterYear, $filterMonthNumber]);
                        });
                });
            });

            $unpaidStudents = $query->latest()->get();
            $payments = collect(); // Empty payments collection
        } else {
            $payments = $query->latest('id')->get();
        }


        if ($request->filled('fee_name')) {
            $filterNames = is_array($request->fee_name) ? $request->fee_name : [$request->fee_name];
            $totalEarnings = 0;

            foreach ($payments as $payment) {
                $matchedAmount = 0;
                $details = $payment->fee_details;
                $matchedItems = []; // Track which items matched to avoid double counting

                if (is_array($details)) {
                    foreach ($details as $index => $itm) {
                        foreach ($filterNames as $filterName) {
                            // Case-insensitive check
                            if (stripos($itm['name'] ?? '', $filterName) !== false) {
                                if (!in_array($index, $matchedItems)) {
                                    $original = isset($itm['original_amount']) ? floatval($itm['original_amount']) : null;
                                    $discount = isset($itm['discount']) ? floatval($itm['discount']) : 0;
                                    $effectiveAmount = ($original !== null)
                                        ? max(0, $original - $discount)
                                        : floatval($itm['amount'] ?? 0);
                                    $matchedAmount += $effectiveAmount;
                                    $matchedItems[] = $index;
                                }
                            }
                        }
                    }
                }

                // Fallback to full amount if payment_type matches but no details matched
                // Only if NO items matched inside details (or details was empty/invalid)
                if (empty($matchedItems)) {
                    foreach ($filterNames as $filterName) {
                        if (stripos($payment->payment_type, $filterName) !== false) {
                            $matchedAmount = $payment->final_amount;
                            break;
                        }
                    }
                }

                $payment->amount_display = $matchedAmount;
                $totalEarnings += $matchedAmount;
            }
        } else {
            $totalEarnings = 0;
            foreach ($payments as $payment) {
                // Use the actual final_amount field since it now stores the net amount after discounts
                // The sub_total and total_discount columns provide the breakdown if needed
                $payment->amount_display = $payment->final_amount;
                $totalEarnings += $payment->amount_display;
            }
        }

        // Get filter options
        $classrooms = Classroom::all();

        // Pass classroom data (sections) as JSON for JavaScript
        $classroomData = $classrooms->mapWithKeys(function ($classroom) {
            return [$classroom->id => [
                'sections' => $classroom->sections ?? []
            ]];
        });

        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];
        $years = Payment::selectRaw('YEAR(payment_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $allStudents = Student::select('id', 'name', 'student_id')->orderBy('name')->get();

        // Get all unique fee names from Classrooms + standard types
        $classroomFees = Classroom::all()->pluck('fees')->flatten(1)->pluck('name')->filter();
        $feeTypes = collect(['Admission', 'Monthly Fee'])->merge($classroomFees)->unique()->sort()->values();

        $allSections = Student::select('section')->whereNotNull('section')->distinct()->orderBy('section')->pluck('section');

        return view('payments.index', compact(
            'payments',
            'totalEarnings',
            'classrooms',
            'classroomData',
            'months',
            'years',
            'allStudents',
            'feeTypes',
            'allSections',
            'unpaidStudents'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::with('classroom')->orderBy('name')->get();
        return view('payments.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'final_amount' => 'required|numeric|min:0',
            'payment_date' => 'required',
            'month' => 'required|string',
            'payment_type' => 'required|string',
            'payment_mode' => 'required|string',
            'note' => 'nullable|string',
            'redirect_to' => 'nullable|string',
            'show_receipt' => 'nullable|boolean',
            'payment_details' => 'nullable|string',
            'selected_months' => 'nullable|string',
            'added_fees' => 'nullable|string',
            'sub_total' => 'nullable|numeric|min:0',
            'total_discount' => 'nullable|numeric|min:0',
        ]);

        // Ensure amount is set for backwards compatibility
        if (!isset($validated['amount'])) {
            $validated['amount'] = $validated['final_amount'];
        }

        // Handle d/m/Y date format
        if (isset($validated['payment_date']) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $validated['payment_date'])) {
            try {
                $validated['payment_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['payment_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback or handle error if needed, but validation passed 'required'
            }
        }

        // Build fee details array from the payment details
        $feeDetails = [];

        if ($request->has('payment_details')) {
            $paymentDetails = json_decode($request->payment_details, true);
            $selectedMonths = $request->has('selected_months') ? json_decode($request->selected_months, true) : [];
            $addedFees = $request->has('added_fees') ? json_decode($request->added_fees, true) : [];

            // Check if flat fee details are provided directly (more accurate for partial months)
            if (isset($paymentDetails['fee_details']) && is_array($paymentDetails['fee_details'])) {
                // Preserve all fields including original_amount and discount
                foreach ($paymentDetails['fee_details'] as $fee) {
                    $feeDetail = [
                        'name' => $fee['name'] ?? 'Fee',
                        'type' => $fee['type'] ?? 'Other',
                        'amount' => $fee['amount'] ?? 0
                    ];

                    // Preserve original_amount and discount if present
                    if (isset($fee['original_amount'])) {
                        $feeDetail['original_amount'] = $fee['original_amount'];
                    }
                    if (isset($fee['discount'])) {
                        $feeDetail['discount'] = $fee['discount'];
                    }
                    if (isset($fee['month'])) {
                        $feeDetail['month'] = $fee['month'];
                    }
                    if (isset($fee['year'])) {
                        $feeDetail['year'] = $fee['year'];
                    }

                    $feeDetails[] = $feeDetail;
                }
            } else {
                // Add monthly fees for each selected month (Fallback cross-product logic)
                if (isset($paymentDetails['monthlyFees']) && count($selectedMonths) > 0) {
                    // For each selected month, add each monthly fee component separately
                    foreach ($selectedMonths as $month) {
                        $monthName = $month['name'] ?? 'N/A';
                        $year = $month['year'] ?? date('y');
                        $displayMonth = $monthName . ', ' . $year;

                        // Add each monthly fee component (Tuition, Structural, etc.)
                        foreach ($paymentDetails['monthlyFees'] as $fee) {
                            $baseName = $fee['name'] ?? 'Monthly Fee';
                            $feeDetails[] = [
                                'name' => $baseName . ' - ' . $displayMonth,
                                'base_name' => $baseName,
                                'type' => 'Monthly',
                                'amount' => $fee['amount'],
                                'month' => $monthName,
                                'year' => $year
                            ];
                        }
                    }
                }

                // Add other fees
                if (count($addedFees) > 0) {
                    foreach ($addedFees as $fee) {
                        $feeDetails[] = [
                            'name' => $fee['name'],
                            'type' => $fee['type'] ?? 'Other',
                            'amount' => $fee['amount']
                        ];
                    }
                }
            }
        }

        $validated['fee_details'] = $feeDetails;

        // Validate that all fees in payment_details are subscribed by student
        $student = Student::find($validated['student_id']);
        if ($student) {
            // Use selected_fees array (JSON column), not the fees relationship
            $studentFees = $student->selected_fees ?? $student->fees ?? [];
            // Ensure it's an array, not a Collection
            $studentFees = is_array($studentFees) ? $studentFees : [];
            $subscribedFeeNames = array_column($studentFees, 'name');

            if (!empty($feeDetails)) {
                foreach ($feeDetails as $fee) {
                    $feeName = $fee['name'];

                    // Check if student is subscribed to this fee
                    // Payment modal sends fee names with month suffix like "Monthly Fee - June, 26"
                    // We need to extract the base fee name for subscription check
                    $baseFeeName = $feeName;
                    if (strpos($feeName, ' - ') !== false) {
                        $parts = explode(' - ', $feeName);
                        $baseFeeName = trim($parts[0]);
                    }

                    $isSubscribed = in_array($baseFeeName, $subscribedFeeNames);

                    if (!$isSubscribed) {
                        return redirect()->back()
                            ->with('error', "Student is not subscribed to '{$baseFeeName}' fee. Please select only subscribed fees.")
                            ->withInput();
                    }
                }
            }
        }

        // Calculate sub_total and discount from fee_details
        $subTotal = 0;
        $totalDiscount = 0;

        if (!empty($feeDetails)) {
            foreach ($feeDetails as $fee) {
                $orig = isset($fee['original_amount']) ? floatval($fee['original_amount']) : null;
                $disc = isset($fee['discount']) ? floatval($fee['discount']) : 0;

                if ($orig !== null) {
                    $subTotal += $orig;
                    $totalDiscount += $disc;
                }
            }
        }

        // If sub_total and total_discount are provided by frontend, use them
        // Otherwise calculate from fee_details
        if (isset($validated['sub_total']) && isset($validated['total_discount'])) {
            $subTotal = floatval($validated['sub_total']);
            $totalDiscount = floatval($validated['total_discount']);
        }

        // Ensure final_amount matches sub_total - total_discount
        $validated['sub_total'] = $subTotal;
        $validated['total_discount'] = $totalDiscount;
        $validated['final_amount'] = max(0, $subTotal - $totalDiscount);

        $payment = Payment::create($validated);

        // Create payment items from fee details
        if (!empty($feeDetails)) {
            foreach ($feeDetails as $fee) {
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'student_id' => $validated['student_id'],
                    'fee_name' => $fee['name'],
                    'base_fee_name' => $fee['base_name'] ?? $fee['name'],
                    'fee_type' => $fee['type'] ?? 'Other',
                    'month' => $fee['month'] ?? null,
                    'year' => $fee['year'] ?? null,
                    'amount' => $fee['amount'],
                    'original_amount' => $fee['original_amount'] ?? $fee['amount'],
                    'discount' => $fee['discount'] ?? 0
                ]);
            }
        }

        // Mark months as paid in student_months table
        // Check if payment covers all required monthly fees based on payment_items
        if (!empty($payment->month)) {
            // Get student's assigned monthly fees
            $student = Student::find($payment->student_id);
            if ($student) {
                $selectedFees = $student->selected_fees ?? [];
                $monthlyFees = collect($selectedFees)->filter(function ($fee) {
                    return isset($fee['type']) && strtolower($fee['type']) === 'monthly';
                });
                $requiredMonthlyFeeNames = $monthlyFees->pluck('name')->unique()->toArray();

                // Get payment items for this payment
                $paymentItems = $payment->payment_items;
                $paidMonthlyFeeNames = [];

                foreach ($paymentItems as $item) {
                    // Check if this payment item is for a monthly fee
                    if (strtolower($item->fee_type ?? '') === 'monthly') {
                        // Use base_fee_name field directly
                        if (!empty($item->base_fee_name)) {
                            $paidMonthlyFeeNames[] = $item->base_fee_name;
                        }
                    }
                }

                // Determine payment status based on fee coverage
                $paidFeeCount = count(array_intersect($requiredMonthlyFeeNames, $paidMonthlyFeeNames));
                $totalRequiredFees = count($requiredMonthlyFeeNames);

                // Only create record if at least some monthly fees are covered
                if ($paidFeeCount > 0) {
                    $paymentStatus = ($paidFeeCount >= $totalRequiredFees) ? 'paid' : 'partial';

                    // Parse month field - payment modal sends properly formatted month keys like "June, 26, July, 26"
                    $monthEntries = preg_split('/,\s*/', $payment->month);
                    $processedMonths = [];

                    foreach ($monthEntries as $monthEntry) {
                        $monthKey = trim($monthEntry);

                        // Skip empty entries
                        if (empty($monthKey)) continue;

                        // Avoid duplicates
                        if (!in_array($monthKey, $processedMonths)) {
                            $processedMonths[] = $monthKey;

                            // Check if existing record exists
                            $existingRecord = StudentMonth::where('student_id', $payment->student_id)
                                ->where('month_key', $monthKey)
                                ->first();

                            if ($existingRecord) {
                                // Update existing record - upgrade to paid if all fees now covered
                                if ($paymentStatus === 'paid') {
                                    $existingRecord->update([
                                        'status' => 'paid',
                                        'payment_id' => $payment->id
                                    ]);
                                } else {
                                    // Keep existing status, just update payment_id
                                    $existingRecord->update(['payment_id' => $payment->id]);
                                }
                            } else {
                                // Create new record with calculated status
                                StudentMonth::create([
                                    'student_id' => $payment->student_id,
                                    'month_key' => $monthKey,
                                    'status' => $paymentStatus,
                                    'payment_id' => $payment->id
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Handle partial payment completion
        $student = Student::find($validated['student_id']);
        if ($student && $student->partial_payments) {
            $partialPayments = $student->partial_payments;
            $updated = false;

            // Check if any fee in this payment is completing a partial payment
            foreach ($feeDetails as $fee) {
                $feeName = $fee['name'];

                // Check if this is a partial payment completion (name ends with " (Remaining)")
                if (str_ends_with($feeName, ' (Remaining)')) {
                    $originalName = str_replace(' (Remaining)', '', $feeName);

                    if (isset($partialPayments[$originalName])) {
                        $partial = $partialPayments[$originalName];
                        $paidAmount = $fee['amount'];

                        // Update partial payment record
                        $partial['paid'] += $paidAmount;
                        $partial['remaining'] = max(0, $partial['total'] - $partial['paid']);
                        $partial['payment_ids'][] = $payment->id;

                        // If fully paid, remove from partial_payments
                        if ($partial['remaining'] <= 0) {
                            unset($partialPayments[$originalName]);
                        } else {
                            $partialPayments[$originalName] = $partial;
                        }

                        $updated = true;

                        \Log::info('Partial Payment Updated:', [
                            'fee' => $originalName,
                            'paid_now' => $paidAmount,
                            'total_paid' => $partial['paid'],
                            'remaining' => $partial['remaining']
                        ]);
                    }
                }
            }

            if ($updated) {
                $student->update(['partial_payments' => $partialPayments]);
            }
        }

        // If show_receipt is true, the form submission has already opened the receipt
        // in a new tab (target="_blank"). We just need to redirect the main window back.
        // However, standard form submission redirects the "target" window.
        // To handle "New Tab + Parent Refresh", we actually need the Controller to return the PDF/View
        // in the response (which goes to the new tab).

        if ($request->has('show_receipt') && $request->show_receipt) {
            // Redirect to the view receipt route, which will load content in the new blank tab
            return redirect()->route('payments.receipt', $payment->id);
        }

        // Check if redirect_to is specified
        $redirectRoute = $validated['redirect_to'] ?? 'payments.index';

        return redirect()->route($redirectRoute)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified resource (Student payment history)
     */
    public function show($studentId)
    {
        $student = Student::with(['classroom', 'payments' => function ($query) {
            $query->latest('payment_date');
        }])->findOrFail($studentId);

        return view('payments.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        $students = Student::with('classroom')->orderBy('name')->get();
        return view('payments.edit', compact('payment', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'month' => 'required|string',
            'payment_type' => 'required|string',
            'payment_mode' => 'required|string',
            'note' => 'nullable|string',
            'redirect_to' => 'nullable|string',
        ]);

        // SECURITY: Prevent modification of payment amounts for accounting integrity
        // Once a payment is recorded, its amounts cannot be changed
        // Only allow editing of: payment_date, month, payment_type, payment_mode, note

        // Store old month value before updating
        $oldMonth = $payment->month;

        $validated['payment_type'] = $request->payment_type;
        $validated['payment_mode'] = $request->payment_mode;
        $validated['note'] = $request->note;
        $validated['month'] = $request->month;
        $validated['payment_date'] = $request->payment_date;

        $payment->update($validated);

        // Update student_months table when month changes
        if ($oldMonth !== $request->month) {
            // Get student's assigned monthly fees for proper status calculation
            $student = Student::find($payment->student_id);
            if ($student) {
                $selectedFees = $student->selected_fees ?? [];
                $monthlyFees = collect($selectedFees)->filter(function ($fee) {
                    return isset($fee['type']) && strtolower($fee['type']) === 'monthly';
                });
                $requiredMonthlyFeeNames = $monthlyFees->pluck('name')->unique()->toArray();

                // Get payment items for this payment
                $paymentItems = $payment->payment_items;
                $paidMonthlyFeeNames = [];

                foreach ($paymentItems as $item) {
                    if (strtolower($item->fee_type ?? '') === 'monthly') {
                        // Use base_fee_name field directly
                        if (!empty($item->base_fee_name)) {
                            $paidMonthlyFeeNames[] = $item->base_fee_name;
                        }
                    }
                }

                // Determine payment status based on fee coverage
                $paidFeeCount = count(array_intersect($requiredMonthlyFeeNames, $paidMonthlyFeeNames));
                $totalRequiredFees = count($requiredMonthlyFeeNames);
                $paymentStatus = ($paidFeeCount >= $totalRequiredFees) ? 'paid' : 'partial';

                // Remove old month records
                $oldMonthEntries = preg_split('/,\s*/', $oldMonth);
                foreach ($oldMonthEntries as $monthEntry) {
                    $monthKey = trim($monthEntry);
                    if (!empty($monthKey)) {
                        StudentMonth::where('student_id', $payment->student_id)
                            ->where('month_key', $monthKey)
                            ->where('payment_id', $payment->id)
                            ->delete();
                    }
                }

                // Add new month records if payment has monthly fees
                if (!empty($request->month) && $paidFeeCount > 0) {
                    // Create new month records
                    $newMonthEntries = preg_split('/,\s*/', $request->month);
                    foreach ($newMonthEntries as $monthEntry) {
                        $monthKey = trim($monthEntry);
                        if (!empty($monthKey)) {
                            StudentMonth::updateOrCreate(
                                [
                                    'student_id' => $payment->student_id,
                                    'month_key' => $monthKey
                                ],
                                [
                                    'status' => $paymentStatus,
                                    'payment_id' => $payment->id
                                ]
                            );
                        }
                    }
                }
            }
        }

        $redirectRoute = $request->input('redirect_to', 'payments.index');

        return redirect()->route($redirectRoute, $payment->student_id)
            ->with('success', 'Payment updated successfully. Note: Amounts cannot be modified for accounting integrity.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        // Clean up student_months records when payment is deleted
        StudentMonth::where('payment_id', $payment->id)->delete();

        $payment->delete();

        return redirect()->back()
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Show payment history for a specific student (AJAX or Modal)
     */
    public function studentHistory($studentId)
    {
        $student = Student::with(['classroom', 'payments' => function ($query) {
            $query->latest('payment_date');
        }])->findOrFail($studentId);

        if (request()->ajax()) {
            return response()->json([
                'student' => $student,
                'payments' => $student->payments
            ]);
        }

        return view('payments.student-history', compact('student'));
    }

    /**
     * View payment receipt
     */
    public function viewReceipt(Payment $payment)
    {
        $payment->load(['student.classroom']);
        $student = $payment->student;

        // Convert amount to words
        $amountInWords = $this->numberToWords(intval($payment->final_amount));

        // Generate Receipt No
        $receiptNo = ($payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date) : now())->format('ymd') . str_pad($payment->id, 3, '0', STR_PAD_LEFT);

        return view('payments.receipt', compact('payment', 'student', 'amountInWords', 'receiptNo'));
    }

    /**
     * Download payment receipt as PDF
     */
    public function downloadReceipt(Payment $payment)
    {
        $payment->load(['student.classroom']);
        $student = $payment->student;

        // Convert amount to words
        $amountInWords = $this->numberToWords(intval($payment->final_amount));

        // Generate Receipt No
        $receiptNo = ($payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date) : now())->format('ymd') . str_pad($payment->id, 3, '0', STR_PAD_LEFT);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.receipt', [
            'payment' => $payment,
            'student' => $student,
            'amountInWords' => $amountInWords,
            'receiptNo' => $receiptNo,
            'isPdf' => true
        ])
            ->setPaper('a4', 'landscape');

        $filename = 'payment_receipt_' . $student->student_id . '_' . $payment->id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Public payment receipt (shareable via WhatsApp)
     */
    public function publicReceipt($token)
    {
        $payment = Payment::findByShareToken($token);

        if (!$payment) {
            abort(404, 'Payment receipt not found or invalid token.');
        }

        $payment->load(['student.classroom']);
        $student = $payment->student;

        // Convert amount to words
        $amountInWords = $this->numberToWords(intval($payment->final_amount));

        // Generate Receipt No
        $receiptNo = ($payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date) : now())->format('ymd') . str_pad($payment->id, 3, '0', STR_PAD_LEFT);

        // Use the consolidated payments/receipt view
        return view('payments.receipt', compact('payment', 'student', 'amountInWords', 'receiptNo'));
    }

    /**
     * Convert number to words (helper function)
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

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}
