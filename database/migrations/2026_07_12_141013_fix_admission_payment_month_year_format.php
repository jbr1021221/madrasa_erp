<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentItem;
use App\Models\StudentMonth;
use App\Models\Student;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix PaymentItems with combined month/year format (e.g., "July, 26" or "July 26")
        $itemsToFix = PaymentItem::whereNotNull('month')
            ->where('fee_type', 'Monthly')
            ->where(function ($query) {
                $query->where('month', 'REGEXP', '[A-Za-z]+[, ]+[0-9]+')
                    ->orWhere('month', 'LIKE', '%,%');
            })
            ->get();

        echo "Found " . $itemsToFix->count() . " payment items with combined month/year format\n";

        foreach ($itemsToFix as $item) {
            // Parse combined format: "July, 26" or "July 26" or "July, 2026"
            $monthValue = trim($item->month);

            // Try to extract month and year
            if (preg_match('/^([A-Za-z]+)[,\s]+(\d+)$/', $monthValue, $matches)) {
                $month = trim($matches[1]);
                $yearPart = trim($matches[2]);

                // Convert to 2-digit year if 4-digit year provided
                $year = strlen($yearPart) === 4 ? substr($yearPart, -2) : $yearPart;

                echo "Fixing payment_item #{$item->id}: '{$monthValue}' -> month='{$month}', year='{$year}'\n";

                $item->update([
                    'month' => $month,
                    'year' => $year
                ]);
            }
        }

        // Create missing StudentMonth records for admission payments with monthly fees
        // Find all payments with type "Admission" that have monthly fee items
        $admissionPayments = \App\Models\Payment::where('payment_type', 'Admission')
            ->with(['payment_items' => function ($query) {
                $query->where('fee_type', 'Monthly')
                    ->whereNotNull('month')
                    ->whereNotNull('year');
            }])
            ->get();

        echo "\nProcessing " . $admissionPayments->count() . " admission payments\n";

        foreach ($admissionPayments as $payment) {
            $monthlyItems = $payment->payment_items->where('fee_type', 'Monthly')
                ->whereNotNull('month')
                ->whereNotNull('year');

            if ($monthlyItems->isEmpty()) {
                continue;
            }

            $student = Student::find($payment->student_id);
            if (!$student) {
                continue;
            }

            // Get student's required monthly fees
            $selectedFees = $student->selected_fees ?? [];
            $monthlyFees = collect($selectedFees)->filter(function ($fee) {
                return isset($fee['type']) && strtolower($fee['type']) === 'monthly';
            });
            $requiredMonthlyFeeNames = $monthlyFees->pluck('name')->unique()->toArray();

            // Get paid monthly fee names from payment items
            $paidMonthlyFeeNames = [];
            foreach ($monthlyItems as $item) {
                // Extract base fee name (remove month suffix like " - July, 26")
                $baseFeeName = preg_replace('/\s*-\s*[A-Za-z]+,\s*\d+$/', '', $item->fee_name);
                if (!empty($baseFeeName)) {
                    $paidMonthlyFeeNames[] = $baseFeeName;
                }
            }

            // Determine payment status based on fee coverage
            $paidFeeCount = count(array_intersect($requiredMonthlyFeeNames, $paidMonthlyFeeNames));
            $totalRequiredFees = count($requiredMonthlyFeeNames);

            if ($paidFeeCount > 0) {
                $paymentStatus = ($paidFeeCount >= $totalRequiredFees) ? 'paid' : 'partial';

                // Create StudentMonth records for each unique month
                $processedMonths = [];
                foreach ($monthlyItems as $item) {
                    $monthKey = $item->month . ', ' . $item->year;

                    // Avoid duplicates
                    if (!in_array($monthKey, $processedMonths)) {
                        $processedMonths[] = $monthKey;

                        // Check if existing record exists
                        $existingRecord = StudentMonth::where('student_id', $student->id)
                            ->where('month_key', $monthKey)
                            ->first();

                        if (!$existingRecord) {
                            echo "Creating StudentMonth for student #{$student->id} - {$monthKey} (status: {$paymentStatus})\n";

                            StudentMonth::create([
                                'student_id' => $student->id,
                                'month_key' => $monthKey,
                                'status' => $paymentStatus,
                                'payment_id' => $payment->id
                            ]);
                        } else {
                            echo "StudentMonth already exists for student #{$student->id} - {$monthKey}\n";
                        }
                    }
                }
            }
        }

        echo "\nMigration completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed as it fixes data inconsistencies
        echo "This migration cannot be reversed as it fixes data inconsistencies.\n";
    }
};
