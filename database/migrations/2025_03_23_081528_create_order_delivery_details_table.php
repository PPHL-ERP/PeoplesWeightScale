<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDeliveryDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('order_delivery_details', function (Blueprint $table) {
            $table->id();
            // Using string for sale_id since saleId might be alphanumeric (e.g., "ESO25020001")
            $table->string('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty');
            $table->string('dealer_code');
            $table->string('driver_name');
            $table->string('driver_phone');
            $table->string('vehicle_no');
            $table->string('vehicle_type');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_delivery_details');
    }
}
