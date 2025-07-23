<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('companyId')->nullable()->constrained('companies')->onDelete('set null');
            $table->unsignedBigInteger('crBy')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('crBy')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
