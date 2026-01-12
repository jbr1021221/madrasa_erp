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
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->get(route('students.index'));

        $response->assertStatus(200);
        $response->assertSee($student->name);
    }

    public function test_can_create_student()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
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
            'shift' => 'Morning',
            'program_type' => ['Schooling'],
        ];

        $response = $this->actingAs($user)->post(route('students.store'), $data);

        $this->assertDatabaseHas('students', [
            'name' => 'Jane Doe',
            'guardian_email' => 'guardian@example.com'
        ]);
        
        $student = Student::where('email', 'guardian@example.com')->orWhere('name', 'Jane Doe')->first();
        if ($student) {
             $response->assertRedirect(route('students.receipt.confirm', $student));
        }
    }

    public function test_can_update_student()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
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

        $response = $this->actingAs($user)->put(route('students.update', $student), $data);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'New Name',
        ]);
    }

    public function test_can_delete_student()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $student = Student::factory()->create();

        $response = $this->actingAs($user)->delete(route('students.destroy', $student));

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_can_create_student_with_fees()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create([
            'admission_fee' => 5000,
            'fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
                ['name' => 'Library Fee', 'type' => 'One Time', 'amount' => 500],
            ]
        ]);

        $selectedFees = [
            ['name' => 'Admission Fee', 'type' => 'One Time', 'amount' => 5000, 'discount' => 0],
            ['name' => 'Library Fee', 'type' => 'One Time', 'amount' => 500, 'discount' => 100],
        ];

        $data = [
            'name' => 'Test Student',
            'father_name' => 'Test Father',
            'mobile' => '01712345678',
            'class_id' => $classroom->id,
            'section' => 'A',
            'payment_mode' => 'Cash',
            'total_admission_fee' => 5400,
            'shift' => 'Morning',
            'program_type' => ['Schooling'],
            'student_assigned_fees' => json_encode($selectedFees),
            'selected_admission_fees' => json_encode($selectedFees),
        ];

        $response = $this->actingAs($user)->post(route('students.store'), $data);

        $student = Student::where('name', 'Test Student')->first();
        $this->assertNotNull($student);
        $this->assertNotNull($student->selected_fees);
        $this->assertIsArray($student->selected_fees);
    }

    public function test_can_update_student_fees()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create([
            'fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
                ['name' => 'Sports Fee', 'type' => 'Monthly', 'amount' => 200],
            ]
        ]);
        
        $student = Student::factory()->create([
            'class_id' => $classroom->id,
            'selected_fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
            ],
            'discounts' => []
        ]);

        $updatedFees = [
            ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000, 'discount' => 100, 'is_permanent' => true],
            ['name' => 'Sports Fee', 'type' => 'Monthly', 'amount' => 200, 'discount' => 0, 'is_permanent' => false],
        ];

        $data = [
            'name' => $student->name,
            'father_name' => $student->father_name,
            'mobile' => $student->mobile,
            'class_id' => $classroom->id,
            'section' => $student->section,
            'student_assigned_fees' => json_encode($updatedFees),
        ];

        $response = $this->actingAs($user)->put(route('students.update', $student), $data);

        $response->assertRedirect(route('students.index'));
        
        $student->refresh();
        $this->assertNotNull($student->discounts);
        $this->assertArrayHasKey('Tuition Fee', $student->discounts);
        $this->assertEquals(100, $student->discounts['Tuition Fee']['amount']);
        $this->assertEquals(1, $student->discounts['Tuition Fee']['permanent']);
    }

    public function test_can_add_fee_to_student()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create([
            'fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
                ['name' => 'Transport Fee', 'type' => 'Monthly', 'amount' => 300],
            ]
        ]);
        
        $student = Student::factory()->create([
            'class_id' => $classroom->id,
            'selected_fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
            ]
        ]);

        // Add Transport Fee
        $updatedFees = [
            ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000, 'discount' => 0, 'is_permanent' => false],
            ['name' => 'Transport Fee', 'type' => 'Monthly', 'amount' => 300, 'discount' => 0, 'is_permanent' => false],
        ];

        $data = [
            'name' => $student->name,
            'father_name' => $student->father_name,
            'mobile' => $student->mobile,
            'class_id' => $classroom->id,
            'section' => $student->section,
            'student_assigned_fees' => json_encode($updatedFees),
        ];

        $response = $this->actingAs($user)->put(route('students.update', $student), $data);

        $student->refresh();
        $this->assertCount(2, $student->selected_fees);
        $this->assertTrue(collect($student->selected_fees)->contains('name', 'Transport Fee'));
    }

    public function test_can_remove_fee_from_student()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create([
            'fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
                ['name' => 'Transport Fee', 'type' => 'Monthly', 'amount' => 300],
            ]
        ]);
        
        $student = Student::factory()->create([
            'class_id' => $classroom->id,
            'selected_fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
                ['name' => 'Transport Fee', 'type' => 'Monthly', 'amount' => 300],
            ]
        ]);

        // Remove Transport Fee
        $updatedFees = [
            ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000, 'discount' => 0, 'is_permanent' => false],
        ];

        $data = [
            'name' => $student->name,
            'father_name' => $student->father_name,
            'mobile' => $student->mobile,
            'class_id' => $classroom->id,
            'section' => $student->section,
            'student_assigned_fees' => json_encode($updatedFees),
        ];

        $response = $this->actingAs($user)->put(route('students.update', $student), $data);

        $student->refresh();
        $this->assertCount(1, $student->selected_fees);
        $this->assertFalse(collect($student->selected_fees)->contains('name', 'Transport Fee'));
    }

    public function test_can_update_fee_discount()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create([
            'fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
            ]
        ]);
        
        $student = Student::factory()->create([
            'class_id' => $classroom->id,
            'selected_fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
            ],
            'discounts' => [
                'Tuition Fee' => ['amount' => 100, 'permanent' => 0]
            ]
        ]);

        // Update discount to 200 and make it permanent
        $updatedFees = [
            ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000, 'discount' => 200, 'is_permanent' => true],
        ];

        $data = [
            'name' => $student->name,
            'father_name' => $student->father_name,
            'mobile' => $student->mobile,
            'class_id' => $classroom->id,
            'section' => $student->section,
            'student_assigned_fees' => json_encode($updatedFees),
        ];

        $response = $this->actingAs($user)->put(route('students.update', $student), $data);

        $student->refresh();
        $this->assertEquals(200, $student->discounts['Tuition Fee']['amount']);
        $this->assertEquals(1, $student->discounts['Tuition Fee']['permanent']);
    }

    public function test_can_delete_fee_discount()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create([
            'fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
            ]
        ]);
        
        $student = Student::factory()->create([
            'class_id' => $classroom->id,
            'selected_fees' => [
                ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000],
            ],
            'discounts' => [
                'Tuition Fee' => ['amount' => 100, 'permanent' => 1]
            ]
        ]);

        // Remove discount (set to 0)
        $updatedFees = [
            ['name' => 'Tuition Fee', 'type' => 'Monthly', 'amount' => 1000, 'discount' => 0, 'is_permanent' => false],
        ];

        $data = [
            'name' => $student->name,
            'father_name' => $student->father_name,
            'mobile' => $student->mobile,
            'class_id' => $classroom->id,
            'section' => $student->section,
            'student_assigned_fees' => json_encode($updatedFees),
        ];

        $response = $this->actingAs($user)->put(route('students.update', $student), $data);

        $student->refresh();
        // Discount should still exist but with 0 amount
        $this->assertArrayHasKey('Tuition Fee', $student->discounts);
    }
}
