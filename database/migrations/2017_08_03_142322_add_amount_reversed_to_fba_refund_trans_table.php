<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountReversedToFbaRefundTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('fba_refund_trans', function (Blueprint $table) {           
            $table->string('amount_reversed')->after('amount_reimbursed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('fba_refund_trans', function (Blueprint $table) {
            $table->dropColumn('amount_reversed');
        });
    }
}
