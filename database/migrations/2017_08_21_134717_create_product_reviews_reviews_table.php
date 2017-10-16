<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductReviewsReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_reviews_reviews', function (Blueprint $table) {
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
        Schema::dropIfExists('product_reviews_reviews');
    }
}
