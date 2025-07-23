<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('payment_payable_infos', function (Blueprint $table) {
            $table->id();
            $table->string('voucherNo')->nullable();
            $table->bigInteger('companyId')->nullable()->index();
            $table->tinyInteger('paidType')->nullable()->comment('1=From Dealer, 2=From Employee, 3=From Vendor');
            $table->bigInteger('chartOfHeadId')->nullable()->index();
            $table->double('amount', 15, 2)->nullable();
            $table->date('paidDate')->nullable()->index();
            $table->bigInteger('paymentType')->nullable();
            $table->bigInteger('paymentMode')->nullable();
            $table->bigInteger('paymentFor')->nullable();
            $table->tinyInteger('invoiceType')->comment('1=with voucher, 2=without voucher');
            $table->string('checkNo')->nullable();
            $table->date('checkDate')->nullable();
            $table->string('trxId')->nullable();
            $table->string('ref')->nullable();
            $table->smallInteger('status')->default('0')->comment('0=Pending, 1=Approved, 2=Rejected');
            $table->bigInteger('createdBy')->nullable();
            $table->bigInteger('modifiedBy')->nullable();
            $table->bigInteger('deletedBy')->nullable();
            $table->longText('note')->nullable();
            $table->bigInteger('appBy')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('payment_payable_infos');
    }
};
