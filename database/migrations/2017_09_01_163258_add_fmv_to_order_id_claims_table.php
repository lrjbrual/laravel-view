<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFmvToOrderIdClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('order_id_claims', function (Blueprint $table) {
            $table->double('fmv_sales');
            $table->double('fmv_cost');
            $table->integer('fmv_quantity');
            $table->double('fmv');
            $table->double('fmv3_sales');
            $table->double('fmv3_cost');
            $table->integer('fmv3_quantity');
            $table->double('fmv3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('order_id_claims', function (Blueprint $table) {
            $table->dropColumn('fmv_sales');
            $table->dropColumn('fmv_cost');
            $table->dropColumn('fmv_quantity');
            $table->dropColumn('fmv');
            $table->dropColumn('fmv3_sales');
            $table->dropColumn('fmv3_cost');
            $table->dropColumn('fmv3_quantity');
            $table->dropColumn('fmv3');
        });
    }
}
