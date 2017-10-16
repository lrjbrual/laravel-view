<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('settlement_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id')->unsigned();
            $table->string('settlement_id')->nullable();
            $table->dateTime('posted_date')->nullable();
            $table->string('order_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('type')->nullable();
            $table->integer('quantity')->nullable();
            $table->double('total')->nullable();
            $table->string('currency')->nullable();
            $table->string('adjustment_id')->nullable();
            $table->string('shipment_id')->nullable();
            $table->string('marketplace_name')->nullable();
            $table->string('fulfillment_id')->nullable();
            $table->double('price_amount')->nullable();
            $table->string('price_type')->nullable();
            $table->string('promotion_id')->nullable();
            $table->string('promotion_type')->nullable();
            $table->double('promotional_rebates')->default(0)->nullable();
            $table->double('other_amount')->default(0)->nullable();
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
        Schema::connection('mysql2')->dropIfExists('settlement_reports');
    }
}
