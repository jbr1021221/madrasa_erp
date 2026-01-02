<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
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
        $query = Student::with(['classroom', 'payments' => function($q) {
            $q->whereIn('payment_type', ['Monthly', 'Admission'])
              ->select('student_id', 'month', 'fee_details', 'payment_type');
        }]);

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
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->get();
    $classrooms = Classroom::all();
    
    if ($request->filled('class_id')) {
        $selectedClass = $classrooms->find($request->class_id);
        $sections = $selectedClass ? $selectedClass->sections : [];
    } else {
        // Get all unique sections from all classrooms
        $sections = $classrooms->pluck('sections')->flatten()->unique()->sort()->values()->all();
    }

        return view('students.index', compact('students', 'classrooms', 'sections'));
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
        $classroomData = $classrooms->mapWithKeys(function($classroom) {
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

        // Create the student
        $student = Student::create($validated);

        // Create admission payment record
    // Build fee details array from ONLY the fees that were selected (checked) in the form
    $feeDetails = [];
    
    // The JavaScript collectAdmissionFees() function stores selected fees in 'selected_admission_fees' field
    if ($request->has('selected_admission_fees') && !empty($request->selected_admission_fees)) {
        $selectedFees = json_decode($request->selected_admission_fees, true);
        
        if (is_array($selectedFees)) {
            foreach ($selectedFees as $fee) {
                $feeName = $fee['name'] ?? '';
                $feeAmount = floatval($fee['amount'] ?? 0);
                $feeType = $fee['type'] ?? 'Admission';
                $feeMonth = $fee['month'] ?? null;
                
                // The amount from JavaScript is already after discount, so use it directly
                if ($feeAmount > 0 && !empty($feeName)) {
                    $feeDetail = [
                        'name' => $feeName,
                        'type' => $feeType,
                        'amount' => $feeAmount
                    ];
                    
                    // Add month information if it's a monthly fee
                    if ($feeMonth) {
                        $feeDetail['month'] = $feeMonth;
                    }
                    
                    $feeDetails[] = $feeDetail;
                }
            }
        }
    }
    
    // Add first month fee to fee_details if selected (legacy support)
    if ($request->pay_first_month === 'yes' && $request->first_month_fee > 0) {
        $classroom = \App\Models\Classroom::find($validated['class_id']);
        if ($classroom && $classroom->fees) {
            // Add each monthly fee component for the first month
            foreach ($classroom->fees as $fee) {
                if (isset($fee['type']) && $fee['type'] === 'Monthly') {
                    $feeDetails[] = [
                        'name' => ($fee['name'] ?? 'Monthly Fee') . ' - ' . $request->first_month . ' ' . date('Y'),
                        'type' => 'Monthly',
                        'amount' => $fee['amount'] ?? 0,
                        'month' => $request->first_month . ' ' . date('Y')
                    ];
                }
            }
        }
    }
        
        $admissionPayment = $student->payments()->create([
            'amount' => $validated['total_admission_fee'],
            'payment_type' => 'Admission',
            'payment_mode' => $validated['payment_mode'],
            'month' => 'Admission',
            'note' => $validated['payment_note'] ?? null,
            'payment_date' => now(),
            'fee_details' => $feeDetails,
        ]);

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
        $classroomData = $classrooms->mapWithKeys(function($classroom) {
            return [$classroom->id => [
                'fees' => $classroom->fees,
                'total_fee' => $classroom->total_fee,
                'sections' => $classroom->sections ?? []
            ]];
        });

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

        // Process discounts or preserve old ones
        if ($request->has('student_assigned_fees') && !empty($request->student_assigned_fees)) {
             $discounts = [];
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

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
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

    /**
     * Download student admission receipt as PDF
     */
    public function downloadReceipt(Student $student)
    {
        $student->load(['classroom', 'payments']);
        
        // Get the most recent payment (admission or latest payment)
        $admissionPayment = $student->payments()
            ->latest('payment_date')
            ->first();
            
        $amountInWords = $this->numberToWords(intval($admissionPayment->amount ?? 0));
        
        // Generate Receipt No
        $receiptNo = ($admissionPayment->payment_date ? \Carbon\Carbon::parse($admissionPayment->payment_date) : now())->format('ymd') . str_pad($admissionPayment->id, 3, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('students.receipt', compact('student', 'admissionPayment', 'amountInWords', 'receiptNo'))
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
        
        // Get the most recent payment (admission or latest payment)
        $payment = $student->payments()
            ->latest('payment_date')
            ->first();
            
        $amountInWords = $this->numberToWords($payment->amount ?? 0);
        
        // Generate Receipt No
        $receiptNo = ($payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date) : now())->format('ymd') . str_pad($payment->id, 3, '0', STR_PAD_LEFT);

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