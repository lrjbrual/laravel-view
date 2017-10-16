<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellerReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('seller_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date_created')->nullable();
            $table->dateTime('review_date')->nullable();
            $table->string('country',5)->nullable();
            $table->string('sku',150)->nullable();
            $table->string('asin',150)->nullable();
            $table->text('product_name')->nullable();
            $table->text('review_comment')->nullable();
            $table->integer('reviewer_rating', false, true)->length(10);
            $table->string('reviewer_name',150)->nullable();
            $table->string('order_number',150)->nullable();
            $table->boolean('status')->default(true);
            $table->string('reviewer_profile_url',250)->nullable();
            $table->string('item_profile_url',250)->nullable();
            $table->integer('review_id', false, true)->length(10)->nullable();
            $table->dateTime('action_date')->nullable();
            $table->text('your_response')->nullable();
            $table->string('arrived_on_time',10)->nullable();
            $table->string('item_as_described',10)->nullable();
            $table->string('customer_service',150)->nullable();
            $table->string('rater_role',150)->nullable();
            $table->string('reviewer_email',150)->nullable();
            $table->boolean('isBlacklist')->default(true);
            $table->integer('seller_id', false, true)->length(15)->nullable();
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
        Schema::connection('mysql2')->dropIfExists('seller_reviews');
    }
}
