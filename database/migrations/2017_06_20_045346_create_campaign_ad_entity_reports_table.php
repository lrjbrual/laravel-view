<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignAdEntityReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('campaign_ad_entity_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('country')->nullable();
            $table->datetime('posted_date')->nullable();
            $table->string('record_id')->nullable();
            $table->string('record_type')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('campaign_daily_budget')->nullable();
            $table->datetime('campaign_start_date')->nullable();
            $table->datetime('campaign_end_date')->nullable();
            $table->string('campaign_targeting_type')->nullable();
            $table->string('ad_group_name')->nullable();
            $table->string('max_bid')->nullable();
            $table->string('keyword')->nullable();
            $table->string('match_type')->nullable();
            $table->string('sku')->nullable();
            $table->string('campaign_status')->nullable();
            $table->string('adgroup_status')->nullable();
            $table->string('status')->nullable();
            $table->integer('impressions')->nullable();
            $table->integer('clicks')->nullable();
            $table->double('spend')->nullable();
            $table->integer('orders')->nullable();
            $table->double('sales')->nullable();
            $table->string('acos')->nullable();
            $table->string('bid+')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('campaign_ad_entity_reports');
    }
}
