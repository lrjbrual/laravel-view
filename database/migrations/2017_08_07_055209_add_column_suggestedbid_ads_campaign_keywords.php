<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSuggestedbidAdsCampaignKeywords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql2')->table('ads_campaign_keywords', function (Blueprint $table) { 
            $table->double('rangeEnd')->after('bid');
            $table->double('rangeStart')->after('bid');
            $table->double('suggestedBid')->after('bid');
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
        Schema::connection('mysql2')->table('ads_campaign_keywords', function (Blueprint $table) { 
            $table->dropColumn('rangeStart');
            $table->dropColumn('rangeEnd');
            $table->dropColumn('suggestedBid');
        });
    }
}
