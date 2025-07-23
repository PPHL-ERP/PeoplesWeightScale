<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW chartof_account_view AS
            SELECT 
                account_ledger_name.*,
                account_sub_groups.name AS \"subGroupName\",
                account_groups.name AS \"groupName\",
                account_class.name AS \"className\"
            FROM 
                account_ledger_name
            JOIN 
                account_sub_groups 
            ON 
                account_sub_groups.id = account_ledger_name.\"subGroupId\"
            JOIN
                account_groups
            ON
                account_groups.id = account_ledger_name.\"groupId\"
            JOIN
                account_class
            ON
                account_class.id = account_groups.\"classId\"
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS chartof_account_view");
    }
};
