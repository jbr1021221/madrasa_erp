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
        $classroom = Classroom::factory()->create();
        Student::create([
            'name' => 'John Doe',
            'class_id' => $classroom->id,
            'roll' => 1,
            'email' => 'john@example.com'
        ]);

        $response = $this->get(route('students.index'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    public function test_can_create_student()
    {
        $classroom = Classroom::factory()->create();

        $data = [
            'name' => 'Jane Doe',
            'class_id' => $classroom->id,
            'roll' => 2,
            'email' => 'jane@example.com'
        ];

        $response = $this->post(route('students.store'), $data);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);
    }

    public function test_can_update_student()
    {
        $classroom = Classroom::factory()->create();
        $student = Student::create([
            'name' => 'Old Name',
            'class_id' => $classroom->id,
            'roll' => 3,
            'email' => 'old@example.com'
        ]);

        $data = [
            'name' => 'New Name',
            'class_id' => $classroom->id,
            'roll' => 4,
            'email' => 'new@example.com'
        ];

        $response = $this->put(route('students.update', $student), $data);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'New Name',
            'email' => 'new@example.com'
        ]);
    }

    public function test_can_delete_student()
    {
        $classroom = Classroom::factory()->create();
        $student = Student::create([
            'name' => 'To Delete',
            'class_id' => $classroom->id,
            'roll' => 5,
            'email' => 'delete@example.com'
        ]);

        $response = $this->delete(route('students.destroy', $student));

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
