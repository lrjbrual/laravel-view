<?php
// Created By: Jun Rhy Crodua
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\Billing;
use Stripe\Token as StripeToken;

class AutomaticEmailsTest extends TestCase
{
    use DatabaseMigrations;
    // use DatabaseTransactions;

    public function setUp(){
        parent::setUp();
        $this->seed('DatabaseSeeder');
    }

    public function tearDown(){
        parent::tearDown();
    }

    /**
     * @group automatic_emails
     * @return void
     */
    public function testCreateCampaignIfBillingPresent()
    {
        $user =  User::where('email','=', 'admin@ls.com')->first();
        $this->actingAs($user);

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

        $campaign = array(
            '_token' => csrf_token(),
            'campaignname' => 'Campaign 1',
            'mkp' => array(1,2)
        );

        $response = $this->call('POST', 'campaign/savecampaign', $campaign);
        $response->assertStatus(200);

        $this->assertDatabaseHas('trendleio2_test.campaigns', [
            'seller_id' => '1',
            'campaign_type' => '1',
            'campaign_name' => 'Campaign 1'
        ]);
    }

    /**
     * @group automatic_emails
     * @return void
     */
    public function testCreateTemplate()
    {
        $user =  User::where('email','=', 'admin@ls.com')->first();
        $this->actingAs($user);

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

        $template = array(
            '_token' => csrf_token(),
            'templatename' => 'Template 1',
            'delayval' => 0,
            'subject' => 'Template 1',
            'body' => 'Test Body',
            'mode' => 'savetemp',
            'cid' => 0,
            'tid' => 0,
            'isactive' => 0,
            'loadmode' => 'new'
        );

        $response = $this->call('POST', 'campaign/savetemplate', $template);
        $response->assertStatus(200);

        $this->assertDatabaseHas('trendleio2_test.campaign_emails', [
            'campaign_id' => 0,
            'template_name' => 'Template 1',
            'days_delay' => 0,
            'subject' => 'Template 1',
            'email_body' => 'Test Body',
            'is_active' => 0,
        ]);
    }
}
