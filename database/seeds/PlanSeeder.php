<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('plans')->delete();

        $collection = collect([
          ['size' => 'XS', 'amount' => 1800, 'load' => '250'],
          ['size' => 'S', 'amount' => 3500, 'load' => '1000'],
          ['size' => 'M', 'amount' => 5200, 'load' => '4000'],
          ['size' => 'L', 'amount' => 6900, 'load' => '10000'],
          ['size' => 'XL', 'amount' => 17300, 'load' => '20000'],
        ]);

        // CRM Plans
        $this->pillar = DB::table('pillars')->where('name', 'Automatic customer emails')->first();

        $collection->each(function ($item, $key) {
            DB::table('plans')->insert([
                'pillar_id' => $this->pillar->id,
                'size' => $item['size'],
                'load' => $item['load'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        });

        // Data Analytics Plans
        // $this->pillar = DB::table('pillars')->where('name', 'Data Analytics')->first();
        //
        // $collection->each(function ($item, $key) {
        //     DB::table('plans')->insert([
        //         'pillar_id' => $this->pillar->id,
        //         'size' => $item['size'],
        //         'country_id' => 826,
        //         'amount' => $item['amount'],
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now()
        //     ]);
        // });

        // Reviews Plans
        // $this->pillar = DB::table('pillars')->where('name', 'Reviews')->first();
        //
        // $collection->each(function ($item, $key) {
        //     DB::table('plans')->insert([
        //         'pillar_id' => $this->pillar->id,
        //         'size' => $item['size'],
        //         'country_id' => 826,
        //         'amount' => $item['amount'],
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now()
        //     ]);
        // });
    }
}
