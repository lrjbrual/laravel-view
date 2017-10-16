<?php

use Illuminate\Database\Seeder;

class CronMasterListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cron_master_lists')->delete(); // delete if exist
        DB::table('cron_master_lists')->truncate();
        //seed for seller crons
        //included in master cron will have sequence
		DB::table('cron_master_lists')->insert([
		  'description' => 'Products',
		  'route' => 'UpdateProductsDatabase',
		  'sequence' => 1
		]);

		//for refunds
		DB::table('cron_master_lists')->insert([
		  'description' => 'Inventory Data',
		  'route' => 'UpdateInventoryDataDatabase',
		  'sequence' => 2
		]);

		DB::table('cron_master_lists')->insert([
		  'description' => 'Reimbursement',
		  'route' => 'UpdateReimbursementDatabase',
		  'sequence' => 3
		]);

		DB::table('cron_master_lists')->insert([
		  'description' => 'Inventory Adjustments',
		  'route' => 'UpdateInventoryAdjustment',
		  'sequence' => 4
		]);

		DB::table('cron_master_lists')->insert([
		  'description' => 'Returns Report',
		  'route' => 'UpdateRuturnsReport',
		  'sequence' => 5
		]);

		DB::table('cron_master_lists')->insert([
		  'description' => 'Financial Events',
		  'route' => 'UpdateFinancialEvents',
		  'sequence' => 6
		]);

		// DB::table('cron_master_lists')->insert([
		//   'description' => 'Settlement Report',
		//   'route' => 'UpdateSettlementReport',
		//   'sequence' => 6
		// ]);
		//end for refunds

		DB::table('cron_master_lists')->insert([
		  'description' => 'Fulfilled Shipments',
		  'route' => 'UpdateFulfilledShipmentsDatabase',
		  'sequence' => 7
		]);

		DB::table('cron_master_lists')->insert([
		  'description' => 'Seller Reviews',
		  'route' => 'UpdateSellerReviewsDatabase',
		  'sequence' => 8
		]);

		//not included in master cron must have sequence 0
		DB::table('cron_master_lists')->insert([
		  'description' => 'CRM Auto Campaign',
		  'route' => 'CRMAutoCampaign',
		  'sequence' => 0
		]);

		//seed for non-seller cron and sequence 0
		//add is_seller_cron false
		$id = DB::table('cron_master_lists')->insertGetId([
		  'description' => 'Seller Trial Period Checker',
		  'route' => 'TrialPeriodChecker',
		  'sequence' => 0,
		  'is_seller_cron' => false
		]);
		DB::table('seller_cron_schedules')->insert([
			'cron_id' => $id,
			'seller_id' => 0,
			'minutes' => 0,
			'hours' => 1,
			'day_of_month' => '*',
			'month' => '*',
			'day_of_week' => '*',
			'date_created' => date('Y-m-d H:i:s'),
			'isactive' => true
		]);

		$id = DB::table('cron_master_lists')->insertGetId([
		  'description' => 'Stripe Payment Method',
		  'route' => 'StripePaymentForFBARefunds',
		  'sequence' => 0,
		  'is_seller_cron' => false
		]);
		DB::table('seller_cron_schedules')->insert([
			'cron_id'=> $id,
			'seller_id'=> 0,
			'minutes'=> 30,
			'hours'=> 1,
			'day_of_month'=> '*',
			'month' => '*',
			'day_of_week' => '*',
			'date_created' => date('Y-m-d H:i:s'),
			'isactive' => true
		]);

		$id = DB::table('cron_master_lists')->insertGetId([
		  'description' => 'FBA Refunds Pre-Calculation',
		  'route' => 'FbaRefundsPreCalculation',
		  'sequence' => 0,
		  'is_seller_cron' => false
		]);
		DB::table('seller_cron_schedules')->insert([
			'cron_id'=> $id,
			'seller_id'=> 0,
			'minutes'=> 0,
			'hours'=> 2,
			'day_of_month'=> '*',
			'month' => '*',
			'day_of_week' => '*',
			'date_created' => date('Y-m-d H:i:s'),
			'isactive' => true
		]);

		$id = DB::table('cron_master_lists')->insertGetId([
		  'description' => 'Populate Admin Sellers',
		  'route' => 'PopulateAdminSellers',
		  'sequence' => 0,
		  'is_seller_cron' => false
		]);
		DB::table('seller_cron_schedules')->insert([
			'cron_id'=> $id,
			'seller_id'=> 0,
			'minutes'=> 30,
			'hours'=> 2,
			'day_of_month'=> '*',
			'month' => '*',
			'day_of_week' => '*',
			'date_created' => date('Y-m-d H:i:s'),
			'isactive' => true
		]);

    }
}
