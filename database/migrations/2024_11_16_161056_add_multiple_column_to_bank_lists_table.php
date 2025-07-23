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
        Schema::table('bank_lists', function (Blueprint $table) {
            $table->string('contactNo')->nullable()->index()->after('isMobileBanking');
            $table->string('bankAddress')->nullable()->after('contactNo');
            $table->double('openingBalance')->nullable()->after('bankAddress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_lists', function (Blueprint $table) {
            $table->dropColumn('contactNo');
            $table->dropColumn('bankAddress');
            $table->dropColumn('openingBalance');
       });
    }
};
