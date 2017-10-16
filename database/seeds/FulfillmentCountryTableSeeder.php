<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FulfillmentCountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql2')->table('fulfillment_country')->delete();

        $collection = collect([
			['fulfillment_center_id' => 'PRG1', 'country_code' => 'CZ'],
			['fulfillment_center_id' => 'PRG2', 'country_code' => 'CZ'],
			['fulfillment_center_id' => 'FRA1', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'LEJ1', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'FRA3', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'DUS2', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'EDE4', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'EDE5', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'MUC3', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'STR1', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'CGN1', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'BER3', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'HAM2', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'DTM1', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'DTM2', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'FRA7', 'country_code' => 'DE'],
			['fulfillment_center_id' => 'MAD4', 'country_code' => 'ES'],
			['fulfillment_center_id' => 'BCN1', 'country_code' => 'ES'],
			['fulfillment_center_id' => 'LIL1', 'country_code' => 'FRA'],
			['fulfillment_center_id' => 'MRS1', 'country_code' => 'FRA'],
			['fulfillment_center_id' => 'ORY1', 'country_code' => 'FRA'],
			['fulfillment_center_id' => 'LYS1', 'country_code' => 'FRA'],
			['fulfillment_center_id' => 'MXP5', 'country_code' => 'IT'],
			['fulfillment_center_id' => 'WRO1', 'country_code' => 'PL'],
			['fulfillment_center_id' => 'POZ1', 'country_code' => 'PL'],
			['fulfillment_center_id' => 'WRO2', 'country_code' => 'PL'],
			['fulfillment_center_id' => 'SZZ1', 'country_code' => 'PL'],
			['fulfillment_center_id' => 'BHX1', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'BHX3', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'CWL1', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'EDI4', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'EUK5', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'LTN1', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'LTN2', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'LTN4', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'MAN1', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'GLA1', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'LBA1', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'LBA2', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'LBA3', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'XUKD', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'LTN3', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'EMA1', 'country_code' => 'UK'],
			['fulfillment_center_id' => 'LCY1', 'country_code' => 'UK'],
        ]);

        $collection->each(function ($item, $key) {
            DB::connection('mysql2')->table('fulfillment_country')->insert([
                'fulfillment_center_id' => $item['fulfillment_center_id'],
                'country_code' => $item['country_code'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        });
    }
}
