<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW view_chicks_stocks AS
            SELECT
                cs.id AS chicks_stock_id,
                cs.\"sectorId\",
                s.name AS sectorName,
                cs.\"productId\",
                p.\"productName\" AS productName,
                cs.\"breedId\",
                b.\"breedName\" AS breedName,
                cs.\"stockType\",
                cs.\"batchNo\",
                cs.\"stockDate\",
                cs.\"approxQty\",
                cs.\"finalQty\",
                cs.closing,
                -- Closing Balances
                SUM(cs.closing) OVER (PARTITION BY cs.\"sectorId\") AS sector_closing_balance,
                SUM(cs.closing) OVER (PARTITION BY cs.\"productId\") AS product_closing_balance,
                SUM(cs.closing) OVER (PARTITION BY cs.\"batchNo\") AS batch_closing_balance,
                SUM(cs.closing) OVER (PARTITION BY cs.\"breedId\") AS breed_closing_balance,
                cs.created_at,
                cs.updated_at
            FROM chicks_stocks cs
            LEFT JOIN sectors s ON s.id = cs.\"sectorId\"
            LEFT JOIN products p ON p.id = cs.\"productId\"
            LEFT JOIN breeds b ON b.id = cs.\"breedId\"
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_chicks_stocks");
    }
};
