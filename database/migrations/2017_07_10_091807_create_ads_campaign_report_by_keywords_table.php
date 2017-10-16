<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsCampaignReportByKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('ads_campaign_report_by_keywords', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('keyword_id');
            $table->string('country');
            $table->integer('impressions');
            $table->integer('clicks');
            $table->double('cost');
            $table->string('attributedconversions1dsamesku');
            $table->string('attributedconversions1d');
            $table->double('attributedsales1dsamesku');
            $table->double('attributedsales1d');
            $table->string('attributedconversions7dsamesku');
            $table->string('attributedconversions7d');
            $table->double('attributedsales7dsamesku');
            $table->double('attributedsales7d');
            $table->string('attributedconversions30dsamesku');
            $table->string('attributedconversions30d');
            $table->double('attributedsales30dsamesku');
            $table->double('attributedsales30d');
            $table->datetime('posted_date');
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
        Schema::connection('mysql2')->dropIfExists('ads_campaign_report_by_keywords');
    }
}
