<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecommendationNameToCampaignAdsRecommendations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('campaign_ads_recommendations', function (Blueprint $table) {
            $table->string('recommendation_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('campaign_ads_recommendations', function (Blueprint $table) {
            $table->dropColumn('recommendation_name');
        });
    }
}
