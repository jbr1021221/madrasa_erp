<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration does 3 things in one file:
     * 1. Adds base_fee_name column
     * 2. Backfills existing records
     * 3. Makes base_fee_name NOT NULL
     */
    public function up(): void
    {
        // Step 1: Add base_fee_name column (nullable initially)
        Schema::table('payment_items', function (Blueprint $table) {
            $table->string('base_fee_name')->nullable()->after('fee_name');
            $table->index('base_fee_name');
        });

        echo "Step 1: Added base_fee_name column\n";

        // Step 2: Backfill existing records
        // For Monthly fees: extract base name from fee_name
        $monthlyItems = PaymentItem::whereNull('base_fee_name')
            ->where('fee_type', 'Monthly')
            ->get();

        echo "Step 2: Backfilling " . $monthlyItems->count() . " Monthly payment items\n";

        foreach ($monthlyItems as $item) {
            $baseFeeName = preg_replace('/\s*-\s*[A-Za-z]+,\s*\d+$/', '', $item->fee_name);
            if (!empty($baseFeeName)) {
                $item->update(['base_fee_name' => $baseFeeName]);
            }
        }

        // For non-monthly fees: base_fee_name = fee_name
        $otherItems = PaymentItem::whereNull('base_fee_name')
            ->where('fee_type', '!=', 'Monthly')
            ->get();

        echo "Step 2: Backfilling " . $otherItems->count() . " non-Monthly payment items\n";

        foreach ($otherItems as $item) {
            $item->update(['base_fee_name' => $item->fee_name]);
        }

        // Step 3: Make base_fee_name NOT NULL
        Schema::table('payment_items', function (Blueprint $table) {
            $table->string('base_fee_name')->nullable(false)->change();
        });

        echo "Step 3: Made base_fee_name NOT NULL\n";
        echo "Migration completed successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->dropIndex(['base_fee_name']);
            $table->dropColumn('base_fee_name');
        });
    }
};
