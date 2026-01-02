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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('name');
            $table->string('father_name');
            $table->string('mother_name');
            $table->text('address');
            $table->string('mobile', 20);
            $table->string('alt_mobile', 20)->nullable();
            $table->string('nid_file_path')->nullable();
            $table->foreignId('class_id')->constrained('classrooms')->onDelete('cascade');
            $table->string('section', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
