<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnChangedToBoolean extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::connection('mysql2')->table('product_reviews_products', function (Blueprint $table) {
            $table->dropColumn('changed');
        });

         Schema::connection('mysql2')->table('product_reviews_products', function (Blueprint $table) {
            $table->integer('changed')->nullable()->after('date_of_change');
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
