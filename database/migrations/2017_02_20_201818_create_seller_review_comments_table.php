<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellerReviewCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('seller_review_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id', false, true)->length(15);
            $table->integer('seller_review_id', false, true)->length(15);
            $table->text("comment")->nullable();
            $table->dateTime("date_created")->nullable();
            $table->boolean("isEdited")->default(0);
            $table->integer('seller_id', false, true)->length(15);
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
        Schema::connection('mysql2')->dropIfExists('seller_review_comments');
    }
}
