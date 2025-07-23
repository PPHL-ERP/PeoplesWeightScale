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
        Schema::table('egg_stock_adjustments', function (Blueprint $table) {
            $table->string('adjCategory')->nullable()->after('adjType');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_stock_adjustments', function (Blueprint $table) {
            $table->dropColumn('adjCategory');
        });
    }
};
