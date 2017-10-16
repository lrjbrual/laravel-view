<?php

use Illuminate\Database\Seeder;

class InitialSellerSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('sellers')->insert([

          'id' => '1',
          'firstname' => 'Admin',
          'lastname' => 'LS',
          'email' => 'admin@ls.com',
          'company' => 'Trendle',
          'address' => '',
          'city' => '',
          'state' => '',
          'zipcode' => '',
          'country_id' => null,
          'phone' => '',
          'is_Deleted' => '0',
          'reason_for_leaving' => '',
          'email_for_crm' => '',
          'emailpw_for_crm' => ''
      ]);

      DB::table('trial_periods')->insert([
        'seller_id' => 1,
        'date_registered' => '2017-01-01 00:00:00',
        'trial_start_date' => '2017-01-01 00:00:00',
        'trial_end_date' =>  '2030-01-01 00:00:00',
        'is_activated' => 1,
        'date_activated' => '2017-01-01 00:00:00'
      ]);


      DB::table('users')->insert([
        'email' => 'admin@ls.com',
        'password' => bcrypt('123'),
        'access' => 1,
        'is_verified' => 1,
        'is_inHouse' => 1,
        'is_admin' => 1,
        'is_active' => 1,
        'seller_id' => 1
      ]);

      // New Admin User with valid email for Unit Testing
      DB::table('sellers')->insert([

          'id' => '2',
          'firstname' => 'Unit',
          'lastname' => 'Tester',
          'email' => 'junrhy@locksoftwares.co.uk',
          'company' => 'Trendle',
          'address' => '',
          'city' => '',
          'state' => '',
          'zipcode' => '',
          'country_id' => 826,
          'phone' => '',
          'is_Deleted' => '0',
          'reason_for_leaving' => '',
          'email_for_crm' => '',
          'emailpw_for_crm' => ''
      ]);

      DB::table('trial_periods')->insert([
        'seller_id' => 2,
        'date_registered' => '2017-01-01 00:00:00',
        'trial_start_date' => '2017-01-01 00:00:00',
        'trial_end_date' =>  '2030-01-01 00:00:00',
        'is_activated' => 1,
        'date_activated' => '2017-01-01 00:00:00'
      ]);


      DB::table('users')->insert([
        'email' => 'junrhy@locksoftwares.co.uk',
        'password' => bcrypt('123'),
        'access' => 1,
        'is_verified' => 1,
        'is_inHouse' => 1,
        'is_admin' => 1,
        'is_active' => 1,
        'seller_id' => 2
      ]);
    }
}
