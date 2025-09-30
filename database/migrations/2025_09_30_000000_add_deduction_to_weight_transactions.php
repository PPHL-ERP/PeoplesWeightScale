<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('weight_transactions', 'deduction')) {
            Schema::table('weight_transactions', function (Blueprint $table) {
                $table->decimal('deduction', 12, 2)->nullable()->after('discount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('weight_transactions', 'deduction')) {
            Schema::table('weight_transactions', function (Blueprint $table) {
                $table->dropColumn('deduction');
            });
        }
    }
};
