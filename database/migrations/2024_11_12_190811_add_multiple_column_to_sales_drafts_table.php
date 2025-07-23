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
        Schema::table('sales_drafts', function (Blueprint $table) {
            $table->bigInteger('companyId')->nullable()->index()->after('salesDraftId');
            $table->double('transportCost')->nullable()->after('pOverRideBy');
            $table->json('othersCost')->nullable()->after('transportCost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_drafts', function (Blueprint $table) {
            $table->dropColumn('companyId');
            $table->dropColumn('transportCost');
            $table->dropColumn('othersCost');
        });
    }
};