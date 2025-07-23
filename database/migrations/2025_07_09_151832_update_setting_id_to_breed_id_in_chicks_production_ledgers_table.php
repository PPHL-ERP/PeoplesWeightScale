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
        Schema::table('chicks_production_ledgers', function (Blueprint $table) {
            // Drop old column
            $table->dropColumn('settingId');

            // Add new column with bigInteger type
            $table->bigInteger('breedId')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chicks_production_ledgers', function (Blueprint $table) {
         // Rollback: Drop breedId and restore settingId
            $table->dropColumn('breedId');
            $table->string('settingId')->nullable();
        });
    }
};
