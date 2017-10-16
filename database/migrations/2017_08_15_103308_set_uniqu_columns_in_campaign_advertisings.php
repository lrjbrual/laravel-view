<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetUniquColumnsInCampaignAdvertisings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('campaign_advertisings', function(Blueprint $table)
        {
            $table->unique(['keyword_id', 'customer_search_term', 'posted_date'], 'keycusdate');
            $table->index('customer_search_term');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('campaign_advertisings', function (Blueprint $table)
        {
             $table->dropIndex(['customer_search_term']);
             $table->dropUnique('keycusdate');
        });

    }
}
