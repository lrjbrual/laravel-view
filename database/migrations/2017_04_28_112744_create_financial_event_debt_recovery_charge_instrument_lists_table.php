<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventDebtRecoveryChargeInstrumentListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_debt_recovery_charge_instrument_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('financial_event_debt_recovery_id');

            $table->string('description')->nullable();
            $table->string('tail')->nullable();
            $table->string('currencycode')->nullable();
            $table->double('amount')->default(0);

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
        Schema::connection('mysql2')->dropIfExists('financial_event_debt_recovery_charge_instrument_lists');
    }
}
