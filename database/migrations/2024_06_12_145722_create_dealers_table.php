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
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('dealerCode')->nullable();
            $table->string('dealerType')->nullable();
            $table->string('tradeName');
            $table->string('tradeNameBn')->nullable();
            $table->string('contactPerson');
            $table->string('address')->nullable();
            $table->string('addressBn')->nullable();
            $table->string('shippingAddress')->nullable();
            $table->bigInteger('zoneId')->nullable()->index();
            $table->bigInteger('divisionId')->nullable();
            $table->bigInteger('districtId')->nullable()->index();
            $table->bigInteger('upazilaId')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('tradeLicenseNo')->nullable();
            $table->boolean('isDueable')->default(false);
            $table->decimal('dueLimit', 10, 2)->nullable();
            $table->string('referenceBy')->nullable();
            $table->string('guarantor')->nullable();
            $table->string('guarantorPerson')->nullable();
            $table->json('guarantorByCheck')->nullable();
            $table->string('dealerGroup')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string('status')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealers');
    }
};