<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('productId')->nullable();
            $table->string('productName')->nullable();
            $table->string('productType')->nullable();
            $table->bigInteger('sn')->nullable();
            $table->string('qrCode')->nullable();
            $table->string('batchNo')->nullable();
            $table->bigInteger('companyId')->nullable()->index();
            $table->bigInteger('categoryId')->nullable()->index();
            $table->bigInteger('subCategoryId')->nullable()->index();
            $table->bigInteger('childCategoryId')->nullable()->index();
            $table->bigInteger('unitId')->nullable();
            $table->string('image')->nullable();
            $table->decimal('basePrice', 10, 2)->nullable();
            $table->string('productForm')->nullable();
            $table->string('warranty')->nullable();
            $table->string('minStock')->nullable();
            $table->longText('description')->nullable();
            $table->string('crBy')->nullable();
            $table->string('appBy')->nullable();
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
        Schema::dropIfExists('products');
    }
}