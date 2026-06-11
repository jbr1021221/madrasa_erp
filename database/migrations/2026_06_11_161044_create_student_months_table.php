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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_months');
    }
};
