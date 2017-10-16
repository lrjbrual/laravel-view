<?php

use Illuminate\Database\Seeder;

class CronMasterListSeeder5 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $id = DB::table('cron_master_lists')->insert([
  		  'description' => 'Product Reviews',
  		  'route' => 'ProductReviews',
  		  'sequence' => 0,
          'is_seller_cron' => 0
        ]);
        
        DB::table('seller_cron_schedules')->insertGetId([
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
    }
}
