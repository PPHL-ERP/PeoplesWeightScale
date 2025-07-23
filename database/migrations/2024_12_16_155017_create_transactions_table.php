
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chartOfHeadId')->nullable()->index();
            $table->bigInteger('companyId')->nullable()->index();
            $table->string('voucherNo')->nullable();
            $table->string('voucherType')->nullable();
            $table->date('voucherDate')->nullable();
            $table->text('note')->nullable();
            $table->double('debit', 10, 2);
            $table->double('credit', 10, 2);
            $table->tinyInteger('status')->default('1')->comment('1=Approved, 2=Rejected, 3=deleted');
            $table->bigInteger('createdBy')->nullable();
            $table->bigInteger('modifiedBy')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
