<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('ads_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('country');
            $table->string('campaignid')->nullable();
            $table->string('name')->nullable();
            $table->string('campaigntype')->nullable();
            $table->string('targetingtype')->nullable();
            $table->string('premiumBidAdjustment')->nullable();
            $table->double('dailybudget')->nullable();
            $table->datetime('startdate');
            $table->datetime('enddate');
            $table->string('state')->nullable();
            $table->string('servingstatus')->nullable();
            $table->datetime('creationdate');
            $table->datetime('lastupdateddate');
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
        Schema::connection('mysql2')->dropIfExists('ads_campaigns');
    }
}
