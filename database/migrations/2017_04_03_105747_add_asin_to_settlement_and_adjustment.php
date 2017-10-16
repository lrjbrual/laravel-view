<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAsinToSettlementAndAdjustment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::connection('mysql2')->table('settlement_reports', function($table) {
            $table->string('asin')->nullable();
        });
        Schema::connection('mysql2')->table('inventory_adjustment_reports', function($table) {
            $table->string('asin')->nullable();
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
        Schema::connection('mysql2')->table('settlement_reports', function($table) {
            $table->dropColumn('asin');
        });
        Schema::connection('mysql2')->table('inventory_adjustment_reports', function($table) {
            $table->dropColumn('asin');
        });
    }
}
