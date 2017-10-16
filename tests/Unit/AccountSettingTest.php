<?php
// Created By: Jun Rhy Crodua
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;

use App\User;
use App\Billing;
use Stripe\Token as StripeToken;
use Artisan;

class AccountSettingTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(){
        parent::setUp();
        $this->seed('DatabaseSeeder');
        Artisan::call('currency:manage', ['action' => 'add', 'currency' => 'gbp,eur,usd,cad']);
        Artisan::call('currency:update', ['--openexchangerates' => 'default']);
    }

    public function tearDown(){
        parent::tearDown();
    }

    /** @group account_settings */
    public function testAccountSettings()
    {
        $user =  User::where('email','=', 'admin@ls.com')->first();
        $this->actingAs($user);

        $company = array (
            'firstname' => 'Jane',
            'lastname' => 'Smith',
        );

        $response = $this->call('PUT', 'company/1', $company);
        $response->assertRedirect('company');
        $response->assertStatus(302);

        $this->assertDatabaseHas('sellers', [
            'firstname' => 'Jane',
            'lastname' => 'Smith'
        ]);

        
    }
}
