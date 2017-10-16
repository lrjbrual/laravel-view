<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToAdsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('ads_campaigns', function(Blueprint $table)
        {
            $table->index('campaignid');
            $table->index('name');
            $table->index('country');
        });
        Schema::connection('mysql2')->table('ads_campaign_ad_groups', function(Blueprint $table)
        {
            $table->index('adgroupid');
            $table->index('campaignid');
            $table->index('name');
            $table->index('country');
        });
        Schema::connection('mysql2')->table('ads_campaign_keywords', function(Blueprint $table)
        {
            $table->index('keywordid');
            $table->index('adgroupid');
            $table->index('campaignid');
            $table->index('keywordtext');
            $table->index('country');
        });
        Schema::connection('mysql2')->table('ads_campaign_products', function(Blueprint $table)
        {
            $table->index('adid');
            $table->index('adgroupid');
            $table->index('campaignid');
            $table->index('sku');
            $table->index('asin');
            $table->index('country');
        });
        Schema::connection('mysql2')->table('campaign_advertisings', function(Blueprint $table)
        {
            $table->index('keyword');
            $table->index('adgroupid');
            $table->index('campaignid');
            $table->index('ad_group_name');
            $table->index('campaign_name');
            $table->index('country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('ads_campaigns', function (Blueprint $table)
        {
            $table->dropIndex(['campaignid']);
            $table->dropIndex(['name']);
            $table->dropIndex(['country']);
        });
        Schema::connection('mysql2')->table('ads_campaign_ad_groups', function (Blueprint $table)
        {
            $table->dropIndex(['adgroupid']);
            $table->dropIndex(['campaignid']);
            $table->dropIndex(['name']);
            $table->dropIndex(['country']);
        });
        Schema::connection('mysql2')->table('ads_campaign_keywords', function (Blueprint $table)
        {
            $table->dropIndex(['adgroupid']);
            $table->dropIndex(['campaignid']);
            $table->dropIndex(['keywordid']);
            $table->dropIndex(['keywordtext']);
            $table->dropIndex(['country']);
        });
        Schema::connection('mysql2')->table('ads_campaign_products', function (Blueprint $table)
        {
            $table->dropIndex(['adgroupid']);
            $table->dropIndex(['campaignid']);
            $table->dropIndex(['adid']);
            $table->dropIndex(['sku']);
            $table->dropIndex(['asin']);
            $table->dropIndex(['country']);
        });
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table)
        {
            $table->dropIndex(['adgroupid']);
            $table->dropIndex(['campaignid']);
            $table->dropIndex(['keyword']);
            $table->dropIndex(['campaign_name']);
            $table->dropIndex(['ad_group_name']);
            $table->dropIndex(['country']);
        });
    }
}
