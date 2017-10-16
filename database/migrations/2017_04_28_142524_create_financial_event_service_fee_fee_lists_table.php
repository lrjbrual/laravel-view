<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventServiceFeeFeeListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_service_fee_fee_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('financial_event_service_fees_id');
            $table->string('feetype')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('financial_event_service_fee_fee_lists');
    }
}
