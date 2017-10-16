<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventRetrochargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_retrocharges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('retrochargeeventtype')->nullable();
            $table->string('amazonorderid')->nullable();
            $table->dateTime('posteddate');

            $table->double('basetax_amount');
            $table->string('basetax_currencycode')->nullable();

            $table->double('shippingtax_amount');
            $table->string('shippingtax_currencycode')->nullable();

            $table->string('marketplacename')->nullable();

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
        Schema::connection('mysql2')->dropIfExists('financial_event_retrocharges');
    }
}
