<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('dealers_assigned_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dealerId')->nullable()->index();
            $table->unsignedBigInteger('commissionId')->nullable()->index();
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealers_assigned_commissions');
    }
};
