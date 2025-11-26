<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_record_payment_and_update_fee_status()
    {
        $classroom = Classroom::factory()->create();
        $student = Student::create([
            'name' => 'Test Student',
            'class_id' => $classroom->id,
            'roll' => 1,
            'email' => 'test@example.com'
        ]);

        $fee = Fee::create([
            'student_id' => $student->id,
            'type' => 'Monthly',
            'amount' => 1000,
            'due_date' => now()->addDays(10),
            'status' => 'pending'
        ]);

        $response = $this->post(route('payments.store'), [
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'amount' => 500,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash'
        ]);

        $response->assertRedirect(route('payments.index'));
        
        $this->assertDatabaseHas('payments', [
            'amount' => 500,
            'student_id' => $student->id,
            'fee_id' => $fee->id
        ]);

        $fee->refresh();
        $this->assertEquals('partial', $fee->status);

        // Pay remaining amount
        $this->post(route('payments.store'), [
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'amount' => 500,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash'
        ]);

        $fee->refresh();
        $this->assertEquals('paid', $fee->status);
    }
}
