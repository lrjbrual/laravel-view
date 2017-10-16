<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCampaignAdsBid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('campaign_ads_bids', function (Blueprint $table) {
            $table->string('campaignid');
            $table->string('adgroupid');
            $table->string('keywordid');
            $table->string('targetingtype');
            $table->index(['campaignid', 'adgroupid'], 'campid_adgid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('campaign_ads_bids', function (Blueprint $table) {
            // $table->dropColumn('campaignid');
            // $table->dropColumn('adgroupid');
            // $table->dropColumn('keywordid');
            // $table->dropColumn('targetingtype');
            // $table->dropIndex(['campid_adgid']);
        });
    }
}
