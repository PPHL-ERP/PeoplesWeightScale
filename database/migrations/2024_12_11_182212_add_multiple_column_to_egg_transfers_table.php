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
        Schema::table('egg_transfers', function (Blueprint $table) {
          $table->string('labourGroupId')->nullable()->after('loadBy');
          $table->double('labourBill', 15, 2)->nullable()->after('labourGroupId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egg_transfers', function (Blueprint $table) {
            $table->dropColumn('labourGroupId');
            $table->dropColumn('labourBill');
         });
    }
};