<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrecalcIndex1 extends Migration
{
    public function up()
    {
      //
      // Schema::connection('mysql2')->table('products', function (Blueprint $table) {
      //   $table->string('asin',250)->change();
      //   $table->string('sku',250)->change();
      // });

      Schema::connection('mysql2')->table('fba_refund_trans', function (Blueprint $table) {
        $cols=array(
          'amount_reimbursed',
          'seller_id',
          'created_at',
        );
        $table->index($cols,'precalc_index001');
      });

      Schema::connection('mysql2')->table('reimbursements', function (Blueprint $table) {
        $cols=array(
          'reason',
          'approval_date',
          'seller_id',
          'currency_unit',
          'original_reimbursement_id',
          'reimbursement_id',
        );
        $table->index($cols,'precalc_index002');

        $cols=array(
          'amazon_order_id',
        );
        $table->index($cols,'precalc_index012');

      });
      Schema::connection('mysql2')->table('fba_pre_calculations', function (Blueprint $table) {
        $cols=array(
          'seller_id',
          'created_at',
          'country_code',
          'status',
        );
        $table->index($cols,'precalc_index003');
      });
      Schema::table('trendle_checkers', function (Blueprint $table) {
        $cols=array(
          'seller_id',
          'checker_name',
          'checker_country',
        );
        $table->index($cols,'precalc_index004');
      });
      Schema::connection('mysql2')->table('financial_events_reports', function (Blueprint $table) {
        $cols=array(
          'seller_id',
          'marketplace_name',
          'type',
          'price_type',
          'order_id',
          'posted_date',
        );
        $table->index($cols,'precalc_index005');

        $cols=array(
          'type',
          'order_id',
          'posted_date',
        );
        $table->index($cols,'precalc_index006');

        $cols=array(
          'type',
          'order_id',
          'asin',
          'quantity',
        );
        $table->index($cols,'precalc_index007');

        $cols=array(
          'type',
          'asin',
          'posted_date',
          'marketplace_name',
        );
        $table->index($cols,'precalc_index008');

        $cols=array(
          'type',
          'asin',
          'marketplace_name',
        );
        $table->index($cols,'precalc_index009');

        $cols=array(
          'order_id',
          'type',
        );
        $table->index($cols,'precalc_index010');

        $cols=array(
          'order_id',
          'type',
          'price_type',
        );
        $table->index($cols,'precalc_index011');

        $cols=array(
          'order_id',
          'type',
          'promotional_rebates',
        );
        $table->index($cols,'precalc_index016');

      });

      Schema::connection('mysql2')->table('returns_reports', function (Blueprint $table) {
        $cols=array(
          'order_id',
          'country',
        );
        $table->index($cols,'precalc_index013');
      });
      Schema::connection('mysql2')->table('order_id_claims', function (Blueprint $table) {
        $cols=array(
          'order_id',
        );
        $table->index($cols,'precalc_index014');
      });

      Schema::connection('mysql2')->table('flat_file_all_orders_by_dates', function (Blueprint $table) {
        $cols=array(
          'amazon_order_id',
        );
        $table->index($cols,'precalc_index015');
      });


    }

    public function down()
    {
      Schema::connection('mysql2')->table('fba_refund_trans', function (Blueprint $table) {
         $table->dropIndex('precalc_index001');
      });
      Schema::connection('mysql2')->table('reimbursements', function (Blueprint $table) {
        $table->dropIndex('precalc_index002');
        $table->dropIndex('precalc_index012');
      });
      Schema::connection('mysql2')->table('fba_pre_calculations', function (Blueprint $table) {
        $table->dropIndex('precalc_index003');
      });
      Schema::table('trendle_checkers', function (Blueprint $table) {
        $table->dropIndex('precalc_index004');
      });
      Schema::connection('mysql2')->table('financial_events_reports', function (Blueprint $table) {
        $table->dropIndex('precalc_index005');
        $table->dropIndex('precalc_index006');
        $table->dropIndex('precalc_index007');
        $table->dropIndex('precalc_index008');
        $table->dropIndex('precalc_index009');
        $table->dropIndex('precalc_index010');
        $table->dropIndex('precalc_index011');
        $table->dropIndex('precalc_index016');
      });
      Schema::connection('mysql2')->table('returns_reports', function (Blueprint $table) {
        $table->dropIndex('precalc_index013');
      });
      Schema::connection('mysql2')->table('order_id_claims', function (Blueprint $table) {
        $table->dropIndex('precalc_index014');
      });
      Schema::connection('mysql2')->table('flat_file_all_orders_by_dates', function (Blueprint $table) {
        $table->dropIndex('precalc_index015');
      });


      // Schema::connection('mysql2')->table('products', function (Blueprint $table) {
      //   $table->text('asin')->change();
      //   $table->text('sku')->change();
      // });
    }
}
