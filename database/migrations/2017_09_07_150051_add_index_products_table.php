<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql2')->table('products', function(Blueprint $table)
        {
            $table->index([DB::raw('asin(10)')]);
            $table->index([DB::raw('country(2)')]);
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
        Schema::connection('mysql2')->table('products', function (Blueprint $table)
        {
            $table->dropIndex(['asin(10)']);
            $table->dropIndex(['country(2)']);
        });
    }
}
