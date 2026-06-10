<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('fee_name'); // e.g., "Monthly Tuition Fee", "Admission Fee"
            $table->string('fee_type'); // 'Monthly', 'Admission', 'Exam', 'Year', 'Other'
            $table->string('month')->nullable(); // 'January', 'February', etc. or 'Admission'
            $table->string('year')->nullable(); // '2025', '26', etc.
            $table->decimal('amount', 10, 2); // Final amount paid
            $table->decimal('original_amount', 10, 2)->nullable(); // Amount before discount
            $table->decimal('discount', 10, 2)->default(0); // Discount applied
            $table->date('due_date')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['student_id', 'month', 'year']);
            $table->index(['fee_type', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_items');
    }
};
