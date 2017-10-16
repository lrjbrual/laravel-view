<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Paypal;
use Redirect;
use Route;
use URL;
use Auth;
use Carbon\Carbon;

use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Agreement;
use PayPal\Api\Plan as PaypalPlan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\AgreementTransactions;
use PayPal\Common\PayPalModel;

use App\Billing;
use App\Subscription;
use App\Plan;
use App\CrmLoad;

class PaypalController extends Controller
{
    use \App\Http\Traits\SubscriptionTraits;

    private $_apiContext;

    public function __construct()
    {
        $this->_apiContext = PayPal::ApiContext(
            config('services.paypal.client_id'),
            config('services.paypal.secret')
        );

        $this->_apiContext->setConfig(array(
            'mode' => 'sandbox',
            'service.EndPoint' => 'https://api.sandbox.paypal.com',
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'FINE'
        ));

    }

    // public function getCheckout(Request $request)
    // {
    //     $plan = $this->createPlan($request);
    //     $activePlan = $this->activatePlan($plan);
    //     $payer = $this->setPayer();
    //     $approvalUrl = $this->createAgreement($activePlan, $payer);

    //     return Redirect::to($approvalUrl);
    // }

    public function getCheckout(Request $request)
    {
        $payer = $this->setPayer();

        $amount = PayPal::Amount();
        $amount->setCurrency('GBP');
        $amount->setTotal($request->paypal_amount);

        $item1 = PayPal::Item();
        $item1->setName($request->paypal_product.' CRM');
        $item1->setDescription($request->paypal_product.' CRM');
        $item1->setCurrency('GBP');
        $item1->setQuantity(1);
        $item1->setPrice($request->paypal_amount);

        $itemList = PayPal::ItemList();
        $itemList->addItem($item1);

        $transaction = PayPal::Transaction();
        $transaction->setAmount($amount);
        $transaction->setItemList($itemList);
        $transaction->setDescription($request->paypal_product.' CRM');

        $redirectUrls = PayPal:: RedirectUrls();
        $redirectUrls->setReturnUrl(action('Trendle\PaypalController@getDone', array('plan_id' => $request->plans)));
        $redirectUrls->setCancelUrl(action('Trendle\PaypalController@getCancel'));

        $payment = PayPal::Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));
        $payment->setExperienceProfileId($this->createWebProfile());

        try {
            $response = $payment->create($this->_apiContext);
        } catch (PayPalConnectionException $e) {
            echo $e->getData();
        }
        $redirectUrl = $response->links[1]->href;
        return Redirect::to( $redirectUrl );
    }

    public function getDone(Request $request)
    {
        $id = $request->get('paymentId');
        $token = $request->get('token');
        $payer_id = $request->get('PayerID');

        $payment = PayPal::getById($id, $this->_apiContext);

        $paymentExecution = PayPal::PaymentExecution();

        $paymentExecution->setPayerId($payer_id);
        $executePayment = $payment->execute($paymentExecution, $this->_apiContext);

        // Clear the shopping cart, write to database, send notifications, etc.
        //get seller_id
        $seller_id = Auth::user()->seller_id;
        $plans = Plan::find($request->plan_id);
        //get load from plan
        $credit = $plans->load;
        //if seller has credit get the amount
        //add the newly purchased load
        //then update credit
        //else insert new crm load
        $total_credit = $credit;
        $loads = CrmLoad::where('seller_id', '=', $seller_id)->first();
        if ($loads) {
            $load = $loads->credit;
            $total_credit = $credit + $load;
            $loads->credit = $total_credit;
            $loads->save();
        } else {
            $crmload = new CrmLoad;
            $crmload->seller_id = $seller_id;
            $crmload->credit = $total_credit;
            $crmload->save();
        }
        
        return redirect('subscription');
    }


    public function createPlan($request)
    {
        $plan = new PaypalPlan();
        $plan->setName('Monthly Plan')
            ->setDescription('Monthly Plan')
            ->setType('infinite');

        $plan->setPaymentDefinitions(array($this->setPaymentDefinitions($request)));
        $plan->setMerchantPreferences($this->setMerchantPreferences($request));

        $createdPlan = $plan->create($this->_apiContext);

        return $createdPlan;
    }

    public function setPaymentDefinitions(Request $request)
    {
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Plan Details')
            ->setType('REGULAR')
            ->setFrequency('Month')
            ->setFrequencyInterval("1")
            ->setCycles("0")
            ->setAmount(new Currency(array('value' => $request->paypal_amount, 'currency' => 'GBP')));

        return $paymentDefinition;
    }

    public function setMerchantPreferences($request)
    {
        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl(url('paypal/executeAgreement?plans='.$request->plans))
            ->setCancelUrl(url('paypal/cancel'))
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0")
            ->setSetupFee(new Currency(array('value' => $request->paypal_amount, 'currency' => 'GBP')));

        return $merchantPreferences;
    }

    public function activatePlan($plan)
    {
        $patch = new Patch();

        $value = new PayPalModel('{
             "state":"ACTIVE"
           }');

        $patch->setOp('replace')
            ->setPath('/')
            ->setValue($value);
        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);

        $plan->update($patchRequest, $this->_apiContext);

        $plan = PaypalPlan::get($plan->getId(), $this->_apiContext);

        $activePlan = new PaypalPlan();
        $activePlan->setId($plan->getId());

        return $activePlan;
    }

    public function setPayer()
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        return $payer;
    }

    public function createAgreement($activePlan, $payer)
    {
        $plan = PaypalPlan::get($activePlan->id, $this->_apiContext);
        $amount = $plan->payment_definitions[0]->amount->currency . ' ' . $plan->payment_definitions[0]->amount->value;

        $agreement = new Agreement();
        $startDate = Carbon::now()->addSecond()->toAtomString();
        $agreement->setName('Subscription')
            ->setDescription('Plan (' . $amount . ' / monthly)')
            ->setStartDate($startDate);
        $agreement->setPlan($activePlan);
        $agreement->setPayer($payer);

        $agreement = $agreement->create($this->_apiContext);
        $approvalUrl = $agreement->getApprovalLink();

        return $approvalUrl;
    }

    public function executeAgreement(Request $request)
    {
        $token = $request->token;
        $agreement = new Agreement();
        $agreement->execute($token, $this->_apiContext);

        $billing = Billing::find(Auth::user()->seller->billing->id);

        $subscription = new Subscription;
        $subscription->seller_id = $billing->seller_id;
        $subscription->billing_id = $billing->id;
        $subscription->name = "paypal";
        $subscription->created_at = Carbon::now();
        $subscription->updated_at = Carbon::now();
        $subscription->save();

        $this->storeSubscriptionPlans($request->plans);

        flash('Successfully subscribed to plan(s).', 'success');
        return redirect('subscription');
    }

    public function getCancel()
    {
        return redirect('subscription');
    }

    public function createWebProfile() {

        $flowConfig = PayPal::FlowConfig();
        $presentation = PayPal::Presentation();
        $inputFields = PayPal::InputFields();
        $webProfile = PayPal::WebProfile();
        $flowConfig->setLandingPageType("Billing"); //Set the page type

        $presentation->setLogoImage(url('images/logo.png'))->setBrandName("Trendle Analytics"); //NB: Paypal recommended to use https for the logo's address and the size set to 190x60.

        $inputFields->setAllowNote(true)->setNoShipping(1)->setAddressOverride(0);

        $webProfile->setName("Trendle " . uniqid())
            ->setFlowConfig($flowConfig)
            // Parameters for style and presentation.
            ->setPresentation($presentation)
            // Parameters for input field customization.
            ->setInputFields($inputFields);

        $createProfileResponse = $webProfile->create($this->_apiContext);

        return $createProfileResponse->getId(); //The new webprofile's id
    }

    static function routes()
    {
       Route::group(array('prefix' => 'paypal'), function() {
          Route::post('checkout', 'Trendle\PaypalController@getCheckout');
          Route::get('executeAgreement', 'Trendle\PaypalController@executeAgreement');
          Route::get('done', 'Trendle\PaypalController@getDone');
          Route::get('cancel', 'Trendle\PaypalController@getCancel');
       });
    }
}
