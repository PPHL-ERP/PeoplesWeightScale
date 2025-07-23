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
        Schema::table('sectors', function (Blueprint $table) {
            $table->string('sectorType')->after('chicksDepotCost')->nullable();
            $table->string('inchargeName')->after('sectorType')->nullable();
            $table->string('inchargePhone')->after('inchargeName')->nullable();
            $table->string('inchargeAddress')->after('inchargePhone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sectors', function (Blueprint $table) {
            $table->dropColumn('sectorType');
            $table->dropColumn('inchargeName');
            $table->dropColumn('inchargePhone');
            $table->dropColumn('inchargeAddress');
        });
    }
};