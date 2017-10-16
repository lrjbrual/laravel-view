<?php

use Illuminate\Database\Seeder;

class CronMasterListSeeder6 extends Seeder
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
  		  'description' => 'Product Image',
  		  'route' => 'UpdateProductImage',
  		  'sequence' => 14,
          'is_seller_cron' => 1
        ]);
    }
}
