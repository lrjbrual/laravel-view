<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignAdMegaReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('campaign_ad_mega_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id', false, true)->length(15)->nullable();
            $table->string('country')->nullable();
            $table->datetime('posted_date')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('ad_group_name')->nullable();
            $table->string('advertised_sku')->nullable();
            $table->string('type')->nullable();
            $table->string('currency')->nullable();
            $table->string('keyword')->nullable();
            $table->string('match_type')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->integer('impressions', false, true)->length(15)->nullable();
            $table->integer('clicks', false, true)->length(15)->nullable();
            $table->double('ctr')->nullable();
            $table->double('total_spend')->nullable();
            $table->double('average_cpc')->nullable();
            $table->integer('1_day_orders_placed', false, true)->length(15)->nullable();
            $table->integer('1_day_ordered_product_sales', false, true)->length(15)->nullable();
            $table->double('1_day_convertion_rate')->nullable();
            $table->integer('1_day_same_sku_units_ordered', false, true)->length(15)->nullable();
            $table->integer('1_day_other_sku_units_ordered', false, true)->length(15)->nullable();
            $table->double('1_day_same_sku_units_ordered_product_sales')->nullable();
            $table->double('1_day_other_sku_units_ordered_product_sales')->nullable();
            $table->integer('1_week_orders_placed', false, true)->length(15)->nullable();
            $table->integer('1_week_ordered_product_sales', false, true)->length(15)->nullable();
            $table->double('1_week_convertion_rate')->nullable();
            $table->integer('1_week_same_sku_units_ordered', false, true)->length(15)->nullable();
            $table->integer('1_week_other_sku_units_ordered', false, true)->length(15)->nullable();
            $table->double('1_week_same_sku_units_ordered_product_sales')->nullable();
            $table->double('1_week_other_sku_units_ordered_product_sales')->nullable();
            $table->integer('1_month_orders_placed', false, true)->length(15)->nullable();
            $table->integer('1_month_ordered_product_sales', false, true)->length(15)->nullable();
            $table->double('1_month_convertion_rate')->nullable();
            $table->integer('1_month_same_sku_units_ordered', false, true)->length(15)->nullable();
            $table->integer('1_month_other_sku_units_ordered', false, true)->length(15)->nullable();
            $table->double('1_month_same_sku_units_ordered_product_sales')->nullable();
            $table->double('1_month_other_sku_units_ordered_product_sales')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('campaign_ad_mega_reports');
    }
}
