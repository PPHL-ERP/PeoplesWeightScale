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
        Schema::table('flocks', function (Blueprint $table) {
            $table->string('flockType')->nullable()->after('flockName');
            $table->bigInteger('sectorId')->nullable()->index()->after('flockType');
            $table->date('flockStartDate')->nullable()->after('sectorId');
            $table->text('note')->nullable()->after('flockStartDate');
            $table->string('status')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flocks', function (Blueprint $table) {
            $table->dropColumn('flockType');
            $table->dropColumn('sectorId');
            $table->dropColumn('flockStartDate');
            $table->dropColumn('note');
            $table->dropColumn('status');
        });
    }
};