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
        Schema::create('sms', function (Blueprint $table) {
            $table->id();

            // API & Gateway Info
            $table->string('apiKey')->nullable();
            $table->string('gatewayName')->nullable();

            // Sender IDs
            $table->string('mSenderId')->nullable();       // main sender id
            $table->integer('nmSenderId')->nullable();     // numeric sender id

            // Message meta
            $table->string('language')->nullable();        // e.g. EN, BN
            $table->string('type')->nullable();            // e.g. OTP, Promo
            $table->string('url')->nullable();

            // Headers & Footers
            $table->string('headerTxtEn')->nullable();
            $table->string('headerTxtBn')->nullable();
            $table->string('footerTxtEn')->nullable();
            $table->string('footerTxtBn')->nullable();

            // Status
            $table->string('status')->nullable();          // Active / Inactive

            $table->softDeletes(); // deleted_at
            $table->timestamps();  // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms');
    }
};
