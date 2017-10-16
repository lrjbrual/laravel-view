<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecColToAdsFilter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::connection('mysql2')->table('campaign_advertising_filters', function (Blueprint $table) {            
            $table->string('filter_recommendation')->nullable("");  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('campaign_advertising_filters', function (Blueprint $table) {            
            $table->dropColumn('filter_recommendation'); 
        });
    }
}
