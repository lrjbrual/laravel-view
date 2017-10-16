<?php

use Illuminate\Database\Seeder;

class MarketplaceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('marketplaces')->insert(array(
      array(
          'id' => '1',
          'marketplace_name' => 'North America',
      ),
        array(
          'id' => '2',
          'marketplace_name' => 'Europe',
      ),
      array(
          'id' => '3',
          'marketplace_name' => 'India',
      ),
        array(
          'id' => '4',
          'marketplace_name' => 'Japan',
      )
    ));
    }
}
