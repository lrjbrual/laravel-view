<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFulfilledShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('fulfilled_shipments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('amazon_order_id')->nullable();
            $table->text('merchant_order_id')->nullable();
            $table->text('shipment_id')->nullable();
            $table->text('shipment_item_id')->nullable();
            $table->text('amazon_order_item_id')->nullable();
            $table->text('merchant_order_item_id')->nullable();
            $table->dateTime('purchase_date')->nullable();
            $table->dateTime('payments_date')->nullable();
            $table->dateTime('shipment_date')->nullable();
            $table->dateTime('reporting_date')->nullable();
            $table->text('buyer_email')->nullable();
            $table->text('buyer_name')->nullable();
            $table->text('buyer_phone_number')->nullable();
            $table->text('sku')->nullable();
            $table->text('product_name')->nullable();
            $table->text('quantity_shipped')->nullable();
            $table->text('currency')->nullable();
            $table->float('item_price', 15, 2)->nullable();
            $table->float('item_tax', 15, 2)->nullable();
            $table->float('shipping_price', 15, 2)->nullable();
            $table->float('shipping_tax', 15, 2)->nullable();
            $table->float('gift_wrap_price', 15, 2)->nullable();
            $table->float('gift_wrap_tax', 15, 2)->nullable();
            $table->text('ship_service_level')->nullable();
            $table->text('recipient_name')->nullable();
            $table->text('ship_address_1')->nullable();
            $table->text('ship_address_2')->nullable();
            $table->text('ship_address_3')->nullable();
            $table->text('ship_city')->nullable();
            $table->text('ship_state')->nullable();
            $table->text('ship_postal_code')->nullable();
            $table->text('ship_country')->nullable();
            $table->text('ship_phone_number')->nullable();
            $table->text('bill_address_1')->nullable();
            $table->text('bill_address_2')->nullable();
            $table->text('bill_address_3')->nullable();
            $table->text('bill_city')->nullable();
            $table->text('bill_state')->nullable();
            $table->text('bill_postal_code')->nullable();
            $table->text('bill_country')->nullable();
            $table->double('item_promotion_discount',15, 2)->nullable();
            $table->double('ship_promotion_discount',15, 2)->nullable();
            $table->text('carrier')->nullable();
            $table->text('tracking_number')->nullable();
            $table->dateTime('estimated_arrival_date')->nullable();
            $table->text('fulfillment_center_id')->nullable();
            $table->text('fulfillment_channel')->nullable();
            $table->text('sales_channel')->nullable();
            $table->boolean('isEmailAddedToSparkpost')->default(0);
            $table->integer('seller_id', false, true)->length(15);
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
        Schema::connection('mysql2')->dropIfExists('fulfilled_shipments');
    }
}
