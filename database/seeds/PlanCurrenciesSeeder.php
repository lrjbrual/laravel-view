<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;

class PlanCurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('plan_currencies')->delete();

        $collection = collect([
          ['plan_id' => 1, 'country_id' => 826, 'amount' => 500],
          ['plan_id' => 2, 'country_id' => 826, 'amount' => 1000],
          ['plan_id' => 3, 'country_id' => 826, 'amount' => 2500],
          ['plan_id' => 4, 'country_id' => 826, 'amount' => 4000],
          ['plan_id' => 5, 'country_id' => 826, 'amount' => 6000],
          ['plan_id' => 1, 'country_id' => 724, 'amount' => 600],
          ['plan_id' => 2, 'country_id' => 724, 'amount' => 1200],
          ['plan_id' => 3, 'country_id' => 724, 'amount' => 3000],
          ['plan_id' => 4, 'country_id' => 724, 'amount' => 4700],
          ['plan_id' => 5, 'country_id' => 724, 'amount' => 7000],
          ['plan_id' => 1, 'country_id' => 840, 'amount' => 700],
          ['plan_id' => 2, 'country_id' => 840, 'amount' => 1300],
          ['plan_id' => 3, 'country_id' => 840, 'amount' => 3200],
          ['plan_id' => 4, 'country_id' => 840, 'amount' => 5000],
          ['plan_id' => 5, 'country_id' => 840, 'amount' => 7500],
        ]);

        $collection->each(function ($item, $key) {
            DB::table('plan_currencies')->insert([
                'plan_id' => $item['plan_id'],
                'country_id' => $item['country_id'],
                'amount' => $item['amount'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        });
    }
}
