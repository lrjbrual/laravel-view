<?php

use Illuminate\Database\Seeder;

class CampaignTriggerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('campaign_triggers')->insert(array(
      array(
          'id' => '1',
          'description' => 'Confirmed',
      ),
        array(
          'id' => '2',
          'description' => 'Shipped',
      ),
      array(
          'id' => '3',
          'description' => 'Delivered',
      )
    ));
    }
}
