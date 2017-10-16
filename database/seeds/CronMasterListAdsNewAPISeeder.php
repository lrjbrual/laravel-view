<?php

use Illuminate\Database\Seeder;

class CronMasterListAdsNewAPISeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cron_master_lists')->insert([
  		  'description' => 'Update Product Prices',
  		  'route' => 'UpdateProductsPrice',
  		  'sequence' => 11
  		]);
        DB::table('cron_master_lists')->insert([
  		  'description' => 'Update Product Estimate Fees',
  		  'route' => 'UpdateProductsEstimateFees',
  		  'sequence' => 12
  		]);
        DB::table('cron_master_lists')->insert([
  		  'description' => 'Update Advertising Campaigns New API',
  		  'route' => 'UpdateAdvertCampaigns',
  		  'sequence' => 0
  		]);
        DB::table('cron_master_lists')->insert([
  		  'description' => 'Extract Advertising Campaigns Report New API',
  		  'route' => 'ExtractAdvertCampaigns',
  		  'sequence' => 0
  		]);
    }
}
