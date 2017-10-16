<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsCampaignProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('ads_campaign_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->string('country');
            $table->string('adid')->nullable();
            $table->string('adgroupid')->nullable();
            $table->string('campaignid')->nullable();
            $table->string('sku')->nullable();
            $table->string('asin')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('ads_campaign_products');
    }
}
