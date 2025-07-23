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
    public function up(): void
    {
        DB::statement("
            CREATE VIEW \"view_chicks_production_ledgers\" AS
            SELECT
                cpl.\"id\" AS \"ledgerId\",
                cpl.\"hatcheryId\",
                h.\"name\" AS \"hatcheryName\",
                cpl.\"productId\",
                p.\"productName\" AS \"productName\",
                cpl.\"breedId\",
                b.\"breedName\" AS \"breedName\",
                cpl.\"transactionId\",
                cpl.\"trType\",
                cpl.\"date\",
                cpl.\"approxQty\",
                cpl.\"finalQty\",
                cpl.\"batchNo\",
                cpl.\"remarks\",
                cpl.\"appBy\",
                cpl.\"deleted_at\",
                cpl.\"created_at\",
                cpl.\"updated_at\"
            FROM \"chicks_production_ledgers\" cpl
            LEFT JOIN \"sectors\" h ON h.\"id\" = cpl.\"hatcheryId\"
            LEFT JOIN \"products\" p ON p.\"id\" = cpl.\"productId\"
            LEFT JOIN \"breeds\" b ON b.\"id\" = cpl.\"breedId\"
        ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"view_chicks_production_ledgers\"");
    }
};