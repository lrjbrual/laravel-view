<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAmazonSellerDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amazon_seller_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->integer('mkp_id');
            $table->string('amz_profile_id');
            $table->string('amz_country_code');
            $table->string('amz_access_token', 1000);
            $table->string('amz_refresh_token', 1000);
            $table->string('amz_token_type');
            $table->datetime('amz_expires_in');
            $table->integer('is_active')->default(0);
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
        Schema::dropIfExists('amazon_seller_details');
    }
}
