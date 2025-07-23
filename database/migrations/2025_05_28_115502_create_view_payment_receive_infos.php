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
            CREATE VIEW view_payment_receive_infos AS
            SELECT
                pri.id,
                pri.\"voucherNo\",
                pri.\"companyId\",
                c.\"nameEn\" AS companyName,
                pri.\"recType\",
                pri.\"chartOfHeadId\",
                coa.\"tradeName\" AS chartOfHeadName,
                pri.amount,
                pri.\"recDate\",
                pri.\"paymentType\",
                bank.\"bankName\" AS bankName,
                pri.\"paymentMode\",
                pri.\"paymentFor\",
                pt.name AS paymentForName,
                pri.\"invoiceType\",
                pri.\"checkNo\",
                pri.\"checkDate\",
                pri.\"trxId\",
                pri.ref,
                pri.status,
                pri.\"createdBy\",
                created_user.name AS createdByName,
                pri.\"modifiedBy\",
                pri.\"deletedBy\",
                pri.note,
                pri.\"appBy\",
                approved_user.name AS appByName,
                pri.created_at,
                pri.updated_at,
                pri.deleted_at
            FROM payment_receive_infos pri
            LEFT JOIN companies c ON c.id = pri.\"companyId\"
            LEFT JOIN dealers coa ON coa.id = pri.\"chartOfHeadId\"
            LEFT JOIN users created_user ON created_user.id = pri.\"createdBy\"
            LEFT JOIN users approved_user ON approved_user.id = pri.\"appBy\"
            LEFT JOIN bank_lists bank ON bank.id = pri.\"paymentType\"
            LEFT JOIN payment_types pt ON pt.id = pri.\"paymentFor\"
        ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_payment_receive_infos");
    }
};