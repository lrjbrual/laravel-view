<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecommendationAndBidToCampaignAdvertisings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) { 
            $table->string('recommendation');     
            $table->string('bid');
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
            $table->dropColumn('recommendation');     
            $table->dropColumn('bid');
        });
    }
}
