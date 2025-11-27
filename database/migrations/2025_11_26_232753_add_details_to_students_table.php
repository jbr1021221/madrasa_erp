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
        Schema::table('students', function (Blueprint $table) {
            // Student Details
            $table->date('dob')->nullable();
            $table->string('gender', 10)->nullable();
            $table->integer('siblings_count')->nullable();
            $table->integer('birth_order')->nullable();
            $table->string('last_school')->nullable();
            $table->string('present_district')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('permanent_district')->nullable();
            $table->string('blood_group', 5)->nullable();

            // Guardian Details
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_nationality')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_nid', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'dob', 'gender', 'siblings_count', 'birth_order', 'last_school',
                'present_district', 'permanent_address', 'permanent_district', 'blood_group',
                'guardian_occupation', 'guardian_nationality', 'guardian_phone',
                'guardian_email', 'guardian_nid'
            ]);
        });
    }
};
