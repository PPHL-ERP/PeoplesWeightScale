<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW view_all_chart_of_head_transactions AS
            SELECT
                transactions.*,
                account_ledger_name.name,
                account_ledger_name.code,
                account_ledger_name.\"partyId\",
                account_ledger_name.\"partyType\",
                companies.\"nameEn\" AS companyName
            FROM
                transactions
            JOIN
                account_ledger_name
            ON
                account_ledger_name.id = transactions.\"chartOfHeadId\"
            LEFT JOIN
                companies
            ON
                companies.id = transactions.\"companyId\"
            WHERE
                transactions.\"deleted_at\" IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_all_chart_of_head_transactions");    }
};