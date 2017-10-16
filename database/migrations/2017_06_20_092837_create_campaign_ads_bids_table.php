<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignAdsBidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('campaign_ads_bids', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->integer('campaign_ads_id');
            $table->double('bid_from')->nullable(); 
            $table->double('bid_to');
            $table->string('match_type');
            $table->boolean('is_uploaded');
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
        Schema::connection('mysql2')->dropIfExists('campaign_ads_bids');
    }
}
