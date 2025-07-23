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
        Schema::create('feed_commission_info', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('saleId')->nullable()->index();
            $table->bigInteger('CommissionId')->nullable();
            $table->bigInteger('dealerId')->nullable()->index();
            $table->date(column: 'saleDate')->index();
            $table->bigInteger('saleReturnId')->nullable();
            $table->date(column: 'saleReturnDate')->index();
            $table->bigInteger('productId')->nullable()->index();
            $table->string('productType')->nullable();
            $table->string('pQty')->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->double('salePrice')->nullable();
            $table->double('comAmount')->nullable();
            $table->string('others')->nullable();
            $table->string('trType')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_commission_info');
    }
};
