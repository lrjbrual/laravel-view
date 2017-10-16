<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemrelatedToSettlementReports extends Migration
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
            $table->string('item_related_fee_type')->nullable();
            $table->double('item_related_fee_amount')->default(0)->nullable();
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
            $table->dropColumn('item_related_fee_type');
            $table->dropColumn('item_related_fee_amount');
        });
    }
}
