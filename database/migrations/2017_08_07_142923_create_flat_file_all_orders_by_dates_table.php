<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlatFileAllOrdersByDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('flat_file_all_orders_by_dates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('country');
            $table->string('amazon_order_id')->nullable();
            $table->string('merchant_order_id')->nullable();
            $table->datetime('purchase_date')->nullable();
            $table->datetime('last_updated_date')->nullable();
            $table->string('order_status')->nullable();
            $table->string('fulfillment_channel')->nullable();
            $table->string('sales_channel')->nullable();
            $table->string('order_channel')->nullable();
            $table->string('url')->nullable();
            $table->string('ship_service_level')->nullable();
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
            $table->string('asin')->nullable();
            $table->string('item_status')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('currency')->nullable();
            $table->double('item_price')->default(0);
            $table->double('item_tax')->default(0);
            $table->double('shipping_price')->default();
            $table->double('shipping_tax')->default(0);
            $table->double('gift_wrap_price')->default(0);
            $table->double('gift_wrap_tax')->default(0);
            $table->double('item_promotion_discount')->default(0);
            $table->double('ship_promotion_discount')->default(0);
            $table->string('ship_city')->nullable();
            $table->string('ship_state')->nullable();
            $table->string('ship_postal_code')->nullable();
            $table->string('ship_country')->nullable();
            $table->string('promotion_ids')->nullable();
            $table->string('item_extensions_data')->nullable();
            $table->string('is_business_order')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->string('price_designation')->nullable();
            $table->string('fulfilled_by')->nullable();
            $table->string('buyer_company_name')->nullable();
            $table->string('buyer_cst_number')->nullable();
            $table->string('buyer_vat_number')->nullable();
            $table->string('customized_url')->nullable();
            $table->string('customized_page')->nullable();
            $table->string('is_heavy_or_bulky')->nullable();
            $table->string('is_replacement_order')->nullable();
            $table->string('original_order_id')->nullable();
            $table->string('is_amazon_invoiced')->nullable();
            $table->double('vat_exclusive_item_price')->default();
            $table->double('vat_exclusive_shipping_price')->default();
            $table->double('vat_exclusive_giftwrap_price')->default();
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
        Schema::connection('mysql2')->dropIfExists('flat_file_all_orders_by_dates');
    }
}
