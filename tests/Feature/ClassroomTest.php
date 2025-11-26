<?php

namespace Tests\Feature;

use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_classrooms()
    {
        Classroom::factory()->create(['name' => 'Class 1']);
        Classroom::factory()->create(['name' => 'Class 2']);

        $response = $this->get(route('classrooms.index'));

        $response->assertStatus(200);
        $response->assertSee('Class 1');
        $response->assertSee('Class 2');
    }

    public function test_can_create_classroom()
    {
        $data = [
            'name' => 'Class 1',
            'sections' => 'A, B, C',
            'max_students_per_section' => 30,
            'fees' => [
                ['name' => 'Admission', 'amount' => 1000, 'type' => 'One Time'],
                ['name' => 'Monthly Fee', 'amount' => 500, 'type' => 'Monthly'],
            ],
        ];

        $response = $this->post(route('classrooms.store'), $data);

        $response->assertRedirect(route('classrooms.index'));
        $this->assertDatabaseHas('classrooms', [
            'name' => 'Class 1',
            'total_fee' => 1500, // 1000 + 500
        ]);
        
        $classroom = Classroom::first();
        $this->assertEquals(['A', 'B', 'C'], $classroom->sections);
    }

    public function test_can_update_classroom()
    {
        $classroom = Classroom::factory()->create();

        $data = [
            'name' => 'Updated Class',
            'sections' => 'X, Y',
            'max_students_per_section' => 25,
            'fees' => [
                ['name' => 'Exam Fee', 'amount' => 200, 'type' => 'Yearly'],
            ],
        ];

        $response = $this->put(route('classrooms.update', $classroom), $data);

        $response->assertRedirect(route('classrooms.index'));
        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'name' => 'Updated Class',
            'total_fee' => 200,
        ]);

        $classroom->refresh();
        $this->assertEquals(['X', 'Y'], $classroom->sections);
    }

    public function test_can_delete_classroom()
    {
        $classroom = Classroom::factory()->create();

        $response = $this->delete(route('classrooms.destroy', $classroom));

        $response->assertRedirect(route('classrooms.index'));
        $this->assertDatabaseMissing('classrooms', ['id' => $classroom->id]);
    }
}
