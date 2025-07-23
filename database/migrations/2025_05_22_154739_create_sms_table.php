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
            $table->string('apiKey')->nullable();
            $table->string('gatewayName')->nullable();
            $table->string('mSenderId')->nullable();
            $table->integer('nmSenderId')->nullable();
            $table->string('language')->nullable();
            $table->string('type')->nullable();
            $table->string('url')->nullable();
            $table->string('headerTxtEn')->nullable();
            $table->string('headerTxtBn')->nullable();
            $table->string('footerTxtEn')->nullable();
            $table->string('footerTxtBn')->nullable();
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
        Schema::dropIfExists('sms');
    }
};
