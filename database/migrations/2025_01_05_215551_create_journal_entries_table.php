<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('voucherNo')->nullable();
            $table->date('voucherDate')->nullable();
            $table->date('approvedDate')->nullable();
            $table->bigInteger('companyId')->nullable()->index();
            $table->bigInteger('debitSubGroupId')->index();
            $table->bigInteger('debitHeadId')->index();
            $table->double('debit', 15, 2)->nullable();
            $table->bigInteger('creditSubGroupId')->index();
            $table->bigInteger('creditHeadId')->index();
            $table->double('credit', 15, 2)->nullable();
            $table->string('checkNo')->nullable();
            $table->date('checkDate')->nullable();
            $table->string('trxId')->nullable();
            $table->string('ref')->nullable();
            $table->smallInteger('status')->default('0')->comment('0=Pending, 1=Approved, 2=Rejected');
            $table->bigInteger('createdBy')->nullable();
            $table->bigInteger('modifiedBy')->nullable();
            $table->bigInteger('deletedBy')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->longText('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
