<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('inventory_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id', false, true)->length(15)->nullable();
            $table->text('item_name')->nullable();
            $table->text('item_description')->nullable();
            $table->text('listing_id')->nullable();
            $table->text('seller_sku')->nullable();
            $table->float('price', 15, 2)->nullable();
            $table->integer('quantity', false, true)->length(15)->nullable();
            $table->dateTime('open_date')->nullable();
            $table->text('image_url')->nullable();
            $table->text('item_is_marketplace')->nullable();
            $table->text('product_id_type')->nullable();
            $table->float('zshop_shipping_fee', 15, 2)->nullable();
            $table->text('item_note')->nullable();
            $table->text('item_condition')->nullable();
            $table->text('zshop_category1')->nullable();
            $table->text('zshop_browse_path')->nullable();
            $table->text('zshop_storefront_feature')->nullable();
            $table->text('asin1')->nullable();
            $table->text('asin2')->nullable();
            $table->text('asin3')->nullable();
            $table->text('will_ship_internationally')->nullable();
            $table->text('expedited_shipping')->nullable();
            $table->text('zshop_boldface')->nullable();
            $table->text('product_id')->nullable();
            $table->text('bid_for_featured_placement')->nullable();
            $table->text('add_delete')->nullable();
            $table->text('pending_quantity')->nullable();
            $table->text('fulfillment_channel')->nullable();
            $table->text('optional_payment_type_exclusion')->nullable();
            $table->text('merchant_shipping_group')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('inventory_datas');
    }
}
