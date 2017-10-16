<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCampaignAdsRecommendation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql2')->table('campaign_ads_recommendations', function (Blueprint $table) {
            $table->string('ad_group_name');
            $table->string('camp_type');
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
        Schema::connection('mysql2')->table('campaign_ads_recommendations', function (Blueprint $table) {
            $table->dropColumn('ad_group_name');
            $table->dropColumn('camp_type');
        });
    }
}
