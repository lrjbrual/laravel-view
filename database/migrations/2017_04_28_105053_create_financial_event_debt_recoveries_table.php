<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventDebtRecoveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_debt_recoveries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('debtrecoverytype')->nullable();
            $table->string('currencycode')->nullable();
            $table->double('amount')->default(0);
            $table->string('overpaymentcredit_currencycode')->nullable();
            $table->double('overpaymentcredit_amount')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('financial_event_debt_recoveries');
    }
}
