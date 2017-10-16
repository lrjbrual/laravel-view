<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;

class PillarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('pillars')->delete(); // delete if exist

      DB::table('pillars')->insert([
          'name' => 'Home',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);

      DB::table('pillars')->insert([
          'name' => 'Inventory',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);

      DB::table('pillars')->insert([
          'name' => 'Sales',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);

      DB::table('pillars')->insert([
          'name' => 'Customer Service',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);

      DB::table('pillars')->insert([
          'name' => 'Data Analytics',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);

      DB::table('pillars')->insert([
          'name' => 'Reviews',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);

      DB::table('pillars')->insert([
          'name' => 'Automatic customer emails',
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
      ]);
    }
}
