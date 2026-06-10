<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing fee_details JSON to payment_items table
        $payments = DB::table('payments')->whereNotNull('fee_details')->get();

        foreach ($payments as $payment) {
            $feeDetails = json_decode($payment->fee_details, true);

            if (is_array($feeDetails)) {
                foreach ($feeDetails as $fee) {
                    // Skip if the fee already exists in payment_items for this payment
                    $exists = DB::table('payment_items')
                        ->where('payment_id', $payment->id)
                        ->where('fee_name', $fee['name'] ?? 'Fee')
                        ->exists();

                    if (!$exists) {
                        DB::table('payment_items')->insert([
                            'payment_id' => $payment->id,
                            'student_id' => $payment->student_id,
                            'fee_name' => $fee['name'] ?? 'Fee',
                            'fee_type' => $fee['type'] ?? 'Other',
                            'month' => $fee['month'] ?? null,
                            'year' => $fee['year'] ?? null,
                            'amount' => $fee['amount'] ?? 0,
                            'original_amount' => $fee['original_amount'] ?? $fee['amount'] ?? 0,
                            'discount' => $fee['discount'] ?? 0,
                            'created_at' => $payment->created_at,
                            'updated_at' => $payment->updated_at
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: Delete all payment_items that were created from fee_details
        // This is a simple rollback - in production you might want to be more careful
        DB::table('payment_items')->truncate();
    }
};
