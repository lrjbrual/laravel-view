<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCheckerCountryToTrendleCheckersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trendle_checkers', function (Blueprint $table) {      
            $table->string('checker_country')->after('checker_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trendle_checkers', function (Blueprint $table) {
            $table->dropColumn('checker_country');
        });
    }
}
