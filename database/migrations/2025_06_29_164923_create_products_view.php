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
        CREATE VIEW products_view AS
        SELECT
            p.id,
            p.\"productId\",
            p.\"productName\",
            p.\"productType\",
            p.sn,
            p.\"qrCode\",
            p.\"batchNo\",
            p.\"companyId\",
            c.\"nameEn\" AS companyName,
            p.\"categoryId\",
            cat.\"name\" AS categoryName,
            p.\"subCategoryId\",
            scat.\"subCategoryName\" AS subCategoryName,
            p.\"childCategoryId\",
            ccat.\"childCategoryName\" AS childCategoryName,
            p.\"unitId\",
            u.\"name\" AS unitName,
            p.\"image\",
            p.\"basePrice\",
            p.\"sizeOrWeight\",
            p.\"shortName\",
            p.\"productForm\",
            p.\"warranty\",
            p.\"minStock\",
            p.\"description\",
            p.\"crBy\",
            p.\"appBy\",
            p.\"status\",
            p.created_at,
            p.updated_at
        FROM products p
        LEFT JOIN companies c ON c.id = p.\"companyId\"
        LEFT JOIN categories cat ON cat.id = p.\"categoryId\"
        LEFT JOIN sub_categories scat ON scat.id = p.\"subCategoryId\"
        LEFT JOIN child_categories ccat ON ccat.id = p.\"childCategoryId\"
        LEFT JOIN units u ON u.id = p.\"unitId\"
        WHERE p.deleted_at IS NULL
    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS products_view");
    }
};
