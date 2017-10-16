<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaseSubscriptionSellerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('base_subscription_seller_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bss_id')->unsigned();
            $table->string('bs_name')->nullable();
            $table->integer('bonus_mail')->nullable();
            $table->integer('email_used')->nullable();
            $table->double('amount_to_pay')->nullable();
            $table->boolean('is_pro_rated')->nullable();
            $table->integer('days_used')->nullable();
            $table->boolean('currently_used')->nullable();
            $table->boolean('up_next')->nullable();
            $table->string('comments')->nullable();
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
        Schema::dropIfExists('base_subscription_seller_transactions');
    }
}
