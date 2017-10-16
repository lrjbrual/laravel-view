<?php

use Illuminate\Database\Seeder;

class MarketplaceCountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('marketplace_countries')->insert(array(
      array(
          'id' => '1',
          'marketplace_id' => '1',
          'country_id' => '840',
      ),
      array(
          'id' => '2',
          'marketplace_id' => '1',
          'country_id' => '124',
      ),
      array(
          'id' => '3',
          'marketplace_id' => '2',
          'country_id' => '826',
      ),
      array(
          'id' => '4',
          'marketplace_id' => '2',
          'country_id' => '250',
      ),
      array(
          'id' => '5',
          'marketplace_id' => '2',
          'country_id' => '276',
      ),
      array(
          'id' => '6',
          'marketplace_id' => '2',
          'country_id' => '380',
      ),
      array(
          'id' => '7',
          'marketplace_id' => '2',
          'country_id' => '724',
      ),
    )
    );
    }
}
