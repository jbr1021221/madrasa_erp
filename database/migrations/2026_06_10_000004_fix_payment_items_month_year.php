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
        // Fix payment_items where month contains both month and year (e.g., "January, 26")
        $paymentItems = DB::table('payment_items')
            ->where('fee_type', 'Monthly')
            ->whereNotNull('month')
            ->where('month', 'LIKE', '%,%')
            ->get();

        foreach ($paymentItems as $item) {
            $monthValue = $item->month;

            // Parse "January, 26" or "January,26" format
            if (preg_match('/^([A-Za-z]+)\s*,\s*(\d{2})$/', $monthValue, $matches)) {
                $monthName = $matches[1]; // e.g., "January"
                $year = $matches[2];       // e.g., "26"

                DB::table('payment_items')
                    ->where('id', $item->id)
                    ->update([
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
        // Revert: combine month and year back
        $paymentItems = DB::table('payment_items')
            ->where('fee_type', 'Monthly')
            ->whereNotNull('month')
            ->whereNotNull('year')
            ->get();

        foreach ($paymentItems as $item) {
            $combinedMonth = $item->month . ', ' . $item->year;

            DB::table('payment_items')
                ->where('id', $item->id)
                ->update([
                    'month' => $combinedMonth,
                    'year' => null
                ]);
        }
    }
};
