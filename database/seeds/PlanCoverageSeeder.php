<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;

class PlanCoverageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('plan_coverages')->delete();

        $plans = DB::table('plans')->get();

        foreach($plans as $plan){
          if ($plan->size == "XS") {
            DB::table('plan_coverages')->insert([
                'plan_id' => $plan->id,
                'coverage' => "Up to 250 listings<br>Email customer<br>Either EU marketplace or NA marketplaces",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
          } else if ($plan->size == "S") {
            DB::table('plan_coverages')->insert([
                'plan_id' => $plan->id,
                'coverage' => "Up to 1,000 listings<br>Email customer<br>Either EU marketplace or NA marketplaces",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
          } else if ($plan->size == "M") {
            DB::table('plan_coverages')->insert([
                'plan_id' => $plan->id,
                'coverage' => "Up to 4,000 listings<br>Email customer<br>Either EU marketplace or NA marketplaces",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
          } else if ($plan->size == "L") {
            DB::table('plan_coverages')->insert([
                'plan_id' => $plan->id,
                'coverage' => "Up to 10,000 listings<br>Email customer<br>Either EU marketplace or NA marketplaces",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
          } else if ($plan->size == "XL") {
            DB::table('plan_coverages')->insert([
                'plan_id' => $plan->id,
                'coverage' => "Up to 20,000 listings<br>Email customer<br>Either EU marketplace or NA marketplaces",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
          }

        }

    }
}
