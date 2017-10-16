<?php

use Illuminate\Database\Seeder;

class CronMasterListSeeder3 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('cron_master_lists')->insert([
  		  'description' => 'Advertising Performance',
  		  'route' => 'UpdateCampaignAdvertising',
  		  'sequence' => 10
  		]);
    }
}
