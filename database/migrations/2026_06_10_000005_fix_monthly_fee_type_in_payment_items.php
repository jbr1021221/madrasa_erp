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
        // Fix payment_items where:
        // 1. fee_name contains a month pattern (e.g., "- January, 26")
        // 2. But fee_type is NOT "Monthly" (could be "Admission", "Yearly", etc.)
        // 3. month/year fields are empty or incorrect

        $paymentItems = DB::table('payment_items')
            ->where('fee_name', 'LIKE', '% - %, %')
            ->where(function($q) {
                $q->where('fee_type', '!=', 'Monthly')
                  ->orWhereNull('fee_type');
            })
            ->get();

        foreach ($paymentItems as $item) {
            $feeName = $item->fee_name;

            // Extract month and year from fee_name pattern: "Anything - Month, YY"
            // Examples: "Monthly Tuition Fee - January, 26", "Hifz - February, 26"
            if (preg_match('/\s*-\s*([A-Za-z]+)\s*,\s*(\d{2})$/', $feeName, $matches)) {
                $monthName = $matches[1]; // e.g., "January"
                $year = $matches[2];       // e.g., "26"

                // Update the payment_item with correct values
                DB::table('payment_items')
                    ->where('id', $item->id)
                    ->update([
                        'fee_type' => 'Monthly',
                        'month' => $monthName,
                        'year' => $year
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: This is difficult to reverse perfectly, so we'll just clear the fields
        // In production, you might want to store the original values before updating
        DB::table('payment_items')
            ->where('fee_type', 'Monthly')
            ->whereNotNull('month')
            ->whereNotNull('year')
            ->update([
                'fee_type' => 'Other', // Can't restore original, so use 'Other'
                'month' => null,
                'year' => null
            ]);
    }
};
