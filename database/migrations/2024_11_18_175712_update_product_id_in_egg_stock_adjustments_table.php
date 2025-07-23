<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductIdInEggStockAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('egg_stock_adjustments', function (Blueprint $table) {
            // Change productId column to string type
            $table->string('productId')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('egg_stock_adjustments', function (Blueprint $table) {
            // Revert productId column back to bigint (if needed)
            $table->bigInteger('productId')->change();
        });
    }
}
