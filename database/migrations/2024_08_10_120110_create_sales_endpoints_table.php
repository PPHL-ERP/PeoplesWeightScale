<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesEndpointsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('companyId')->nullable();
            $table->unsignedBigInteger('crBy')->nullable();
            $table->unsignedBigInteger('appBy')->nullable();
            $table->softDeletes();
            $table->timestamps();
            // Foreign Key Constraints
            $table->foreign('companyId')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('crBy')->references('id')->on('users')->onDelete('set null');
            $table->foreign('appBy')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_endpoints');
    }
}