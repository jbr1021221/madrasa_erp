<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
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
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->whereHas('student', function($q) use ($request) {
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

            $query->where(function($q) use ($monthName, $monthIndex) {
                // 1. The payment is explicitly for this month (Billing Month)
                $q->where('month', $monthName);

                if ($monthIndex) {
                    // 2. OR The payment is an Admission fee (or similar non-standard month) PAID in this month
                    $q->orWhere(function($sq) use ($monthIndex) {
                        $sq->where('month', 'Admission')
                           ->whereMonth('payment_date', $monthIndex);
                    });
                    
                    // 3. OR It's inside fee_details (bundled fees) - using LIKE for JSON search
                    $q->orWhere('fee_details', 'LIKE', '%"month":"' . $monthName . '%')
                      ->orWhere('fee_details', 'LIKE', '%"month": "' . $monthName . '%')
                      ->orWhere('fee_details', 'LIKE', '%"month":"' . $monthName . ',%')
                      ->orWhere('fee_details', 'LIKE', '%"month": "' . $monthName . ',%');
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
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Filter by fee name (payment type or content in fee_details)
        if ($request->filled('fee_name')) {
            $feeNames = is_array($request->fee_name) ? $request->fee_name : [$request->fee_name];
            
            $query->where(function($q) use ($feeNames) {
                foreach ($feeNames as $feeName) {
                    $q->orWhere('payment_type', 'like', "%{$feeName}%")
                      ->orWhere('fee_details', 'like', "%{$feeName}%");
                }
            });
        }

        $payments = $query->latest('id')->get();

        
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
                                    $matchedAmount += ($itm['amount'] ?? 0);
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
                            $matchedAmount = $payment->amount;
                            break; 
                        }
                    }
                }

                $payment->amount_display = $matchedAmount;
                $totalEarnings += $matchedAmount;
            }
        } else {
            $totalEarnings = $payments->sum('amount');
            foreach ($payments as $payment) {
                $payment->amount_display = $payment->amount;
            }
        }

        // Get filter options
        $classrooms = Classroom::all();
        
        // Pass classroom data (sections) as JSON for JavaScript
        $classroomData = $classrooms->mapWithKeys(function($classroom) {
            return [$classroom->id => [
                'sections' => $classroom->sections ?? []
            ]];
        });

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
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
            'allSections'
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
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'month' => 'required|string',
            'payment_type' => 'required|string',
            'payment_mode' => 'required|string',
            'note' => 'nullable|string',
            'redirect_to' => 'nullable|string',
            'show_receipt' => 'nullable|boolean',
            'payment_details' => 'nullable|string',
            'selected_months' => 'nullable|string',
            'added_fees' => 'nullable|string',
        ]);

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
                            $feeDetails[] = [
                                'name' => ($fee['name'] ?? 'Monthly Fee') . ' - ' . $displayMonth,
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

        $payment = Payment::create($validated);
        
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
        $student = Student::with(['classroom', 'payments' => function($query) {
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
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'month' => 'required|string',
            'payment_type' => 'required|string',
            'payment_mode' => 'required|string',
            'note' => 'nullable|string',
            'payment_details' => 'nullable|string',
            'selected_months' => 'nullable|string',
            'added_fees' => 'nullable|string',
            'redirect_to' => 'nullable|string',
        ]);

        // Build fee details array from the payment details if provided
        if ($request->has('payment_details')) {
            $feeDetails = [];
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
                    if (isset($fee['original_amount'])) $feeDetail['original_amount'] = $fee['original_amount'];
                    if (isset($fee['discount'])) $feeDetail['discount'] = $fee['discount'];
                    if (isset($fee['month'])) $feeDetail['month'] = $fee['month'];
                    if (isset($fee['year'])) $feeDetail['year'] = $fee['year'];
                    
                    $feeDetails[] = $feeDetail;
                }
            } else {
                // Add monthly fees for each selected month (Fallback cross-product logic)
                if (isset($paymentDetails['monthlyFees']) && count($selectedMonths) > 0) {
                    foreach ($selectedMonths as $month) {
                        $monthName = $month['name'] ?? 'N/A';
                        $year = $month['year'] ?? date('y');
                        $displayMonth = $monthName . ', ' . $year;
                        
                        foreach ($paymentDetails['monthlyFees'] as $fee) {
                            $feeDetails[] = [
                                'name' => ($fee['name'] ?? 'Monthly Fee') . ' - ' . $displayMonth,
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
            $validated['fee_details'] = $feeDetails;
        }

        $payment->update($validated);

        if ($request->has('show_receipt') && $request->show_receipt) {
             return redirect()->route('payments.receipt', $payment->id);
        }

        // Handle Redirect
        $redirectRoute = $request->input('redirect_to', 'payments.index');
        
        return redirect()->route($redirectRoute, $payment->student_id)
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Show payment history for a specific student (AJAX or Modal)
     */
    public function studentHistory($studentId)
    {
        $student = Student::with(['classroom', 'payments' => function($query) {
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
        $amountInWords = $this->numberToWords(intval($payment->amount));
        
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
        $amountInWords = $this->numberToWords(intval($payment->amount));
        
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
     * Convert number to words (helper function)
     */
    private function numberToWords($number) {
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