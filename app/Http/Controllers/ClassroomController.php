<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    /**
     * Display a listing of classrooms
     */
    public function index()
    {
        $classrooms = Classroom::orderBy('name')->get();
        return view('classrooms.index', compact('classrooms'));
    }

    /**
     * Show the form for creating a new classroom
     */
    public function create()
    {
        return view('classrooms.create');
    }

    /**
     * Store a newly created classroom
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sections' => 'required|string',
            'max_students_per_section' => 'required|integer|min:1',
            'admission_fee' => 'required|numeric|min:0',
            'fees' => 'nullable|array',
            'fees.*.name' => 'required|string',
            'fees.*.amount' => 'required|numeric|min:0',
            'fees.*.type' => 'required|in:One Time,Monthly,Yearly'
        ]);

        $classroom = new Classroom();
        $classroom->fill($validated);
        
        // Ensure fees is an array if not present
        if (!isset($validated['fees'])) {
            $classroom->fees = [];
        }
        
        // Convert sections string to array
        $classroom->sections = array_map('trim', explode(',', $validated['sections']));
        
        // Calculate total fee using model method
        $classroom->total_fee = $classroom->calculateTotalFee();
        
        $classroom->save();

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Class created successfully!');
    }

    /**
     * Show the form for editing the specified classroom
     */
    public function edit(Classroom $classroom)
    {
        return view('classrooms.edit', compact('classroom'));
    }

    /**
     * Update the specified classroom
     */
    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sections' => 'required|string',
            'max_students_per_section' => 'required|integer|min:1',
            'admission_fee' => 'required|numeric|min:0',
            'fees' => 'nullable|array',
            'fees.*.name' => 'required|string',
            'fees.*.amount' => 'required|numeric|min:0',
            'fees.*.type' => 'required|in:One Time,Monthly,Yearly'
        ]);

        $classroom->fill($validated);

        // Ensure fees is an array if not present
        if (!isset($validated['fees'])) {
            $classroom->fees = [];
        }

        // Convert sections string to array
        $classroom->sections = array_map('trim', explode(',', $validated['sections']));

        // Calculate total fee using model method
        $classroom->total_fee = $classroom->calculateTotalFee();

        $classroom->save();

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Class updated successfully!');
    }

    /**
     * Remove the specified classroom
     */
    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Class deleted successfully!');
    }
}