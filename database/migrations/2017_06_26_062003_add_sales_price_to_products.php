<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalesPriceToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('products', function (Blueprint $table) {            
            $table->double('sale_price')->default(0);           
            $table->double('advice_margin')->default(0);           
            $table->integer('time_period')->default(14);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('products', function (Blueprint $table) {            
            $table->dropColumn('sale_price');           
            $table->dropColumn('advice_margin');           
            $table->dropColumn('time_period');
        });
    }
}
