<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unitId')->constrained('units')->onDelete('cascade');
            $table->foreignId('productId')->constrained('products')->onDelete('cascade');
            $table->foreignId('companyId')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('sales_endpoint_id')->nullable()->constrained('sales_endpoints')->onDelete('set null');
            $table->integer('adjustment_quantity');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('crBy')->nullable();
            $table->unsignedBigInteger('appBy')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('crBy')->references('id')->on('users')->onDelete('set null');
            $table->foreign('appBy')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_adjustments');
    }
}
