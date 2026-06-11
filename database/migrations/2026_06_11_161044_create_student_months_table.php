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
        Schema::create('student_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('month_key'); // Format: "June, 26"
            $table->enum('status', ['paid', 'partial'])->nullable(); // 'paid', 'partial', or null (unpaid)
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->unique(['student_id', 'month_key']);
            $table->index(['student_id', 'status']);
        });

        // Populate student_months from existing payments
        $totalCreated = 0;

        // Get all payments
        $payments = DB::table('payments')->get();

        echo "Processing " . count($payments) . " payments to populate student_months...\n";

        foreach ($payments as $payment) {
            $studentId = $payment->student_id;

            // Get student info
            $student = DB::table('students')->where('id', $studentId)->first();
            if (!$student) {
                continue;
            }

            // Get student's required monthly fees
            $selectedFees = json_decode($student->selected_fees, true) ?? [];
            $monthlyFees = array_filter($selectedFees, function($fee) {
                return isset($fee['type']) && strtolower($fee['type']) === 'monthly';
            });
            $requiredMonthlyFeeNames = array_map(function($fee) {
                return $fee['name'];
            }, $monthlyFees);

            if (empty($requiredMonthlyFeeNames)) {
                continue; // Student has no monthly fees
            }

            // Get payment items for this payment
            $paymentItems = DB::table('payment_items')
                ->where('payment_id', $payment->id)
                ->where('student_id', $studentId)
                ->get();

            // Extract paid monthly fee names and month keys
            $paidMonthlyFeeNames = [];
            $monthKeys = [];

            foreach ($paymentItems as $item) {
                if (strtolower($item->fee_type ?? '') === 'monthly') {
                    // Extract base fee name and month key from fee_name (format: "Fee Name - Month, Year")
                    $parts = explode(' - ', $item->fee_name);
                    if (count($parts) >= 2) {
                        $baseFeeName = trim($parts[0]);
                        $monthKey = trim($parts[1]);

                        if (!empty($baseFeeName)) {
                            $paidMonthlyFeeNames[] = $baseFeeName;
                        }
                        if (!empty($monthKey)) {
                            $monthKeys[] = $monthKey;
                        }
                    }
                }
            }

            if (empty($monthKeys)) {
                continue; // No monthly fees in this payment
            }

            // Calculate payment status based on fee coverage
            $paidFeeCount = count(array_intersect($requiredMonthlyFeeNames, $paidMonthlyFeeNames));
            $totalRequiredFees = count($requiredMonthlyFeeNames);

            $paymentStatus = ($paidFeeCount >= $totalRequiredFees) ? 'paid' : 'partial';

            // Create student_months records for each month
            $uniqueMonthKeys = array_unique($monthKeys);
            foreach ($uniqueMonthKeys as $monthKey) {
                // Check if record already exists
                $exists = DB::table('student_months')
                    ->where('student_id', $studentId)
                    ->where('month_key', $monthKey)
                    ->first();

                if (!$exists) {
                    DB::table('student_months')->insert([
                        'student_id' => $studentId,
                        'month_key' => $monthKey,
                        'status' => $paymentStatus,
                        'payment_id' => $payment->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $totalCreated++;
                }
            }
        }

        echo "Populated {$totalCreated} student_months records from existing payments.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_months');
    }
};
