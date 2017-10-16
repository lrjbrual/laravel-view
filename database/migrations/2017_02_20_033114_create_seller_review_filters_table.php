<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellerReviewFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('seller_review_filters', function (Blueprint $table) {
            $table->increments('id');
            $table->text('filter_name');
            $table->text('country_filter')->nullable();
            $table->text('column_to_filter')->nullable();
            $table->text('text_filter')->nullable();
            $table->integer('rating_from_filter', false, true)->length(5);
            $table->integer('rating_to_filter', false, true)->length(5);
            $table->text('date_range_filter')->nullable();
            $table->dateTime('date_from_filter')->nullable();
            $table->dateTime('date_to_filter')->nullable();
            $table->dateTime('date_created');
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
        Schema::connection('mysql2')->dropIfExists('seller_review_filters');
    }
}
