<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxBidRecToAdsCampaign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) {            
            $table->double('max_bid_recommendation')->default(0);  
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
            $table->dropColumn('max_bid_recommendation'); 
        });
    }
}
