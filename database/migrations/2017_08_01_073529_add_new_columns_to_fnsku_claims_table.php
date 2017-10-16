<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToFnskuClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('fnsku_claims', function (Blueprint $table) { 
            $table->integer('six')->after('five');
            $table->string('reimbursed_reasons')->after('reimbursed_units');
            $table->integer('items_lost')->after('reimbursed_reasons');
            $table->integer('items_damaged')->after('items_lost');
            $table->boolean('is_third_scenario')->after('items_damaged');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('fnsku_claims', function (Blueprint $table) {
            $table->dropColumn('six');
            $table->dropColumn('reimbursed_reasons');
            $table->dropColumn('items_lost');
            $table->dropColumn('items_damaged');
            $table->dropColumn('is_third_scenario');
        });
    }
}
