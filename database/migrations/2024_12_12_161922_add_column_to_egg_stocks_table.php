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
        Schema::table('egg_stocks', function (Blueprint $table) {
            $table->double('lockQty')->default(0)->after('closing');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_stocks', function (Blueprint $table) {
            $table->dropColumn('lockQty');
        });
    }
};