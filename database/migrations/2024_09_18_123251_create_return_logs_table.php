<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnLogsTable extends Migration
{
    public function up()
    {
        Schema::create('return_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_log_id');
            $table->unsignedBigInteger('productId');
            $table->unsignedBigInteger('companyId')->nullable();
            $table->unsignedBigInteger('sales_endpoint_id')->nullable();
            $table->integer('quantity_returned');
            $table->string('reason')->nullable();
            $table->date('return_date');
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('crBy')->nullable(); // Created by
            $table->unsignedBigInteger('appBy')->nullable(); // Approved by
            $table->softDeletes();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('sales_log_id')->references('id')->on('sales_logs')->onDelete('cascade');
            $table->foreign('productId')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('companyId')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('sales_endpoint_id')->references('id')->on('sales_endpoints')->onDelete('set null');
            $table->foreign('crBy')->references('id')->on('users')->onDelete('set null');
            $table->foreign('appBy')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('return_logs');
    }
}
