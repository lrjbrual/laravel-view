<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToAdsCampaignReportByKeywords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql2')->table('ads_campaign_report_by_keywords', function (Blueprint $table) {            
            $table->string('query');  
        });
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) {            
            $table->string('keyword_id');           
            $table->integer('ads_campaign_report_by_keywords_id');
            $table->string('campaignid');
            $table->string('adgroupid');  
            $table->dropColumn('orders_placed_within_1_week_of_a_click');
            $table->double('attributedconversions1dsamesku');
            $table->dropColumn('product_sales_within_1_week_of_a_click');
            $table->double('attributedconversions1d');
            $table->dropColumn('conversion_rate_within_1_week_of_a_click');
            $table->double('attributedsales1dsamesku');
            $table->dropColumn('same_sku_units_ordered_within_1_week_of_click');
            $table->double('attributedsales1d');
            $table->dropColumn('other_sku_units_ordered_within_1_week_of_click');
            $table->double('attributedconversions7dsamesku');
            $table->dropColumn('same_sku_units_product_sales_within_1_week_of_click');
            $table->double('attributedconversions7d');
            $table->dropColumn('other_sku_units_product_sales_within_1_week_of_click');
            $table->double('attributedsales7dsamesku');
            $table->double('attributedsales7d');
            $table->double('attributedconversions30dsamesku');
            $table->double('attributedconversions30d');
            $table->double('attributedsales30dsamesku');
            $table->double('attributedsales30d');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::connection('mysql2')->table('ads_campaign_report_by_keywords', function (Blueprint $table) {            
            $table->dropColumn('query'); 
        });
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) {            
            $table->dropColumn('keyword_id');     
            $table->dropColumn('campaignid');
            $table->dropColumn('adgroupid');        
            $table->dropColumn('ads_campaign_report_by_keywords_id');
            $table->dropColumn('attributedconversions1dsamesku');
            $table->double('orders_placed_within_1_week_of_a_click'); 
            $table->dropColumn('attributedconversions1d');
            $table->double('product_sales_within_1_week_of_a_click'); 
            $table->dropColumn('attributedsales1dsamesku');
            $table->double('conversion_rate_within_1_week_of_a_click'); 
            $table->dropColumn('attributedsales1d');
            $table->double('same_sku_units_ordered_within_1_week_of_click'); 
            $table->dropColumn('attributedconversions7dsamesku');
            $table->double('other_sku_units_ordered_within_1_week_of_click'); 
            $table->dropColumn('attributedconversions7d');
            $table->double('same_sku_units_product_sales_within_1_week_of_click'); 
            $table->dropColumn('attributedsales7dsamesku');
            $table->double('other_sku_units_product_sales_within_1_week_of_click'); 
            $table->dropColumn('attributedsales7d');
            $table->dropColumn('attributedconversions30dsamesku');
            $table->dropColumn('attributedconversions30d');
            $table->dropColumn('attributedsales30dsamesku');
            $table->dropColumn('attributedsales30d');
        });
    }
}
