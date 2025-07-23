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
        Schema::table('feed_transfers', function (Blueprint $table) {
            $table->string('isLabourBill')->after('labourBill')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_transfers', function (Blueprint $table) {
            $table->dropColumn('isLabourBill');
        });
    }
};