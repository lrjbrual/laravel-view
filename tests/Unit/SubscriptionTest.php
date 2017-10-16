<?php
// Created By: Jun Rhy Crodua
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Billing;
use Stripe\Token as StripeToken;
use Artisan;

class SubscriptionTest extends TestCase
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

    private function setPaymentDetails()
    {
        $stripe_key = Billing::getStripeKey();

        $token_params = array(
            "card" => array(
                "number" => "4242424242424242",
                "exp_month" => 5,
                "exp_year" => 2019,
                "cvc" => "123"
            )
        );

        $token = StripeToken::create($token_params, ['api_key' => $stripe_key]);

        $card = array(
            'firstname' => 'John',
            'lastname' => 'Doe',
            'company' => 'Trendle Inc.',
            'address1' => '123 Main St.',
            'city' => 'Any City',
            'postal_code' => '123456',
            'country_id' => '533',
            'card_number' => '4242424242424242',
            'card_holder_name' => 'John Doe',
            'expiry_month' => '05',
            'expiry_year' => '2019',
            'cvv' => '123',
            'stripeToken' => $token->id,
            '_token' => csrf_token()
        );

        $response = $this->call('POST', 'billing', $card);
        $response->assertStatus(302);

        $response = $this->call('POST', 'billing/registerCard', $card);
        $response->assertStatus(302);

        $preferred_currency = array (
            'preferred_currency' => 'usd',
        );

        $response = $this->call('POST', 'billing/storePreferredPayment', $preferred_currency);
        $response->assertStatus(302);

        $this->assertDatabaseHas('billings', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'company' => 'Trendle Inc.',
            'address1' => '123 Main St.',
            'city' => 'Any City',
            'postal_code' => '123456',
            'country_id' => '533',
            'card_holder_name' => 'John Doe',
            'card_expiry_month' => '05',
            'card_expiry_year' => '2019',
            'card_brand' => 'Visa',
            'payment_method' => 'card',
            'card_last_four' => '4242',
            'preferred_currency' => 'usd'
        ]);
    }

    /**
     * @group subscription
     * @return void
     */
    public function testCreateBaseSubscription()
    {
        $user =  User::where('email','=', 'admin@ls.com')->first();
        $this->actingAs($user);

        $this->setPaymentDetails();

        $subscription = array (
            'base_subscription' => 'XS',
        );

        $response = $this->call('POST', 'selectBaseSubscription', $subscription);
        $response->assertStatus(302);

        $this->assertDatabaseHas('base_subscription_seller_transactions', [
            'bs_name' => 'XS',
            'bonus_mail' => '100',
            'amount_to_pay' => '20',
            'currently_used' => '1'
        ]);

        $subscription = array (
            'base_subscription' => 'S',
        );

        $response = $this->call('POST', 'selectBaseSubscription', $subscription);
        $response->assertStatus(302);

        $this->assertDatabaseHas('base_subscription_seller_transactions', [
            'bs_name' => 'S',
            'bonus_mail' => '300',
            'amount_to_pay' => '50',
            'currently_used' => '1'
        ]);

        $subscription = array (
            'base_subscription' => 'M',
        );

        $response = $this->call('POST', 'selectBaseSubscription', $subscription);
        $response->assertStatus(302);

        $this->assertDatabaseHas('base_subscription_seller_transactions', [
            'bs_name' => 'M',
            'bonus_mail' => '2000',
            'amount_to_pay' => '100',
            'currently_used' => '1'
        ]);

        $subscription = array (
            'base_subscription' => 'L',
        );

        $response = $this->call('POST', 'selectBaseSubscription', $subscription);
        $response->assertStatus(302);

        $this->assertDatabaseHas('base_subscription_seller_transactions', [
            'bs_name' => 'L',
            'bonus_mail' => '5000',
            'amount_to_pay' => '200',
            'currently_used' => '1'
        ]);

        $subscription = array (
            'base_subscription' => 'XL',
        );

        $response = $this->call('POST', 'selectBaseSubscription', $subscription);
        $response->assertStatus(302);

        $this->assertDatabaseHas('base_subscription_seller_transactions', [
            'bs_name' => 'XL',
            'bonus_mail' => '10000',
            'amount_to_pay' => '400',
            'currently_used' => '1'
        ]);
    }

    /**
     * @group subscription
     * @return void
     */
    public function testBuyEmailPack()
    {
        $user =  User::where('email','=', 'junrhy@locksoftwares.co.uk')->first();
        $this->actingAs($user);

        $this->setPaymentDetails();

        $subscription = array (
            'planSize' => 'S',
            'amount' => 500
        );

        $response = $this->call('POST', 'subscription/subscribe', $subscription);
        $response->assertStatus(200);

        $this->assertDatabaseHas('crm_loads', [
            'seller_id' => 2,
            'credit' => 250,
        ]);

        $subscription = array (
            'planSize' => 'M',
            'amount' => 1000
        );

        $response = $this->call('POST', 'subscription/subscribe', $subscription);
        $response->assertStatus(200);

        $this->assertDatabaseHas('crm_loads', [
            'seller_id' => 2,
            'credit' => 2250,
        ]);

        $subscription = array (
            'planSize' => 'L',
            'amount' => 2500
        );

        $response = $this->call('POST', 'subscription/subscribe', $subscription);
        $response->assertStatus(200);

        $this->assertDatabaseHas('crm_loads', [
            'seller_id' => 2,
            'credit' => 12250,
        ]);
    }
}
