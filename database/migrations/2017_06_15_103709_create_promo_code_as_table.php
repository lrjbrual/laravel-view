<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromoCodeAsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_code_as', function (Blueprint $table) {
            $table->increments('id');
            $table->string('voucher_code');
            $table->string('voucher_type');
            $table->string('discount_type');
            $table->integer('discount_value');
            $table->integer('max_redemption');
            $table->integer('days_applied')->nullable();
            $table->string('currency')->nullable();
            $table->tinyInteger('is_active');
            $table->dateTime('redeem_by')->nullable();
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
        Schema::dropIfExists('promo_code_as');
    }
}
