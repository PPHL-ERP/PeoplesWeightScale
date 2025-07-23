<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('nameEn')->nullable();
            $table->string('nameBn')->nullable();
            $table->string('sloganEn')->nullable();
            $table->string('sloganBn')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('image')->nullable();
            $table->string('tin')->nullable();
            $table->string('bin')->nullable();
            $table->string('addressEn')->nullable();
            $table->string('addressBn')->nullable();
            $table->string('comEx')->nullable();
            $table->enum('status', ['approved', 'declined'])->default('approved')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
