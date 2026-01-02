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

            // Monthly admissions (Real data)
            $admissions = array_fill(0, 12, 0);
            
            if (DB::getDriverName() === 'sqlite') {
                $studentData = DB::table('students')
                    ->select(DB::raw('count(*) as count'), DB::raw('strftime("%m", created_at) as month'))
                    ->whereYear('created_at', date('Y'))
                    ->groupBy(DB::raw('strftime("%m", created_at)'))
                    ->get();
            } else {
                $studentData = DB::table('students')
                    ->select(DB::raw('count(*) as count'), DB::raw('MONTH(created_at) as month'))
                    ->whereYear('created_at', date('Y'))
                    ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                    ->get();
            }

            foreach ($studentData as $data) {
                // month is 1-12 (or "01"-"12" for sqlite), array index is 0-11
                $admissions[(int)$data->month - 1] = $data->count;
            }

            // Monthly earnings (Real data)
            $earnings = array_fill(0, 12, 0);
            
            if (DB::getDriverName() === 'sqlite') {
                $paymentData = DB::table('payments')
                    ->select(DB::raw('sum(amount) as total'), DB::raw('strftime("%m", payment_date) as month'))
                    ->whereYear('payment_date', date('Y'))
                    ->groupBy(DB::raw('strftime("%m", payment_date)'))
                    ->get();
            } else {
                $paymentData = DB::table('payments')
                    ->select(DB::raw('sum(amount) as total'), DB::raw('MONTH(payment_date) as month'))
                    ->whereYear('payment_date', date('Y'))
                    ->groupBy(DB::raw('YEAR(payment_date)'), DB::raw('MONTH(payment_date)'))
                    ->get();
            }

            foreach ($paymentData as $data) {
                $earnings[(int)$data->month - 1] = $data->total;
            }

            // Get total users
            $totalUsers = \App\Models\User::count();

            // Get today's earnings
            $todaysEarnings = $paymentsExist ? DB::table('payments')->whereDate('payment_date', now()->toDateString())->sum('amount') ?? 0 : 0;

            return view('dashboard', compact(
                'totalStudents',
                'totalClasses',
                'totalEarnings',
                'totalUsers',
                'todaysEarnings',
                'classWiseData',
                'admissions',
                'earnings'
            ));
    }
}