<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    //view_dealer_wise_transactions
    
    public function up()
{
    DB::statement("
        CREATE VIEW view_dealer_wise_transactions AS
        SELECT 
            transactions.*,
            account_ledger_name.name,
            account_ledger_name.code,
            account_ledger_name.\"partyId\",
            companies.\"nameEn\"
        FROM 
            transactions
        JOIN 
            account_ledger_name 
        ON 
            account_ledger_name.id = transactions.\"chartOfHeadId\"
        JOIN 
            companies 
        ON 
            companies.id = transactions.\"companyId\"
        WHERE 
            account_ledger_name.\"partyType\" = 'D'
    ");
}


    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS view_dealer_wise_transactions");
    }
};
