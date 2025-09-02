<?php

// database/migrations/2025_09_02_000001_add_detractionqty_to_weight_transactions.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('weight_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('weight_transactions', 'detractionQty')) {
                $table->decimal('detractionQty', 12, 2)
                      ->nullable()
                      ->after('gross_weight');
            }
        });
    }

    public function down(): void
    {
        Schema::table('weight_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('weight_transactions', 'detractionQty')) {
                $table->dropColumn('detractionQty');
            }
        });
    }
};

