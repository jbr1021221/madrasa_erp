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
        $query = Student::with('classroom');

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->where('section', $request->section);
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
        $sections = ['A', 'B'];

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

        // Handle file upload
        if ($request->hasFile('nid_file')) {
            $validated['nid_file_path'] = $request->file('nid_file')->store('nid_files', 'public');
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('student_photos', 'public');
        }

        // Create the student
        $student = Student::create($validated);

        // Create admission payment record
        $student->payments()->create([
            'amount' => $validated['total_admission_fee'],
            'payment_type' => 'Admission',
            'payment_mode' => $validated['payment_mode'],
            'month' => 'Admission',
            'note' => $validated['payment_note'] ?? null,
            'payment_date' => now(),
        ]);

        // Redirect to receipt confirmation page with success message
        return redirect()->route('students.receipt.confirm', $student)
            ->with('success', 'Student created successfully! You can download the admission receipt below.');
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

        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

 
/**
 * Generate unique student ID
 * Format: YY + ClassNumber(2 digits) + Sequential(3 digits)
 * Examples: 2501001 (Class 1, 1st student), 2502001 (Class 2, 1st student), 2510001 (Class 10, 1st student)
 */
private function generateStudentIdInternal($classId = null)
{
    $year = date('y'); // Last 2 digits of year (e.g., 25 for 2025)
    
    if ($classId) {
        $classroom = Classroom::find($classId);
        // Extract number from class name (e.g., "Class 1" -> "1", "Class 10" -> "10")
        $classNumber = preg_replace('/[^0-9]/', '', $classroom->name ?? '0');
        
        // Pad class number to 2 digits (e.g., 1 -> 01, 10 -> 10)
        $classNumberPadded = str_pad($classNumber, 2, '0', STR_PAD_LEFT);
        
        // Count existing students in this class to get the next sequential number
        $count = Student::where('class_id', $classId)->count() + 1;
        
        // Format: YY + ClassNum(2 digits) + Count(3 digits)
        // Example: 25 + 01 + 001 = 2501001
        return $year . $classNumberPadded . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
    
    // Default ID when no class selected (use class 00)
    $totalCount = Student::count() + 1;
    return $year . '00' . str_pad($totalCount, 3, '0', STR_PAD_LEFT);
}

    /**
     * Show receipt confirmation page
     */
    public function receiptConfirm(Student $student)
    {
        $student->load(['classroom', 'payments']);
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
        
        // Get admission payment
        $admissionPayment = $student->payments()
            ->where('payment_type', 'Admission')
            ->first();
            
        $amountInWords = $this->numberToWords(intval($admissionPayment->amount ?? 0));
        
        $pdf = Pdf::loadView('students.receipt', compact('student', 'admissionPayment', 'amountInWords'))
            ->setPaper('a5', 'portrait');
        
        $filename = 'student_receipt_' . $student->student_id . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * View student admission receipt in browser
     */
    public function viewReceipt(Student $student)
    {
        $student->load(['classroom', 'payments']);
        
        // Get admission payment
        $admissionPayment = $student->payments()
            ->where('payment_type', 'Admission')
            ->first();
            
        $amountInWords = $this->numberToWords($admissionPayment->amount ?? 0);
        
        $pdf = Pdf::loadView('students.receipt', compact('student', 'admissionPayment', 'amountInWords'))
            ->setPaper('a5', 'portrait');
        
        return $pdf->stream('student_receipt_' . $student->student_id . '.pdf');
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