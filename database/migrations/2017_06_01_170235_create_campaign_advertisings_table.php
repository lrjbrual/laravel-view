<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignAdvertisingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('campaign_advertisings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('seller_id');
            $table->string('campaign_name')->nullable();
            $table->string('ad_group_name')->nullable();
            $table->string('type')->nullable();
            $table->string('keyword')->nullable();
            $table->string('currency')->nullable();
            $table->string('country')->nullable();
            $table->string('customer_search_term')->nullable();
            $table->string('match_type')->nullable();
            $table->dateTime('first_day_of_impression')->nullable();
            $table->dateTime('last_day_of_impression')->nullable();
            $table->integer('impressions')->nullable();
            $table->integer('clicks')->nullable();
            $table->double('ctr')->nullable();
            $table->double('total_spend')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('acos')->nullable();
            $table->integer('orders_placed_within_1_week_of_a_click')->nullable();
            $table->integer('product_sales_within_1_week_of_a_click')->nullable();
            $table->integer('conversion_rate_within_1_week_of_a_click')->nullable();
            $table->integer('same_sku_units_ordered_within_1_week_of_click')->nullable();
            $table->integer('other_sku_units_ordered_within_1_week_of_click')->nullable();
            $table->integer('same_sku_units_product_sales_within_1_week_of_click')->nullable();
            $table->integer('other_sku_units_product_sales_within_1_week_of_click')->nullable();
            $table->dateTime('posted_date')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('campaign_advertisings');
    }
}
