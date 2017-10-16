<?php
// Created By: Jun Rhy Crodua
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;

use App\Mail\FbaCSEmailNotif;

class RegistrationTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(){
        parent::setUp();
        $this->seed('DatabaseSeeder');
    }
    public function tearDown(){
        parent::tearDown();
    }

    /** @group registration */
    public function testSellerCanRegister()
    {
        Mail::fake();

        $data = array(
            'fname' => 'John',
            'lname' => 'Doe',
            'company' => 'Trendle',
            'country_id' => 380,
            'company' => 'Trendle',
            'email' => 'johndoe@yopmail.com',
            'password' => '1233456',
            'password_confirmation' => '1233456',
            '_token' => csrf_token()
        );

        $response = $this->call('POST', 'register', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@yopmail.com'
        ]);

        $this->assertDatabaseHas('sellers', [
            'email' => 'johndoe@yopmail.com'
        ]);

        $this->assertDatabaseHas('registration_tokens', [
            'email' => 'johndoe@yopmail.com'
        ]);

        Mail::assertSent(FbaCSEmailNotif::class, function ($mail) {
            return $mail->hasTo('customerservice@trendle.io');
        });

        $this->assertDatabaseHas('trial_periods', [
            'seller_id' => 2
        ]);

        $this->assertDatabaseHas('todos', [
            'item' => 'Enter API Authorisations credentials in "Marketplace"',
            'item' => 'Manage my Subscriptions',
            'item' => 'Enter VAT ID (if applicable), complete Billing Address and enter a payment method before Free Trial ends',
            'item' => 'Create first Automatic Email Campaign!',
            'item' => 'See how much money Amazon owes me under "FBA Refunds"',
            'item' => 'Work through any negative Seller Reviews',
        ]);
    }
}
