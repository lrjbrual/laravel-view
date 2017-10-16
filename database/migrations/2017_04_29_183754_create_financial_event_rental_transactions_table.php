<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventRentalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_rental_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('amazonorderid')->nullable();
            $table->string('rentaleventtype')->nullable();
            $table->double('extensionlength');
            $table->dateTime('posteddate');
            $table->string('marketplacename')->nullable();
            $table->string('rentalinitialvalue_currencycode')->nullable();
            $table->double('rentalinitialvalue_amount');
            $table->string('rentalreimbursement_currencycode')->nullable();
            $table->double('rentalreimbursement_amount');

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
        Schema::connection('mysql2')->dropIfExists('financial_event_rental_transactions');
    }
}
