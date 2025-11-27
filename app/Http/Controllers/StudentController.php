<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Support\Facades\Storage;

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students,student_id',
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'address' => 'required|string',
            'mobile' => 'required|string|max:20',
            'alt_mobile' => 'nullable|string|max:20',
            'class_id' => 'required|exists:classrooms,id',
            'section' => 'required|string|max:10',
            'nid_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'payment_mode' => 'required|string',
            'payment_note' => 'nullable|string',
            'total_admission_fee' => 'required|numeric|min:0',
            // New student details
            'dob' => 'required|date',
            'gender' => 'required|string|in:Male,Female,Other',
            'blood_group' => 'nullable|string|max:5',
            'last_school' => 'nullable|string|max:255',
            'siblings_count' => 'nullable|integer|min:1',
            'birth_order' => 'nullable|integer|min:1',
            // Address details
            'present_district' => 'required|string|max:255',
            'permanent_address' => 'nullable|string',
            'permanent_district' => 'nullable|string|max:255',
            // Guardian details
            'guardian_occupation' => 'required|string|max:255',
            'guardian_nationality' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:20',
            'guardian_email' => 'nullable|email|max:255',
            'guardian_nid' => 'required|string|max:30',
        ]);

        // Handle file upload
        if ($request->hasFile('nid_file')) {
            $validated['nid_file_path'] = $request->file('nid_file')->store('nid_files', 'public');
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

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
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
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'address' => 'required|string',
            'mobile' => 'required|string|max:20',
            'alt_mobile' => 'nullable|string|max:20',
            'class_id' => 'required|exists:classrooms,id',
            'section' => 'required|string|max:10',
            'nid_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            // New student details
            'dob' => 'required|date',
            'gender' => 'required|string|in:Male,Female,Other',
            'blood_group' => 'nullable|string|max:5',
            'last_school' => 'nullable|string|max:255',
            'siblings_count' => 'nullable|integer|min:1',
            'birth_order' => 'nullable|integer|min:1',
            // Address details
            'present_district' => 'required|string|max:255',
            'permanent_address' => 'nullable|string',
            'permanent_district' => 'nullable|string|max:255',
            // Guardian details
            'guardian_occupation' => 'required|string|max:255',
            'guardian_nationality' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:20',
            'guardian_email' => 'nullable|email|max:255',
            'guardian_nid' => 'required|string|max:30',
        ]);

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
     * Format: YY + ClassNumber + Sequential (e.g., 251001, 251002)
     */
    private function generateStudentIdInternal($classId = null)
    {
        $year = date('y'); // Last 2 digits of year (e.g., 25 for 2025)
        
        if ($classId) {
            $classroom = Classroom::find($classId);
            // Extract number from class name (e.g., "Class 1" -> "1")
            $classNumber = preg_replace('/[^0-9]/', '', $classroom->name ?? '0');
            
            // Count existing students in this class
            $count = Student::where('class_id', $classId)->count() + 1;
            
            // Format: YY + ClassNum + Count (padded to 3 digits)
            return $year . $classNumber . str_pad($count, 3, '0', STR_PAD_LEFT);
        }
        
        // Default ID when no class selected
        $totalCount = Student::count() + 1;
        return $year . '0' . str_pad($totalCount, 3, '0', STR_PAD_LEFT);
    }
}