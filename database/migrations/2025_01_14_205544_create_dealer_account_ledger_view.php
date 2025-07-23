<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW dealer_account_ledger_view AS
            SELECT 
                dealers.id AS \"dealerId\",
                dealers.\"tradeName\",
                dealers.\"dealerCode\",
                dealers.\"dealerType\",
                dealers.\"zoneId\",
                dealers.\"dealerGroup\",
                account_ledger_name.current_balance,
                account_ledger_name.id AS \"chartOfHeadId\"
            FROM 
                dealers
            JOIN 
                account_ledger_name 
            ON 
                account_ledger_name.\"partyId\" = dealers.id
            WHERE 
                account_ledger_name.\"partyType\" = 'D';
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS dealer_account_ledger_view");
    }
};
