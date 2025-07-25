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
        Schema::table('account_ledger_name', function (Blueprint $table) {
            $table->string('partyId')->nullable()->after('description');
            $table->string('partyType')->nullable()->after('partyId');
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_ledger_name', function (Blueprint $table) {
            $table->dropColumn('partyId');
            $table->dropColumn('partyType');
        });
    }
};
