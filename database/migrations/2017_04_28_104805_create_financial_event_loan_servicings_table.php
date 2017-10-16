<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventLoanServicingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_loan_servicings', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('seller_id');
          $table->double('amount')->default(0);
          $table->string('currency')->nullable();
          $table->string('sourcebusinesseventtype')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('financial_event_loan_servicings');
    }
}
