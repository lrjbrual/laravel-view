<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransferProductReviewsProductAndDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::dropIfExists('product_reviews_products');
        Schema::dropIfExists('product_reviews_reviews');

        Schema::connection('mysql2')->create('product_reviews_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id')->unsigned();
            $table->integer('url_id')->unsigned();
            $table->string('product_asin')->nullable();
            $table->string('title',1000)->nullable();
            $table->double('star_rating')->nullable();
            $table->integer('nb_of_reviews')->nullable();
            $table->datetime('date_of_change')->nullable();
            $table->boolean('changed');
            $table->string('status')->default('origin');
            $table->timestamps();
        });

        Schema::connection('mysql2')->create('product_reviews_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id');
            $table->integer('product_id');
            $table->string('review_code')->nullable();
            $table->datetime('review_date');
            $table->string('review_title')->nullable();
            $table->string('review_text',10000)->nullable();
            $table->string('variation')->nullable();
            $table->string('verified_purchase')->nullable();
            $table->integer('star');
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->integer('archieved');
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
        //
       Schema::connection('mysql2')->dropIfExists('product_reviews_reviews');
       Schema::connection('mysql2')->dropIfExists('product_reviews_products');
    }
}
