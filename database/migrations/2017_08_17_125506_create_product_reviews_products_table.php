<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductReviewsProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_reviews_products', function (Blueprint $table) {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_reviews_products');
    }
}
