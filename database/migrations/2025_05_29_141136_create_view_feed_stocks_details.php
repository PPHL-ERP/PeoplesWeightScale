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
            CREATE VIEW view_feed_stocks_details AS
            SELECT
                fs.id AS feed_stock_id,
                fs.\"sectorId\",
                s.name AS sectorName,
                fs.\"productId\",
                p.\"productName\" AS productName,
                c.id AS categoryId,
                c.name AS categoryName,
                sc.id AS subCategoryId,
                sc.\"subCategoryName\" AS subCategoryName,
                cc.id AS childCategoryId,
                cc.\"childCategoryName\" AS childCategoryName,
                fs.closing,
                fs.\"lockQty\",
                fs.\"trDate\",
                fs.created_at,
                fs.updated_at
            FROM feed_stocks fs
            LEFT JOIN sectors s ON fs.\"sectorId\" = s.id
            LEFT JOIN products p ON fs.\"productId\" = p.id
            LEFT JOIN categories c ON p.\"categoryId\" = c.id
            LEFT JOIN sub_categories sc ON p.\"subCategoryId\" = sc.id
            LEFT JOIN child_categories cc ON p.\"childCategoryId\" = cc.id
        ");
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS sectors_view');
    }
};