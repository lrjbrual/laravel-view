<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeToNullableReviewDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::connection('mysql2')->table('product_reviews_reviews', function (Blueprint $table) {
            $table->dropColumn('review_date');
        });

         Schema::connection('mysql2')->table('product_reviews_reviews', function (Blueprint $table) {
            $table->datetime('review_date')->nullable()->after('review_title');
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
    }
}
