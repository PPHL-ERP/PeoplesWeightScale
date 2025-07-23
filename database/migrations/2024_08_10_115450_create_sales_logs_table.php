<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesLogsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_endpoint_id')->constrained('sales_endpoints')->onDelete('cascade');
            $table->foreignId('productId')->constrained('products')->onDelete('cascade');
            $table->foreignId('companyId')->nullable()->constrained('companies')->onDelete('set null');
            $table->string('customer_name');
            $table->integer('quantity');
            $table->date('sale_date');
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('sales_logs');
    }
}
