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
        Schema::create('egg_receives', function (Blueprint $table) {
            $table->id();
            $table->string('recId')->nullable();
            $table->bigInteger('transferId')->nullable()->index();
            $table->bigInteger('transferFrom')->nullable()->index();
            $table->string('recHead')->nullable();
            $table->bigInteger('recStore')->nullable()->index();
            $table->string('chalanNo')->nullable();
            $table->date('date')->nullable()->index();
            $table->string('unLoadBy')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
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
        Schema::dropIfExists('egg_receives');
    }
};
