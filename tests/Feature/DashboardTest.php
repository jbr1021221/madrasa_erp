<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_correct_total_counts()
    {
        $classroom = Classroom::factory()->create();
        Student::factory()->count(5)->create(['class_id' => $classroom->id]);
        
        // Create payments
        $student = Student::first();
        Payment::create([
            'student_id' => $student->id,
            'amount' => 1000,
            'payment_date' => now(),
            'payment_type' => 'Monthly',
            'payment_mode' => 'Cash',
            'month' => 'January'
        ]);
        Payment::create([
            'student_id' => $student->id,
            'amount' => 500,
            'payment_date' => now(),
            'payment_type' => 'Monthly',
            'payment_mode' => 'Cash',
            'month' => 'February'
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('totalStudents', 5);
        $response->assertViewHas('totalEarnings', 1500);
    }

    public function test_dashboard_displays_correct_monthly_charts()
    {
        $classroom = Classroom::factory()->create();
        
        // Create students in different months
        // Note: created_at is timestamp, so we need to be careful with factory
        // We'll manually create to set created_at
        DB::table('students')->insert([
            'student_id' => '1001',
            'name' => 'Jan Student',
            'class_id' => $classroom->id,
            'created_at' => now()->startOfYear()->addMonth(0), // January
            'updated_at' => now(),
            // Add required fields to satisfy NOT NULL constraints
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'address' => 'Address',
            'mobile' => '01700000000',
            'section' => 'A',
            'present_district' => 'Dhaka',
            'guardian_occupation' => 'Job',
            'guardian_nationality' => 'BD',
            'guardian_phone' => '01700000000',
            'guardian_nid' => '123'
        ]);

        DB::table('students')->insert([
            'student_id' => '1002',
            'name' => 'Feb Student',
            'class_id' => $classroom->id,
            'created_at' => now()->startOfYear()->addMonth(1), // February
            'updated_at' => now(),
            'father_name' => 'Father',
            'mother_name' => 'Mother',
            'address' => 'Address',
            'mobile' => '01700000000',
            'section' => 'A',
            'present_district' => 'Dhaka',
            'guardian_occupation' => 'Job',
            'guardian_nationality' => 'BD',
            'guardian_phone' => '01700000000',
            'guardian_nid' => '123'
        ]);

        // Create payments in different months
        $studentId = DB::table('students')->first()->id;
        
        Payment::create([
            'student_id' => $studentId,
            'amount' => 1000,
            'payment_date' => now()->startOfYear()->addMonth(0), // January
            'payment_type' => 'Monthly',
            'payment_mode' => 'Cash',
            'month' => 'January'
        ]);

        Payment::create([
            'student_id' => $studentId,
            'amount' => 2000,
            'payment_date' => now()->startOfYear()->addMonth(1), // February
            'payment_type' => 'Monthly',
            'payment_mode' => 'Cash',
            'month' => 'February'
        ]);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        $admissions = $response->viewData('admissions');
        $earnings = $response->viewData('earnings');

        // Check January (index 0)
        $this->assertEquals(1, $admissions[0]);
        $this->assertEquals(1000, $earnings[0]);

        // Check February (index 1)
        $this->assertEquals(1, $admissions[1]);
        $this->assertEquals(2000, $earnings[1]);
        
        // Check March (index 2) - should be 0
        $this->assertEquals(0, $admissions[2]);
        $this->assertEquals(0, $earnings[2]);
    }
}
