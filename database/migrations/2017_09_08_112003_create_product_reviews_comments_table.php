<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductReviewsCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('product_reviews_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('review_id');
            $table->integer('seller_id');
            $table->text('comment');
            $table->datetime('date_created');
            $table->integer('isEdited');
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
        Schema::connection('mysql2')->dropIfExists('product_reviews_comments');
    }
}
