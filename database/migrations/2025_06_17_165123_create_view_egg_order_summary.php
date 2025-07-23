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
            CREATE VIEW view_egg_order_summary AS

            -- Order Part
            SELECT
                so.id AS sales_order_id,
                so.\"saleId\" AS order_saleId,
                so.\"bookingId\",
                so.\"saleCategoryId\",
                sc.name AS saleCategoryName,
                so.\"dealerId\",
                d.\"tradeName\" AS dealerName,
                so.\"salesPointId\",
                sp.name AS salesPointName,
                so.\"salesDraftId\",
                so.\"saleType\",
                so.\"salesPerson\",
                so.\"companyId\",
                com.\"nameEn\" AS companyName,
                so.\"chartOfHeadId\",
                ch.name AS chartOfHeadName,
                so.\"transportType\",
                so.\"outTransportInfo\",
                so.\"dueAmount\",
                so.\"totalAmount\",
                so.\"discount\",
                so.\"discountType\",
                so.\"fDiscount\",
                so.\"vat\",
                so.\"invoiceDate\",
                so.\"dueDate\",
                so.\"note\" AS order_note,
                so.\"pOverRideBy\",
                so.\"transportCost\",
                so.\"othersCost\",
                so.\"depotCost\",
                so.\"billingAddress\",
                so.\"deliveryAddress\",
                so.\"paymentStatus\",
                so.\"crBy\",
                created_user.name AS createdByName,
                so.\"appBy\",
                approved_user.name AS appByName,
                so.\"status\" AS order_status,
                so.\"deleted_at\" AS order_deleted_at,
                so.\"created_at\" AS order_created_at,
                so.\"updated_at\" AS order_updated_at,

                sod.id AS detail_id,
                sod.\"saleId\" AS detail_saleId,
                sod.\"productId\",
                p.\"productName\" AS productName,
                NULL AS basePrice,
                NULL AS sizeOrWeight,
                NULL AS minStock,
                NULL AS shortName,
                sod.\"unitId\",
                u.name AS unitName,
                sod.\"tradePrice\",
                sod.\"salePrice\",
                sod.\"qty\"::double precision AS qty,
                NULL::double precision AS rQty,
                sod.\"unitBatchNo\",
                sod.\"created_at\" AS detail_created_at,
                sod.\"updated_at\" AS detail_updated_at,
                'order' AS type

            FROM sales_orders so
            JOIN sales_order_details sod ON so.id = sod.\"saleId\"
            LEFT JOIN dealers d ON d.id = so.\"dealerId\"
            LEFT JOIN products p ON p.id = sod.\"productId\"
            LEFT JOIN units u ON u.id = sod.\"unitId\"
            LEFT JOIN sales_bookings b ON b.id = so.\"bookingId\"
            LEFT JOIN categories sc ON sc.id = so.\"saleCategoryId\"
            LEFT JOIN sectors sp ON sp.id = so.\"salesPointId\"
            LEFT JOIN sales_drafts sd ON sd.id = so.\"salesDraftId\"
            LEFT JOIN companies com ON com.id = so.\"companyId\"
            LEFT JOIN account_ledger_name ch ON ch.id = so.\"chartOfHeadId\"
            LEFT JOIN users created_user ON created_user.id = so.\"crBy\"
            LEFT JOIN users approved_user ON approved_user.id = so.\"appBy\"

            UNION ALL

            -- Return Part
            SELECT
                so.id AS sales_order_id,
                so.\"saleReturnId\" AS order_saleId,
                so.\"saleId\" AS bookingId,
                NULL AS saleCategoryId,
                NULL AS saleCategoryName,
                so.\"dealerId\",
                d.\"tradeName\" AS dealerName,
                NULL AS salesPointId,
                NULL AS salesPointName,
                NULL AS salesDraftId,
                NULL AS saleType,
                NULL AS salesPerson,
                NULL AS companyId,
                NULL AS companyName,
                NULL AS chartOfHeadId,
                NULL AS chartOfHeadName,
                NULL AS transportType,
                NULL AS outTransportInfo,
                NULL AS dueAmount,
                so.\"totalReturnAmount\" AS totalAmount,
                so.\"discount\",
                NULL AS discountType,
                NULL AS fDiscount,
                NULL AS vat,
                so.\"invoiceDate\",
                so.\"returnDate\" AS dueDate,
                so.\"note\" AS order_note,
                NULL AS pOverRideBy,
                NULL AS transportCost,
                NULL AS othersCost,
                NULL AS depotCost,
                NULL AS billingAddress,
                NULL AS deliveryAddress,
                NULL AS paymentStatus,
                so.\"crBy\",
                NULL AS createdByName,
                so.\"appBy\",
                NULL AS appByName,
                so.\"status\" AS order_status,
                so.\"deleted_at\" AS order_deleted_at,
                so.\"created_at\" AS order_created_at,
                so.\"updated_at\" AS order_updated_at,

                sod.id AS detail_id,
                sod.\"saleReturnId\" AS detail_saleId,
                sod.\"productId\",
                p.\"productName\" AS productName,
                p.\"basePrice\",
                p.\"sizeOrWeight\",
                p.\"minStock\",
                p.\"shortName\",
                sod.\"unitId\",
                u.name AS unitName,
                sod.\"tradePrice\",
                sod.\"salePrice\",
                -1 * sod.\"qty\"::double precision AS qty,
                sod.\"rQty\"::double precision AS rQty,
                NULL AS unitBatchNo,
                sod.\"created_at\" AS detail_created_at,
                sod.\"updated_at\" AS detail_updated_at,
                'return' AS type

            FROM sales_returns so
            JOIN sales_return_details sod ON so.id = sod.\"saleReturnId\"
            LEFT JOIN dealers d ON d.id = so.\"dealerId\"
            LEFT JOIN products p ON p.id = sod.\"productId\"
            LEFT JOIN units u ON u.id = sod.\"unitId\"
        ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_egg_order_summary");
    }
};