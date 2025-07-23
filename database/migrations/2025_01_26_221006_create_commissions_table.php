<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('companyId')->nullable()->index();
            $table->string('commissionNo')->nullable()->index();
            $table->date(column: 'commissionDate')->index();
            $table->smallInteger('commissionType')->index();
            $table->unsignedBigInteger('categoryId')->nullable()->index();
            $table->unsignedBigInteger('dealerId')->nullable()->index();
            $table->unsignedBigInteger('zoneId')->nullable()->index();
            $table->text('note')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('crBy')->nullable();
            $table->unsignedBigInteger('appBy')->nullable();
            $table->unsignedBigInteger('delBy')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
