<?php

use Illuminate\Database\Seeder;

class CronMasterListSeeder4 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('cron_master_lists')->insert([
  		  'description' => 'Flat File All Orders By Date',
  		  'route' => 'UpdateFlatFileAllOrdersByDate',
  		  'sequence' => 13
  		]);
    }
}
