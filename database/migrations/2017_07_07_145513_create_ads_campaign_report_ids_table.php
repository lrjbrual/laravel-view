<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsCampaignReportIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('ads_campaign_report_ids', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('country');
            $table->integer('mkp_id');
            $table->string('report_id', 1000)->nullable();
            $table->string('report_url', 1000)->nullable();
            $table->datetime('posted_date')->nullable();
            $table->boolean('is_new')->default(0);
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
        Schema::connection('mysql2')->dropIfExists('ads_campaign_report_ids');
    }
}
