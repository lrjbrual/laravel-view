<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialEventServiceFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_event_service_fees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('amazonorderid')->nullable();
            $table->string('feereason')->nullable();
            $table->string('sellersku')->nullable();
            $table->string('fnsku')->nullable();
            $table->string('feedescription')->nullable();
            $table->string('asin')->nullable();

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
        Schema::connection('mysql2')->dropIfExists('financial_event_service_fees');
    }
}
