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
            // Add columns for multi-company support
            $table->bigInteger('company_id')->nullable()->index()->after('id');

            // Add account classification columns
            $table->bigInteger('account_type')->nullable()->after('subGroupId');
            $table->enum('nature', ['Debit', 'Credit'])->nullable()->after('account_type');

            // Add balance tracking columns
            $table->decimal('opening_balance', 15, 2)->default(0)->after('nature');
            $table->decimal('current_balance', 15, 2)->default(0)->after('opening_balance');

            // Add status flags
            $table->boolean('is_active')->default(true)->after('description');
            $table->boolean('is_posting_allowed')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_ledger_name', function (Blueprint $table) {
            // Drop the added columns
            $table->dropColumn('company_id');
            $table->dropColumn('account_type');
            $table->dropColumn('nature');
            $table->dropColumn('opening_balance');
            $table->dropColumn('current_balance');
            $table->dropColumn('is_active');
            $table->dropColumn('is_posting_allowed');
        });
    }
};
