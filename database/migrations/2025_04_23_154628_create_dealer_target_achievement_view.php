<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateDealerTargetAchievementView extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW dealer_target_achievement_view AS
            SELECT
                fo.\"dealerId\",
                fod.\"productId\",
                EXTRACT(YEAR FROM fo.\"invoiceDate\")::INT AS year,
                EXTRACT(MONTH FROM fo.\"invoiceDate\")::INT AS month,
                SUM(fod.qty::numeric) AS achieved_qty
            FROM feed_order_details fod
            JOIN feed_orders fo ON fo.id = fod.\"feedId\"
            WHERE fo.status = 'approved'
            GROUP BY fo.\"dealerId\", fod.\"productId\", EXTRACT(YEAR FROM fo.\"invoiceDate\"), EXTRACT(MONTH FROM fo.\"invoiceDate\")
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS dealer_target_achievement_view');
    }
};
