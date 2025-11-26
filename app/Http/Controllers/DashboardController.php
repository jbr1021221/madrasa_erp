<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Check if tables exist
            $studentsExist = Schema::hasTable('students');
            $paymentsExist = Schema::hasTable('payments');

            // Get total students
            $totalStudents = $studentsExist ? DB::table('students')->count() : 0;

            // Get total classes
            $totalClasses = Classroom::count();

            // Get total earnings
            $totalEarnings = $paymentsExist ? DB::table('payments')->sum('amount') ?? 0 : 0;

            // Get class-wise student count
            $classWiseData = [];
            if ($studentsExist && Schema::hasColumn('students', 'class_id')) {
                $classWiseData = DB::table('students')
                    ->join('classrooms', 'students.class_id', '=', 'classrooms.id')
                    ->select('classrooms.name as className', DB::raw('count(*) as total'))
                    ->groupBy('classrooms.id', 'classrooms.name')
                    ->get()
                    ->toArray();
            }

            // Monthly admissions (demo data for now)
            $admissions = [12, 18, 21, 14, 25, 30, 22, 28, 18, 15, 10, 7];

            // Monthly earnings (demo data for now)
            $earnings = [50000, 62000, 48000, 75000, 80000, 90000, 85000, 72000, 56000, 60000, 45000, 40000];

            return view('dashboard', compact(
                'totalStudents',
                'totalClasses',
                'totalEarnings',
                'classWiseData',
                'admissions',
                'earnings'
            ));

        } catch (\Exception $e) {
            // If any error, return with default demo data
            return view('dashboard', [
                'totalStudents' => 0,
                'totalClasses' => 0,
                'totalEarnings' => 0,
                'classWiseData' => [],
                'admissions' => [12, 18, 21, 14, 25, 30, 22, 28, 18, 15, 10, 7],
                'earnings' => [50000, 62000, 48000, 75000, 80000, 90000, 85000, 72000, 56000, 60000, 45000, 40000]
            ]);
        }
    }
}