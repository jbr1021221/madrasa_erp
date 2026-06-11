<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing student_months records by recalculating status based on payment_items
        $studentMonths = DB::table('student_months')->get();

        foreach ($studentMonths as $studentMonth) {
            $studentId = $studentMonth->student_id;
            $paymentId = $studentMonth->payment_id;

            // Get student's required monthly fees
            $student = DB::table('students')->where('id', $studentId)->first();
            if (!$student) continue;

            $selectedFees = json_decode($student->selected_fees, true) ?? [];
            $monthlyFees = array_filter($selectedFees, function($fee) {
                return isset($fee['type']) && strtolower($fee['type']) === 'monthly';
            });
            $requiredMonthlyFeeNames = array_map(function($fee) {
                return $fee['name'];
            }, $monthlyFees);

            // Get payment items for this payment
            $paymentItems = DB::table('payment_items')
                ->where('payment_id', $paymentId)
                ->where('student_id', $studentId)
                ->get();

            $paidMonthlyFeeNames = [];
            foreach ($paymentItems as $item) {
                if (strtolower($item->fee_type ?? '') === 'monthly') {
                    // Extract base fee name from fee_name (remove month suffix like " - June, 26")
                    $baseFeeName = preg_replace('/\s*-\s*[A-Za-z]+,\s*\d+$/', '', $item->fee_name);
                    if (!empty($baseFeeName)) {
                        $paidMonthlyFeeNames[] = $baseFeeName;
                    }
                }
            }

            // Calculate correct status based on fee coverage
            $paidFeeCount = count(array_intersect($requiredMonthlyFeeNames, $paidMonthlyFeeNames));
            $totalRequiredFees = count($requiredMonthlyFeeNames);

            if ($paidFeeCount > 0 && $totalRequiredFees > 0) {
                $correctStatus = ($paidFeeCount >= $totalRequiredFees) ? 'paid' : 'partial';

                // Update the record with correct status
                DB::table('student_months')
                    ->where('id', $studentMonth->id)
                    ->update(['status' => $correctStatus]);
            } elseif ($paidFeeCount === 0 || $totalRequiredFees === 0) {
                // No monthly fees covered or no monthly fees required - delete record
                DB::table('student_months')
                    ->where('id', $studentMonth->id)
                    ->delete();
            }
        }

        echo "Fixed " . count($studentMonths) . " student_months records based on payment_items coverage.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For rollback, we'll just set all records back to 'paid'
        DB::table('student_months')
            ->whereNotNull('status')
            ->update(['status' => 'paid']);
    }
};
