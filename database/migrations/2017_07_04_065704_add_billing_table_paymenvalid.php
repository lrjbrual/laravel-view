<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingTablePaymenvalid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('billings', function (Blueprint $table) {
            //
            $table->boolean('payment_valid')->default(false);
            $table->dateTime('payment_invalid_date')->default(null)->nullable();
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
         Schema::table('billings', function (Blueprint $table) {
            //
            $table->dropColumn('payment_valid');
            $table->dropColumn('payment_invalid_date');
        });
    }
}
