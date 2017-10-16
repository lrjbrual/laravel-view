<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToSettlementReports extends Migration
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
            $table->string('shipment_fee_type')->nullable();
            $table->double('shipment_fee_amount')->default(0)->nullable();
            $table->string('order_fee_type')->nullable();
            $table->double('order_fee_amount')->default(0)->nullable();
            $table->string('order_item_code')->nullable();
            $table->string('merchant_order_item_id')->nullable();
            $table->string('merchant_adjustment_item_id')->nullable();
            $table->string('direct_payment_type')->nullable();
            $table->double('direct_payment_amount')->default(0)->nullable();
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
            $table->dropColumn('shipment_fee_type');
            $table->dropColumn('shipment_fee_amount');
            $table->dropColumn('order_fee_type');
            $table->dropColumn('order_fee_amount');
            $table->dropColumn('order_item_code');
            $table->dropColumn('merchant_order_item_id');
            $table->dropColumn('merchant_adjustment_item_id');
            $table->dropColumn('direct_payment_type');
            $table->dropColumn('direct_payment_amount');
        });
    }
}
