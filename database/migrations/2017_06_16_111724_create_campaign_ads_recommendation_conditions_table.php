<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignAdsRecommendationConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('campaign_ads_recommendation_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('camp_ads_rec_id');
            $table->string('operation');
            $table->string('matrix');
            $table->string('metric');
            $table->string('value');
            $table->timestamps();            
            $table->boolean('is_active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('campaign_ads_recommendation_conditions');
    }
}
