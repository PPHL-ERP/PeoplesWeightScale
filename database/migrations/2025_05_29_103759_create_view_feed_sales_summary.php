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
            CREATE VIEW view_feed_order_summary AS

            -- Order Part
            SELECT
                fo.id AS order_id,
                fo.\"feedId\" AS order_feedId,
                fo.\"bookingId\",
                fo.\"saleCategoryId\",
                sc.name AS saleCategoryName,
                fo.\"subCategoryId\",
                ssc.\"subCategoryName\" AS subCategoryName,
                fo.\"childCategoryId\",
                cc.\"childCategoryName\" AS childCategoryName,
                fo.\"dealerId\",
                d.\"tradeName\" AS dealerName,
                fo.\"salesPointId\",
                sp.name AS salesPointName,
                fo.\"feedDraftId\",
                fo.\"saleType\",
                fo.\"salesPerson\",
                fo.\"companyId\",
                com.\"nameEn\" AS companyName,
                fo.\"chartOfHeadId\",
                ch.name AS chartOfHeadName,
                fo.\"commissionId\",
                coms.\"commissionNo\" AS commissionNo,
                fo.\"transportType\",
                fo.\"loadBy\",
                lb.\"labourName\" AS labourName,
                fo.\"isLabourBill\",
                fo.\"transportBy\",
                fo.\"outTransportInfo\",
                fo.\"subTotal\",
                fo.\"dueAmount\",
                fo.\"totalAmount\",
                fo.\"discount\",
                fo.\"discountType\",
                fo.\"fDiscount\",
                fo.\"vat\",
                fo.\"invoiceDate\",
                fo.\"dueDate\",
                fo.\"note\" AS order_note,
                fo.\"pOverRideBy\",
                fo.\"transportCost\",
                fo.\"othersCost\",
                fo.\"depotCost\",
                fo.\"billingAddress\",
                fo.\"deliveryAddress\",
                fo.\"paymentStatus\",
                fo.\"crBy\",
                created_user.name AS createdByName,
                fo.\"appBy\",
                approved_user.name AS appByName,
                fo.\"status\" AS order_status,
                fo.\"deleted_at\" AS order_deleted_at,
                fo.\"created_at\" AS order_created_at,
                fo.\"updated_at\" AS order_updated_at,

                fod.id AS detail_id,
                fod.\"feedId\" AS detail_feedId,
                fod.\"productId\",
                p.\"productName\" AS productName,
                p.\"sizeOrWeight\",
                p.\"shortName\",
                p.\"minStock\",
                p.\"basePrice\",
                fod.\"unitId\",
                u.name AS unitName,
                fod.\"tradePrice\",
                fod.\"salePrice\",
                fod.\"qty\"::double precision AS qty,
                NULL::double precision AS rQty,
                fod.\"unitBatchNo\",
                fod.\"created_at\" AS detail_created_at,
                fod.\"updated_at\" AS detail_updated_at,
                'order' AS type

            FROM feed_orders fo
            JOIN feed_order_details fod ON fo.id = fod.\"feedId\"
            LEFT JOIN dealers d ON d.id = fo.\"dealerId\"
            LEFT JOIN products p ON p.id = fod.\"productId\"
            LEFT JOIN units u ON u.id = fod.\"unitId\"
            LEFT JOIN feed_bookings b ON b.id = fo.\"bookingId\"
            LEFT JOIN categories sc ON sc.id = fo.\"saleCategoryId\"
            LEFT JOIN sub_categories ssc ON ssc.id = fo.\"subCategoryId\"
            LEFT JOIN child_categories cc ON cc.id = fo.\"childCategoryId\"
            LEFT JOIN sectors sp ON sp.id = fo.\"salesPointId\"
            LEFT JOIN feed_drafts fd ON fd.id = fo.\"feedDraftId\"
            LEFT JOIN companies com ON com.id = fo.\"companyId\"
            LEFT JOIN account_ledger_name ch ON ch.id = fo.\"chartOfHeadId\"
            LEFT JOIN commissions coms ON coms.id = fo.\"commissionId\"
            LEFT JOIN labour_infos lb ON lb.id = fo.\"loadBy\"
            LEFT JOIN users created_user ON created_user.id = fo.\"crBy\"
            LEFT JOIN users approved_user ON approved_user.id = fo.\"appBy\"

            UNION ALL

            -- Return Part
            SELECT
                fo.id AS order_id,
                fo.\"saleReturnId\" AS order_feedId,
                fo.\"saleId\" AS bookingId,
                NULL AS saleCategoryId,
                NULL AS saleCategoryName,
                NULL AS subCategoryId,
                NULL AS subCategoryName,
                NULL AS childCategoryId,
                NULL AS childCategoryName,
                fo.\"dealerId\",
                d.\"tradeName\" AS dealerName,
                NULL AS salesPointId,
                NULL AS salesPointName,
                NULL AS feedDraftId,
                NULL AS saleType,
                NULL AS salesPerson,
                NULL AS companyId,
                NULL AS companyName,
                NULL AS chartOfHeadId,
                NULL AS chartOfHeadName,
                NULL AS commissionId,
                NULL AS commissionNo,
                NULL AS transportType,
                NULL AS loadBy,
                NULL AS labourName,
                NULL AS isLabourBill,
                NULL AS transportBy,
                NULL AS outTransportInfo,
                NULL AS subTotal,
                NULL AS dueAmount,
                fo.\"totalReturnAmount\" AS totalAmount,
                fo.\"discount\",
                NULL AS discountType,
                NULL AS fDiscount,
                NULL AS vat,
                fo.\"invoiceDate\",
                fo.\"returnDate\" AS dueDate,
                fo.\"note\" AS order_note,
                NULL AS pOverRideBy,
                NULL AS transportCost,
                NULL AS othersCost,
                NULL AS depotCost,
                NULL AS billingAddress,
                NULL AS deliveryAddress,
                NULL AS paymentStatus,
                fo.\"crBy\",
                NULL AS createdByName,
                fo.\"appBy\",
                NULL AS appByName,
                fo.\"status\" AS order_status,
                fo.\"deleted_at\" AS order_deleted_at,
                fo.\"created_at\" AS order_created_at,
                fo.\"updated_at\" AS order_updated_at,

                fod.id AS detail_id,
                fod.\"saleReturnId\" AS detail_feedId,
                fod.\"productId\",
                p.\"productName\" AS productName,
                NULL AS \"sizeOrWeight\",
                NULL AS \"shortName\",
                NULL AS \"minStock\",
                NULL AS \"basePrice\",


                fod.\"unitId\",
                u.name AS unitName,
                fod.\"tradePrice\",
                fod.\"salePrice\",
                -1 * fod.\"qty\"::double precision AS qty,
                fod.\"rQty\"::double precision AS rQty,
                NULL AS unitBatchNo,
                fod.\"created_at\" AS detail_created_at,
                fod.\"updated_at\" AS detail_updated_at,
                'return' AS type

            FROM feed_sales_returns fo
            JOIN feed_sales_return_details fod ON fo.id = fod.\"saleReturnId\"
            LEFT JOIN dealers d ON d.id = fo.\"dealerId\"
            LEFT JOIN products p ON p.id = fod.\"productId\"
            LEFT JOIN units u ON u.id = fod.\"unitId\"
        ");
    }







    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_feed_sales_summary");
    }
};
