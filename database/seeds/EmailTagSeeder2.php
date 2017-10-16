<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EmailTagSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('email_tags')->insert([
            'description' => 'Product Image',
            'icon' => 'fa fa-picture-o',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
