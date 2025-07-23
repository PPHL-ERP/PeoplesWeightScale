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
        Schema::create('company_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('trId')->nullable();
            $table->string('typeId')->nullable();
            $table->bigInteger('companyId')->nullable()->index();
            $table->string('accountHead')->nullable();
            $table->date('transactionDate')->nullable()->index();
            $table->string('trType')->nullable();
            $table->double('amount', 15, 2)->nullable();
            $table->double('balance', 15, 2)->nullable();
            $table->string('particular')->nullable();
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
        Schema::dropIfExists('company_ledgers');
    }
};