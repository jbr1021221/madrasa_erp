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
        // Step 1: Add new columns as nullable (check if they don't exist first)
        if (!Schema::hasColumn('payments', 'sub_total')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('sub_total', 10, 2)->nullable()->after('amount');
            });
        }

        if (!Schema::hasColumn('payments', 'discount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('discount', 10, 2)->nullable()->after('sub_total');
            });
        }

        // Step 2: Migrate existing data from fee_details JSON
        $payments = DB::table('payments')->get();

        foreach ($payments as $payment) {
            $subTotal = 0;
            $totalDiscount = 0;

            if ($payment->fee_details) {
                $feeDetails = json_decode($payment->fee_details, true);
                if (is_array($feeDetails)) {
                    foreach ($feeDetails as $fee) {
                        $subTotal += floatval($fee['original_amount'] ?? $fee['amount'] ?? 0);
                        $totalDiscount += floatval($fee['discount'] ?? 0);
                    }
                }
            }

            // For records without fee_details, use current amount
            if ($subTotal === 0) {
                $subTotal = $payment->amount;
                $totalDiscount = 0;
            }

            DB::table('payments')
                ->where('id', $payment->id)
                ->update([
                    'sub_total' => $subTotal,
                    'discount' => $totalDiscount
                ]);
        }

        // Step 3: Make columns non-nullable
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('sub_total', 10, 2)->nullable(false)->change();
            $table->decimal('discount', 10, 2)->nullable(false)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['sub_total', 'discount']);
        });
    }
};
