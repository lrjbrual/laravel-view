<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;

class EmailTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('email_tags')->delete(); // delete if exist

        DB::table('email_tags')->insert([
            'description' => 'Buyer Name',
            'icon' => 'fa fa-user',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('email_tags')->insert([
            'description' => 'Product Name',
            'icon' => 'fa fa-dropbox',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('email_tags')->insert([
            'description' => 'Order ID',
            'icon' => 'fa fa-shopping-cart',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('email_tags')->insert([
            'description' => 'Product Review Link',
            'icon' => 'fa fa-link',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('email_tags')->insert([
            'description' => 'Estimated Arrival Date',
            'icon' => 'fa fa-calendar',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('email_tags')->insert([
            'description' => 'Order Date',
            'icon' => 'fa fa-calendar',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
