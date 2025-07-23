<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW view_bank_transactions AS
            SELECT 
                transactions.*,
                account_ledger_name.name AS bankName,
                account_ledger_name.code AS bankCode,
                account_ledger_name.current_balance AS currentBalance,
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
                account_ledger_name.\"partyType\" = 'B'
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS view_bank_transactions");
    }
};
