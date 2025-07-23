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
        Schema::create('chicks_productions', function (Blueprint $table) {
            $table->id();
            $table->string('productionId')->nullable();
            $table->bigInteger('hatcheryId')->nullable()->index();
            $table->string('settingId')->nullable();
            $table->string('eggSource')->nullable();
            $table->date('settingDate')->nullable()->index();
            $table->date('hatchDate')->nullable()->index();
            $table->bigInteger('breedId')->nullable()->index();
            $table->bigInteger('flockId')->nullable();
            $table->string('color')->nullable();
            $table->decimal('totalEggSetting', 10, 2)->nullable();
            $table->longText('note')->nullable();
            $table->bigInteger('crBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->string(column: 'status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chicks_productions');
    }
};
