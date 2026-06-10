<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
            // Check if tables exist
            $studentsExist = Schema::hasTable('students');
            $paymentsExist = Schema::hasTable('payments');

            // Get total active students (excluding soft-deleted)
            $totalStudents = $studentsExist ? DB::table('students')->where('is_active', 1)->whereNull('deleted_at')->count() : 0;

            // Get total classes
            $totalClasses = Classroom::count();

            // Get total earnings
            $totalEarnings = $paymentsExist ? DB::table('payments')->sum('final_amount') ?? 0 : 0;

            // Get class-wise active student count (excluding soft-deleted)
            $classWiseData = [];
            if ($studentsExist && Schema::hasColumn('students', 'class_id')) {
                $classWiseData = DB::table('students')
                    ->join('classrooms', 'students.class_id', '=', 'classrooms.id')
                    ->where('students.is_active', 1)
                    ->whereNull('students.deleted_at')
                    ->select('classrooms.name as className', DB::raw('count(*) as total'))
                    ->groupBy('classrooms.id', 'classrooms.name')
                    ->get()
                    ->toArray();
            }

            // Monthly admissions (Real data)
            $admissions = array_fill(0, 12, 0);
            
            if (DB::getDriverName() === 'sqlite') {
                $studentData = DB::table('students')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at')
                    ->select(DB::raw('count(*) as count'), DB::raw('strftime("%m", created_at) as month'))
                    ->whereYear('created_at', date('Y'))
                    ->groupBy(DB::raw('strftime("%m", created_at)'))
                    ->get();
            } else {
                $studentData = DB::table('students')
                    ->where('is_active', 1)
                    ->whereNull('deleted_at')
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
                    ->select(DB::raw('sum(final_amount) as total'), DB::raw('strftime("%m", payment_date) as month'))
                    ->whereYear('payment_date', date('Y'))
                    ->groupBy(DB::raw('strftime("%m", payment_date)'))
                    ->get();
            } else {
                $paymentData = DB::table('payments')
                    ->select(DB::raw('sum(final_amount) as total'), DB::raw('MONTH(payment_date) as month'))
                    ->whereYear('payment_date', date('Y'))
                    ->groupBy(DB::raw('YEAR(payment_date)'), DB::raw('MONTH(payment_date)'))
                    ->get();
            }

            foreach ($paymentData as $data) {
                $earnings[(int)$data->month - 1] = $data->total;
            }

            // Get today's earnings
            $todaysEarnings = $paymentsExist ? DB::table('payments')->whereDate('payment_date', now()->toDateString())->sum('final_amount') ?? 0 : 0;

            return view('dashboard', compact(
                'totalStudents',
                'totalClasses',
                'totalEarnings',
                'todaysEarnings',
                'classWiseData',
                'admissions',
                'earnings'
            ));
    }
}