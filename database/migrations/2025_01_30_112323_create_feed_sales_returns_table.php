<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feed_sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('saleReturnId')->nullable();
            $table->bigInteger('saleId')->nullable()->index();
            $table->bigInteger('dealerId')->nullable()->index();
            $table->string('returnPurpose')->nullable();
            $table->date('invoiceDate')->nullable()->index();
            $table->date('returnDate')->nullable()->index();
            $table->double('totalReturnAmount')->nullable();
            $table->double('discount')->nullable();
            $table->longText('note')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_sales_returns');
    }
};