<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_students()
    {
        $student = Student::factory()->create();

        $response = $this->get(route('students.index'));

        $response->assertStatus(200);
        $response->assertSee($student->name);
    }

    public function test_can_create_student()
    {
        $classroom = Classroom::factory()->create();

        $data = [
            'name' => 'Jane Doe',
            'father_name' => 'John Doe Sr.',
            'mother_name' => 'Jane Doe Sr.',
            'address' => '123 Main St',
            'mobile' => '01712345678',
            'alt_mobile' => '01812345678',
            'class_id' => $classroom->id,
            'section' => 'A',
            'payment_mode' => 'Cash',
            'total_admission_fee' => 1000,
            'dob' => '2015-01-01',
            'gender' => 'Female',
            'blood_group' => 'A+',
            'last_school' => 'Previous School',
            'siblings_count' => 1,
            'birth_order' => 1,
            'present_district' => 'Dhaka',
            'permanent_address' => '456 Another St',
            'permanent_district' => 'Chittagong',
            'guardian_occupation' => 'Engineer',
            'guardian_nationality' => 'Bangladeshi',
            'guardian_phone' => '01912345678',
            'guardian_email' => 'guardian@example.com',
            'guardian_nid' => '1234567890',
        ];

        $response = $this->post(route('students.store'), $data);

        $response->assertRedirect(route('students.receipt.confirm', Student::first()));
        $this->assertDatabaseHas('students', [
            'name' => 'Jane Doe',
            'guardian_email' => 'guardian@example.com'
        ]);
    }

    public function test_can_update_student()
    {
        $student = Student::factory()->create();

        $data = [
            'name' => 'New Name',
            'father_name' => $student->father_name,
            'mother_name' => $student->mother_name,
            'address' => $student->address,
            'mobile' => $student->mobile,
            'class_id' => $student->class_id,
            'section' => $student->section,
            'dob' => $student->dob,
            'gender' => $student->gender,
            'present_district' => $student->present_district,
            'guardian_occupation' => $student->guardian_occupation,
            'guardian_nationality' => $student->guardian_nationality,
            'guardian_phone' => $student->guardian_phone,
            'guardian_nid' => $student->guardian_nid,
        ];

        $response = $this->put(route('students.update', $student), $data);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'New Name',
        ]);
    }

    public function test_can_delete_student()
    {
        $student = Student::factory()->create();

        $response = $this->delete(route('students.destroy', $student));

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
