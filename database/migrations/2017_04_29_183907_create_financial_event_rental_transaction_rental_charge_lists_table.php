<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventRentalTransactionRentalChargeListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_rental_transaction_rental_charge_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('financial_event_rental_transactions_id');
            $table->string('chargetype')->nullable();
            $table->string('currencycode')->nullable();
            $table->double('amount');
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
        Schema::connection('mysql2')->dropIfExists('financial_event_rental_transaction_rental_charge_lists');
    }
}
