<?php

use Illuminate\Database\Seeder;

class CronMasterListSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('cron_master_lists')
      ->where('route', 'FbaRefundsPreCalculation')
      ->update(['sequence' => 9,'is_seller_cron' => true]);


      DB::table('seller_cron_schedules')->where('id', '=', 3)->delete();

    }
}
