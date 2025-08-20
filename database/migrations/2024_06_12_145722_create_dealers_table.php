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

            // Dealer Info
            $table->string('dealerCode')->nullable()->unique();
            $table->string('dealerType')->nullable();
            $table->string('tradeName');
            $table->string('tradeNameBn')->nullable();
            $table->string('contactPerson');

            // Addresses
            $table->string('address')->nullable();
            $table->string('addressBn')->nullable();
            $table->string('shippingAddress')->nullable();

            // Location Relations (unsigned for MySQL FKs)
            $table->unsignedBigInteger('zoneId')->nullable()->index();
            $table->unsignedBigInteger('divisionId')->nullable();
            $table->unsignedBigInteger('districtId')->nullable()->index();
            $table->unsignedBigInteger('upazilaId')->nullable();

            // Contact
            $table->string('phone')->unique();
            $table->string('email')->nullable();

            // Licensing / Legal
            $table->string('tradeLicenseNo')->nullable();

            // Financial
            $table->boolean('isDueable')->default(false);
            $table->decimal('dueLimit', 10, 2)->nullable();

            // Guarantor Info
            $table->string('referenceBy')->nullable();
            $table->string('guarantor')->nullable();
            $table->string('guarantorPerson')->nullable();
            $table->json('guarantorByCheck')->nullable();

            // Group
            $table->string('dealerGroup')->nullable();

            // Audit
            $table->unsignedBigInteger('crBy')->nullable();
            $table->unsignedBigInteger('appBy')->nullable();
            $table->string('status')->nullable();

            // Timestamps
            $table->softDeletes();   // In MySQL, use softDeletes() not softDeletesTz()
            $table->timestamps();    // In MySQL, timestampsTz() is not true TZ-aware
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
