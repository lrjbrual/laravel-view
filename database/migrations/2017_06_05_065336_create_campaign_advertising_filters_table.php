<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignAdvertisingFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('campaign_advertising_filters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id', false, true)->length(15);
            $table->string('filter_name')->nullable();
            $table->string('filter_columns')->nullable();
            $table->datetime('filter_date_start')->nullable();
            $table->datetime('filter_date_end')->nullable();
            $table->string('filter_imp')->nullable();
            $table->string('filter_clicks')->nullable();
            $table->string('filter_ctr')->nullable();
            $table->string('filter_total_spend')->nullable();
            $table->string('filter_avg_cpc')->nullable();
            $table->string('filter_acos')->nullable();
            $table->string('filter_conv_rate')->nullable();
            $table->string('filter_revenue')->nullable();
            $table->string('filter_country')->nullable();
            $table->string('filter_camp_type')->nullable();
            $table->string('filter_camp_name')->nullable();
            $table->string('filter_ad_group')->nullable();
            $table->string('filter_keyword')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('campaign_advertising_filters');
    }
}
