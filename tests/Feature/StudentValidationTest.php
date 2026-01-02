<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_creation_requires_mandatory_fields()
    {
        $response = $this->post(route('students.store'), []);

        $response->assertSessionHasErrors([
            'name', 'father_name', 'mother_name', 'address', 'mobile',
            'class_id', 'section', 'payment_mode', 'total_admission_fee',
            'dob', 'gender', 'present_district', 'guardian_occupation',
            'guardian_nationality', 'guardian_phone', 'guardian_nid'
        ]);
    }

    public function test_mobile_number_must_be_valid_bd_format()
    {
        $classroom = Classroom::factory()->create();
        $studentData = Student::factory()->make([
            'class_id' => $classroom->id,
            'mobile' => '1234567890', // Invalid
            'payment_mode' => 'Cash',
            'total_admission_fee' => 1000,
        ])->toArray();

        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasErrors(['mobile']);

        $studentData['mobile'] = '01712345678'; // Valid
        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasNoErrors();
    }

    public function test_guardian_phone_must_be_valid_bd_format()
    {
        $classroom = Classroom::factory()->create();
        $studentData = Student::factory()->make([
            'class_id' => $classroom->id,
            'guardian_phone' => 'invalid-phone',
            'payment_mode' => 'Cash',
            'total_admission_fee' => 1000,
        ])->toArray();

        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasErrors(['guardian_phone']);
    }

    public function test_dob_must_be_before_today()
    {
        $classroom = Classroom::factory()->create();
        $studentData = Student::factory()->make([
            'class_id' => $classroom->id,
            'dob' => now()->addDay()->format('Y-m-d'), // Future date
            'payment_mode' => 'Cash',
            'total_admission_fee' => 1000,
        ])->toArray();

        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasErrors(['dob']);
    }

    public function test_guardian_email_must_be_valid()
    {
        $classroom = Classroom::factory()->create();
        $studentData = Student::factory()->make([
            'class_id' => $classroom->id,
            'guardian_email' => 'not-an-email',
            'payment_mode' => 'Cash',
            'total_admission_fee' => 1000,
        ])->toArray();

        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasErrors(['guardian_email']);
    }
}
