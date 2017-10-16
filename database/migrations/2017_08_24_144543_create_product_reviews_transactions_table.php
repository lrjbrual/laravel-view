<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductReviewsTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_reviews_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('prs_id');
            $table->string('subscription_name');
            $table->double('amount_to_pay');
            $table->datetime('pr_schedule');
            $table->integer('currently_used');
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
        Schema::dropIfExists('product_reviews_transactions');
    }
}
