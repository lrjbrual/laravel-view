<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDataTypeToCampaignAdvertisings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) {
            $table->dropColumn('product_sales_within_1_week_of_a_click');
            $table->dropColumn('conversion_rate_within_1_week_of_a_click');
            $table->dropColumn('same_sku_units_product_sales_within_1_week_of_click');
            $table->dropColumn('other_sku_units_product_sales_within_1_week_of_click');
        });
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) {    
            $table->double('product_sales_within_1_week_of_a_click')->after('orders_placed_within_1_week_of_a_click');
            $table->double('conversion_rate_within_1_week_of_a_click')->after('product_sales_within_1_week_of_a_click');
            $table->double('same_sku_units_product_sales_within_1_week_of_click')->after('other_sku_units_ordered_within_1_week_of_click');
            $table->double('other_sku_units_product_sales_within_1_week_of_click')->after('same_sku_units_product_sales_within_1_week_of_click');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) {
            $table->dropColumn('product_sales_within_1_week_of_a_click');
            $table->dropColumn('conversion_rate_within_1_week_of_a_click');
            $table->dropColumn('same_sku_units_product_sales_within_1_week_of_click');
            $table->dropColumn('other_sku_units_product_sales_within_1_week_of_click');
        });
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) {    
            $table->integer('product_sales_within_1_week_of_a_click')->after('orders_placed_within_1_week_of_a_click');
            $table->integer('conversion_rate_within_1_week_of_a_click')->after('product_sales_within_1_week_of_a_click');
            $table->integer('same_sku_units_product_sales_within_1_week_of_click')->after('other_sku_units_ordered_within_1_week_of_click');
            $table->integer('other_sku_units_product_sales_within_1_week_of_click')->after('same_sku_units_product_sales_within_1_week_of_click');
        });
        
    }
}
