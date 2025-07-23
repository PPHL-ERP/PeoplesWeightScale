<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE account_ledger_name ALTER COLUMN "partyId" TYPE bigint USING "partyId"::bigint');
    }

    public function down()
    {
        DB::statement('ALTER TABLE account_ledger_name ALTER COLUMN "partyId" TYPE varchar USING "partyId"::varchar');
    }
};
