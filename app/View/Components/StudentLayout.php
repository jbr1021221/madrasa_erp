<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Classroom;
use Illuminate\Support\Facades\Request;

class StudentLayout extends Component
{
    public $students;
    public $classrooms;
    public $sections;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($students)
    {
        $this->students = $students;
        $this->classrooms = Classroom::all();

        // Logic to get sections (mirroring controller logic)
        $selectedClass = null;
        if (request()->filled('class_id')) {
            $selectedClass = $this->classrooms->find(request('class_id'));
            $this->sections = $selectedClass ? $selectedClass->sections : [];
        } else {
             $this->sections = $this->classrooms->pluck('sections')->flatten()->unique()->sort()->values()->all();
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.student-layout');
    }
}
