<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsonboardedToSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('sellers', function (Blueprint $table) {            
            $table->boolean('is_onboarded_to_billing')->default(0);  
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
        Schema::table('sellers', function (Blueprint $table) {            
            $table->dropColumn('is_onboarded_to_billing'); 
        });
    }
}