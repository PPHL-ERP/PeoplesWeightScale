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
            CREATE VIEW view_egg_sale_return AS
            SELECT
                esr.\"id\" AS return_id,
                esr.\"saleReturnId\",
                esr.\"saleId\",
                s.\"saleId\" AS egg_order_code,
                s.\"salesPointId\",
                sp.\"name\" AS salesPointName,
                s.\"salesPerson\",
                s.\"totalAmount\",
                s.\"dueAmount\",
                s.\"transportCost\",
                s.\"paymentStatus\",

                esr.\"dealerId\",
                d.\"tradeName\" AS dealerName,
                esr.\"returnPurpose\",
                esr.\"invoiceDate\",
                esr.\"returnDate\",
                esr.\"totalReturnAmount\",
                esr.\"discount\",
                esr.\"note\" AS return_note,
                esr.\"crBy\",
                esr.\"appBy\",
                esr.\"status\",

                esrd.\"productId\",
                p.\"productName\" AS productName,
                p.\"basePrice\",
                p.\"sizeOrWeight\",
                p.\"minStock\",
                p.\"shortName\",

                esrd.\"unitId\",
                u.\"name\" AS unitName,
                esrd.\"tradePrice\",
                esrd.\"salePrice\",
                esrd.\"qty\",
                esrd.\"rQty\",
                esrd.\"note\" AS detail_note,

                esr.\"created_at\",
                esr.\"updated_at\"

            FROM \"sales_returns\" esr
            LEFT JOIN \"sales_return_details\" esrd ON esrd.\"saleReturnId\" = esr.\"id\"
            LEFT JOIN \"products\" p ON p.\"id\" = esrd.\"productId\"
            LEFT JOIN \"units\" u ON u.\"id\" = esrd.\"unitId\"
            LEFT JOIN \"dealers\" d ON d.\"id\" = esr.\"dealerId\"
            LEFT JOIN \"sales_orders\" s ON s.\"id\" = esr.\"saleId\"
            LEFT JOIN \"sectors\" sp ON sp.\"id\" = s.\"salesPointId\"

        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_egg_sale_return');
    }
};