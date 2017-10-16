<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSuggestedbidAdsCampaignAdgroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql2')->table('ads_campaign_ad_groups', function (Blueprint $table) { 
            $table->double('rangeEnd')->after('state');
            $table->double('rangeStart')->after('state');
            $table->double('suggestedBid')->after('state');
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
        Schema::connection('mysql2')->table('ads_campaign_ad_groups', function (Blueprint $table) { 
            $table->dropColumn('rangeStart');
            $table->dropColumn('rangeEnd');
            $table->dropColumn('suggestedBid');
        });
    }
}
