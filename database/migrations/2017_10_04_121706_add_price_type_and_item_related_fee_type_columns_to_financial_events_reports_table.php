<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceTypeAndItemRelatedFeeTypeColumnsToFinancialEventsReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('financial_events_reports', function (Blueprint $table) {
            $table->double('pt_export_charge')->default(0)->nullable();
            $table->double('pt_generic_deduction')->default(0)->nullable();
            $table->double('pt_gift_wrap')->default(0)->nullable();
            $table->double('pt_gift_wrap_tax')->default(0)->nullable();
            $table->double('pt_goodwill')->default(0)->nullable();
            $table->double('pt_restocking_fee')->default(0)->nullable();
            $table->double('pt_return_shipping')->default(0)->nullable();
            $table->double('pt_shipping_tax')->default(0)->nullable();
            $table->double('irft_commission')->default(0)->nullable();
            $table->double('irft_gift_wrap_chargeback')->default(0)->nullable();
            $table->double('irft_refund_commission')->default(0)->nullable();
            $table->double('irft_sales_tax_collection_fee')->default(0)->nullable();
            $table->double('irft_shipping_charge_back')->default(0)->nullable();
            $table->double('irft_shipping_hb')->default(0)->nullable();
            $table->double('irft_fba_per_order_fulfillmen_fee')->default(0)->nullable();
            $table->double('irft_fba_per_unit_fulfillmen_fee')->default(0)->nullable();
            $table->double('irft_fba_weight_based_fee')->default(0)->nullable();
            $table->double('irft_fixed_closing_fee')->default(0)->nullable();
            $table->double('irft_get_paid_faster_fee')->default(0)->nullable();
            $table->double('irft_gift_wrap_commission')->default(0)->nullable();
            $table->double('irft_variable_closing_fee')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('financial_events_reports', function (Blueprint $table) {            
            $table->dropColumn('pt_export_charge');
            $table->dropColumn('pt_generic_deduction');
            $table->dropColumn('pt_gift_wrap');
            $table->dropColumn('pt_gift_wrap_tax');
            $table->dropColumn('pt_goodwill');
            $table->dropColumn('pt_restocking_fee');
            $table->dropColumn('pt_return_shipping');
            $table->dropColumn('pt_shipping_tax');
            $table->dropColumn('irft_commission');
            $table->dropColumn('irft_gift_wrap_chargeback');
            $table->dropColumn('irft_refund_commission');
            $table->dropColumn('irft_sales_tax_collection_fee');
            $table->dropColumn('irft_shipping_charge_back');
            $table->dropColumn('irft_shipping_hb');
            $table->dropColumn('irft_fba_per_order_fulfillmen_fee');
            $table->dropColumn('irft_fba_per_unit_fulfillmen_fee');
            $table->dropColumn('irft_fba_weight_based_fee');
            $table->dropColumn('irft_fixed_closing_fee');
            $table->dropColumn('irft_get_paid_faster_fee');
            $table->dropColumn('irft_gift_wrap_commission');
            $table->dropColumn('irft_variable_closing_fee');
        });
    }
}
