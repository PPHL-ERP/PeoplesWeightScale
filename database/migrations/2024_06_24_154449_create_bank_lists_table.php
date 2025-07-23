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
        Schema::create('bank_lists', function (Blueprint $table) {
            $table->id();
            $table->string('bankName')->nullable();
            $table->string('bankBranch')->nullable();
            $table->string('accountHolder')->nullable();
            $table->string('accountNo')->nullable();
            $table->string('routingNo')->nullable();
            $table->string('isMobileBanking')->nullable();
            $table->string('crBy')->nullable();
            $table->longText('note')->nullable();
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
        Schema::dropIfExists('bank_lists');
    }
};
