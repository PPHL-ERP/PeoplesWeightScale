<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('transport_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('origin_sales_endpoint_id');
            $table->unsignedBigInteger('destination_sales_endpoint_id');
            $table->unsignedBigInteger('productId');
            $table->unsignedBigInteger('companyId')->nullable();
            $table->integer('quantity');
            $table->string('status')->default('Pending'); // Possible values: Pending, In Transit, Delivered, Cancelled
            $table->date('dispatch_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('crBy')->nullable(); // Created by
            $table->unsignedBigInteger('appBy')->nullable(); // Approved by
            $table->softDeletes();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('origin_sales_endpoint_id')->references('id')->on('sales_endpoints')->onDelete('cascade');
            $table->foreign('destination_sales_endpoint_id')->references('id')->on('sales_endpoints')->onDelete('cascade');
            $table->foreign('productId')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('companyId')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('crBy')->references('id')->on('users')->onDelete('set null');
            $table->foreign('appBy')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_orders');
    }
}
