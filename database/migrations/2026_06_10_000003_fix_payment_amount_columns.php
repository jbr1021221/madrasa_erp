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
        // Step 1: Add final_amount column if it doesn't exist
        if (!Schema::hasColumn('payments', 'final_amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('final_amount', 10, 2)->nullable()->after('amount');
            });
        }

        // Step 2: Rename discount to total_discount if discount exists but total_discount doesn't
        if (Schema::hasColumn('payments', 'discount') && !Schema::hasColumn('payments', 'total_discount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('discount', 'total_discount');
            });
        } elseif (!Schema::hasColumn('payments', 'total_discount')) {
            // If neither exists, add total_discount
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('total_discount', 10, 2)->default(0)->after('sub_total');
            });
        }

        // Step 3: Migrate data - final_amount should be amount for existing records
        // For records that already have data, calculate final_amount = sub_total - total_discount
        // Or use the existing amount column
        DB::statement('UPDATE payments SET final_amount = COALESCE(sub_total - total_discount, amount) WHERE final_amount IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename total_discount back to discount
        if (Schema::hasColumn('payments', 'total_discount') && !Schema::hasColumn('payments', 'discount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('total_discount', 'discount');
            });
        }

        // Drop final_amount
        if (Schema::hasColumn('payments', 'final_amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('final_amount');
            });
        }
    }
};
