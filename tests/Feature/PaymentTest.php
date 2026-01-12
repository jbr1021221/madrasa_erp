<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_record_payment()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->create(['class_id' => $classroom->id]);

        $data = [
            'student_id' => $student->id,
            'amount' => 500,
            'payment_date' => now()->format('Y-m-d'),
            'month' => 'January',
            'payment_type' => 'Monthly Fee',
            'payment_mode' => 'Cash',
            'note' => 'Test payment',
        ];

        $response = $this->actingAs($user)->post(route('payments.store'), $data);

        $response->assertRedirect(route('payments.index'));
        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'amount' => 500,
            'month' => 'January',
            'payment_type' => 'Monthly Fee',
        ]);
    }

    public function test_can_update_payment()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->create(['class_id' => $classroom->id]);

        $payment = Payment::create([
            'student_id' => $student->id,
            'amount' => 500,
            'payment_date' => now()->format('Y-m-d'),
            'month' => 'January',
            'payment_type' => 'Monthly Fee',
            'payment_mode' => 'Cash',
        ]);

        $data = [
            'student_id' => $student->id,
            'amount' => 600,
            'payment_date' => now()->addDay()->format('Y-m-d'),
            'month' => 'February',
            'payment_type' => 'Exam Fee',
            'payment_mode' => 'Bank',
            'note' => 'Updated payment',
        ];

        $response = $this->actingAs($user)->put(route('payments.update', $payment), $data);

        $response->assertRedirect(route('payments.index'));
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'amount' => 600,
            'month' => 'February',
            'payment_type' => 'Exam Fee',
        ]);
    }

    public function test_can_delete_payment()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->create(['class_id' => $classroom->id]);

        $payment = Payment::create([
            'student_id' => $student->id,
            'amount' => 500,
            'payment_date' => now()->format('Y-m-d'),
            'month' => 'January',
            'payment_type' => 'Monthly Fee',
            'payment_mode' => 'Cash',
        ]);

        $response = $this->actingAs($user)->delete(route('payments.destroy', $payment));

        $response->assertRedirect(route('payments.index'));
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
