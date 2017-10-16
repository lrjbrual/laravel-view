<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReimbursedUnitsToFnskuClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('fnsku_claims', function (Blueprint $table) {
            $table->integer('reimbursed_units')->default(0)->after('summation');
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
            $table->dropColumn('reimbursed_units');
        });
    }
}
