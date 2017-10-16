<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FinancialEventsReportRaws extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('financial_events_report_raws', function (Blueprint $table) {
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
            $table->string('item_related_fee_type')->nullable();
            $table->double('item_related_fee_amount')->default(0)->nullable();
            $table->string('shipment_fee_type')->nullable();
            $table->double('shipment_fee_amount')->default(0)->nullable();
            $table->string('order_fee_type')->nullable();
            $table->double('order_fee_amount')->default(0)->nullable();
            $table->string('order_item_code')->nullable();
            $table->string('merchant_order_item_id')->nullable();
            $table->string('merchant_adjustment_item_id')->nullable();
            $table->string('direct_payment_type')->nullable();
            $table->double('direct_payment_amount')->default(0)->nullable();
            $table->string('asin')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('amazon_seller_id')->nullable();
            $table->string('seller_store_name')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('provider_store_name')->nullable();

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
        Schema::connection('mysql2')->dropIfExists('financial_events_report_raws');
    }
}
