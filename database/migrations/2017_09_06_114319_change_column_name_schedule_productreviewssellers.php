<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnNameScheduleProductreviewssellers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::dropIfExists('product_reviews_transactions');

         Schema::table('product_reviews_sellers', function (Blueprint $table) {
             $table->dropColumn('next_billing_date');
        });

         Schema::table('product_reviews_sellers', function (Blueprint $table) {
            $table->integer('bst_id')->nullable()->after('id');
            $table->datetime('schedule')->nullable()->after('seller_id');
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
        Schema::table('product_reviews_sellers', function (Blueprint $table) {
            $table->dropColumn('bst_id');
            $table->dropColumn('schedule');
        });

        Schema::table('product_reviews_sellers', function (Blueprint $table) {
             $table->datetime('next_billing_date');
        });
    }
}
