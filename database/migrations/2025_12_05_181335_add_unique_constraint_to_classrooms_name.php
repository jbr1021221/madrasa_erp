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
        // First, handle existing duplicate class names by making them unique
        $duplicates = DB::table('classrooms')
            ->select('name', DB::raw('COUNT(*) as count'))
            ->groupBy('name')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $classrooms = DB::table('classrooms')
                ->where('name', $duplicate->name)
                ->orderBy('id')
                ->get();

            // Keep the first one, rename the rest
            foreach ($classrooms->skip(1) as $index => $classroom) {
                $newName = $classroom->name . ' (' . ($index + 2) . ')';
                DB::table('classrooms')
                    ->where('id', $classroom->id)
                    ->update(['name' => $newName]);
            }
        }

        // Now add the unique constraint
        Schema::table('classrooms', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
