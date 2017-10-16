<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsCampaignKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('ads_campaign_keywords', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('country');
            $table->string('keywordid')->nullable();
            $table->string('adgroupid')->nullable();
            $table->string('campaignid')->nullable();
            $table->string('keywordtext')->nullable();
            $table->string('bid')->nullable();
            $table->string('matchtype')->nullable();
            $table->string('state')->nullable();
            $table->datetime('creationdate');
            $table->datetime('lastupdateddate');
            $table->string('servingstatus')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('ads_campaign_keywords');
    }
}
