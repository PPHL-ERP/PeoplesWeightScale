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
        Schema::table('egg_receives', function (Blueprint $table) {
            $table->longText('remarks')->nullable()->after('unLoadBy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_receives', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
