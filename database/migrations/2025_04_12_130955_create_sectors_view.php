<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS sectors_view");

        DB::statement("
            CREATE VIEW sectors_view AS
            SELECT
                sectors.id,
                sectors.\"companyId\",
                companies.\"nameEn\" AS companyName,
                sectors.name,
                sectors.\"isFarm\",
                sectors.\"isSalesPoint\",
                sectors.\"salesPointName\",
                sectors.\"feedDepotCost\",
                sectors.\"chicksDepotCost\",
                sectors.\"sectorType\",
                sectors.\"inchargeName\",
                sectors.\"inchargePhone\",
                sectors.\"inchargeAddress\",
                sectors.description,
                sectors.status,
                sectors.created_at,
                sectors.updated_at
            FROM sectors
            LEFT JOIN companies ON companies.id = sectors.\"companyId\"
            WHERE sectors.deleted_at IS NULL
        ");
    }


    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS sectors_view');
    }
};