<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNxtDateInFbaRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('fba_refunds', function (Blueprint $table) {
            $table->dropColumn('nxt_date');
        });
        Schema::connection('mysql2')->table('fba_refunds', function (Blueprint $table) {
            $table->dateTime('nxt_date')->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('fba_refunds', function (Blueprint $table) {
            $table->dropColumn('nxt_date');
        });
    }
}
