<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(InitialSellerSeed::class);
        $this->call(MarketplaceTableSeeder::class);
        $this->call(MarketplaceCountryTableSeeder::class);
        $this->call(PlanCurrenciesSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->command->info('Seeded the countries!');
        $this->call(PillarSeeder::class);
        $this->command->info('Seeded the pillars!');
        $this->call(PlanSeeder::class);
        $this->command->info('Seeded the plans!');
        $this->call(PlanCoverageSeeder::class);
        $this->command->info('Seeded the plan coverages!');
        $this->call(EmailTagSeeder::class);
        $this->call(EmailTagSeeder2::class);
        $this->command->info('Seeded the email tags!');
        $this->call(CampaignTriggerSeeder::class);
        // $this->command->info('Seeded the countries!');
        $this->command->info('Seeded the Cron Master List!');
        $this->call(CronMasterListSeeder::class);
        $this->command->info('Seeded the Cron Master List 2!');
        $this->call(CronMasterListSeeder2::class);
        $this->command->info('Seeded the Cron Master List 3!');
        $this->call(CronMasterListSeeder3::class);
        $this->command->info('Seeded the Cron Master List Ads New API!');
        $this->call(CronMasterListAdsNewAPISeeder::class);
        $this->command->info('Seeded the Cron Master List 4!');
        $this->call(CronMasterListSeeder4::class);
        $this->command->info('Seeded the Cron Master List 5!');
        $this->call(CronMasterListSeeder5::class);
        $this->command->info('Seeded the Cron Master List 6!');
        $this->call(CronMasterListSeeder6::class);

        $this->command->info('Seeded the FulfillmentCountryTableSeeder!');
        $this->call(FulfillmentCountryTableSeeder::class);


    }
}
