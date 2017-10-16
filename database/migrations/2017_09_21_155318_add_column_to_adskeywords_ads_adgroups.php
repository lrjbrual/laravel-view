<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToAdskeywordsAdsAdgroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('ads_campaign_ad_groups', function (Blueprint $table) {
            $table->string('max_bid_recommendation');
            $table->string('recommendation');
        });
        Schema::connection('mysql2')->table('ads_campaign_keywords', function (Blueprint $table) {
            $table->string('max_bid_recommendation');
            $table->string('recommendation');
        });
        Schema::connection('mysql2')->table('ads_campaigns', function (Blueprint $table) {
            $table->index(['seller_id','country'], 'seller_id-country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('ads_campaign_ad_groups', function (Blueprint $table) {
            $table->dropColumn('max_bid_recommendation');
            $table->dropColumn('recommendation');
        });
        Schema::connection('mysql2')->table('ads_campaign_keywords', function (Blueprint $table) {
            $table->dropColumn('max_bid_recommendation');
            $table->dropColumn('recommendation');
        });
        Schema::connection('mysql2')->table('ads_campaigns', function (Blueprint $table) {
            $table->dropIndex(['seller_id-country']);
        });
    }
}
