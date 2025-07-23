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
        Schema::table('feed_orders', function (Blueprint $table) {
            $table->string('loadBy' )->after('transportType')->nullable();
            $table->string('transportBy')->after('loadBy')->nullable();
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_orders', function (Blueprint $table) {
            $table->dropColumn('loadBy');
            $table->dropColumn('transportBy');
        });
    }
};