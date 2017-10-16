<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSuggestedbidCampaignAdvertisings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) { 
            $table->double('rangeEnd')->after('match_type');
            $table->double('rangeStart')->after('match_type');
            $table->double('suggestedBid')->after('match_type');
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
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table) { 
            $table->dropColumn('rangeStart');
            $table->dropColumn('rangeEnd');
            $table->dropColumn('suggestedBid');
        });
    }
}
