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
            CREATE VIEW view_feed_sale_return AS
            SELECT
                fsr.\"id\" AS return_id,
                fsr.\"saleReturnId\",
                fsr.\"saleId\",
                s.\"feedId\" AS feed_order_code,
                s.\"salesPointId\",
                sp.\"name\" AS salesPointName,
                s.\"commissionId\",
                coms.\"commissionNo\" AS commissionNo,
                s.\"salesPerson\",
                s.\"totalAmount\",
                s.\"dueAmount\",
                s.\"transportCost\",
                s.\"loadBy\",
                l.\"labourName\" AS loadByName,
                s.\"paymentStatus\",

                fsr.\"dealerId\",
                d.\"tradeName\" AS dealerName,
                fsr.\"returnPurpose\",
                fsr.\"invoiceDate\",
                fsr.\"returnDate\",
                fsr.\"totalReturnAmount\",
                fsr.\"discount\",
                fsr.\"note\" AS return_note,
                fsr.\"crBy\",
                fsr.\"appBy\",
                fsr.\"status\",

                fsrd.\"productId\",
                p.\"productName\" AS productName,
                p.\"basePrice\",
                p.\"sizeOrWeight\",
                p.\"minStock\",
                p.\"shortName\",

                fsrd.\"unitId\",
                u.\"name\" AS unitName,
                fsrd.\"tradePrice\",
                fsrd.\"salePrice\",
                fsrd.\"qty\",
                fsrd.\"rQty\",
                fsrd.\"note\" AS detail_note,

                fsr.\"created_at\",
                fsr.\"updated_at\"

            FROM \"feed_sales_returns\" fsr
            LEFT JOIN \"feed_sales_return_details\" fsrd ON fsrd.\"saleReturnId\" = fsr.\"id\"
            LEFT JOIN \"products\" p ON p.\"id\" = fsrd.\"productId\"
            LEFT JOIN \"units\" u ON u.\"id\" = fsrd.\"unitId\"
            LEFT JOIN \"dealers\" d ON d.\"id\" = fsr.\"dealerId\"
            LEFT JOIN \"feed_orders\" s ON s.\"id\" = fsr.\"saleId\"
            LEFT JOIN \"sectors\" sp ON sp.\"id\" = s.\"salesPointId\"
            LEFT JOIN \"labour_infos\" l ON l.\"id\" = s.\"loadBy\"
            LEFT JOIN \"commissions\" coms ON coms.\"id\" = s.\"commissionId\"
        ");
    }






    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_feed_sale_return");
    }
};