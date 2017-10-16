<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventDebtRecoveryItemListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_debt_recovery_item_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('financial_event_debt_recovery_id');

            $table->string('recoverycurrencycode')->nullable();
            $table->double('recoveryamount')->default(0);

            $table->string('originalcurrencycode')->nullable();
            $table->double('originalamount')->default(0);

            $table->dateTime('groupbegindate');
            $table->dateTime('groupenddate');

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
        Schema::connection('mysql2')->dropIfExists('financial_event_debt_recovery_item_lists');
    }
}
