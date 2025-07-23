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
            CREATE OR REPLACE VIEW dealer_view AS
            SELECT
                d.\"id\" AS dealer_id,
                d.\"dealerCode\",
                d.\"dealerType\",
                d.\"tradeName\",
                d.\"tradeNameBn\",
                d.\"contactPerson\",
                d.\"address\",
                d.\"addressBn\",
                d.\"shippingAddress\",
                d.\"zoneId\",
                z.\"zoneName\" AS zone_name,
                d.\"divisionId\",
                dv.\"name\" AS division_name,
                d.\"districtId\",
                ds.\"name\" AS district_name,
                d.\"upazilaId\",
                u.\"name\" AS upazila_name,
                d.\"phone\",
                d.\"email\",
                d.\"tradeLicenseNo\",
                d.\"isDueable\",
                d.\"dueLimit\",
                d.\"referenceBy\",
                d.\"openingBalance\",
                d.\"salesPerson\",
                d.\"guarantor\",
                d.\"guarantorPerson\",
                d.\"guarantorByCheck\",
                d.\"dealerGroup\",
                d.\"crBy\",
                created_user.name AS createdByName,
                d.\"appBy\",
                approved_user.name AS appByName,
                d.\"status\",
                d.\"created_at\",
                d.\"updated_at\",
                d.\"deleted_at\"
            FROM
                \"dealers\" d
            LEFT JOIN \"zones\" z ON d.\"zoneId\" = z.\"id\"
            LEFT JOIN \"divisions\" dv ON d.\"divisionId\" = dv.\"id\"
            LEFT JOIN \"districts\" ds ON d.\"districtId\" = ds.\"id\"
            LEFT JOIN \"upazilas\" u ON d.\"upazilaId\" = u.\"id\"
            LEFT JOIN users created_user ON created_user.id = d.\"crBy\"
            LEFT JOIN users approved_user ON approved_user.id = d.\"appBy\"
        ");
    }




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS dealer_view");
   }

};
