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
            $query->where('month', $request->month);
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

        $payments = $query->latest('payment_date')->get();
        $totalEarnings = $payments->sum('amount');

        // Get filter options
        $classrooms = Classroom::all();
        $sections = Student::distinct()->pluck('section')->filter();
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $years = Payment::selectRaw('YEAR(payment_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('payments.index', compact(
            'payments',
            'totalEarnings',
            'classrooms',
            'sections',
            'months',
            'years'
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
        ]);

        $payment = Payment::create($validated);

        return redirect()->route('payments.index')
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
        ]);

        $payment->update($validated);

        return redirect()->route('payments.index')
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
}