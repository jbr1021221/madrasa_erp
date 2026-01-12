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

    protected function setUp(): void
    {
        parent::setUp();
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
    }

    private function getValidStudentData()
    {
        $classroom = Classroom::factory()->create();
        $data = Student::factory()->make([
            'class_id' => $classroom->id,
            'payment_mode' => 'Cash',
            'total_admission_fee' => 1000,
        ])->toArray();

        unset($data['id'], $data['created_at'], $data['updated_at']);
        $data['program_type'] = ['Schooling']; // Ensure array
        
        return $data;
    }

    public function test_student_creation_requires_mandatory_fields()
    {
        $response = $this->post(route('students.store'), []);

        $response->assertSessionHasErrors([
            'name', 'father_name', 'mobile',
            'class_id', 'section', 'payment_mode', 'total_admission_fee',
            'shift', 'program_type'
        ]);
        
        // Assert that nullable fields do NOT have errors
        $response->assertSessionDoesntHaveErrors([
            'mother_name', 'address', 'dob', 'gender', 
            'present_district', 'guardian_occupation', 
            'guardian_nationality', 'guardian_phone', 'guardian_nid'
        ]);
    }

    public function test_mobile_number_must_be_valid_bd_format()
    {
        $studentData = $this->getValidStudentData();
        $studentData['mobile'] = '1234567890'; // Invalid

        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasErrors(['mobile']);

        $studentData['mobile'] = '01712345678'; // Valid
        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasNoErrors();
    }

    public function test_guardian_phone_must_be_valid_bd_format()
    {
        $studentData = $this->getValidStudentData();
        $studentData['guardian_phone'] = 'invalid-phone';

        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasErrors(['guardian_phone']);
    }

    public function test_dob_must_be_before_today()
    {
        $studentData = $this->getValidStudentData();
        $studentData['dob'] = now()->addDay()->format('Y-m-d'); // Future date

        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasErrors(['dob']);
    }

    public function test_guardian_email_must_be_valid()
    {
        $studentData = $this->getValidStudentData();
        $studentData['guardian_email'] = 'not-an-email';

        $response = $this->post(route('students.store'), $studentData);
        $response->assertSessionHasErrors(['guardian_email']);
    }
}
