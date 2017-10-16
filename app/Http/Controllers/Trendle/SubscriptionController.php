<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Stripe\Plan as StripePlan;
use Stripe\Coupon as StripeCoupon;
use Stripe\Error\InvalidRequest as StripeInvalidRequest;
use Route;
use Auth;
use Carbon\Carbon;
use Session;
use App\Pillar;
use App\Plan;
use App\PlanCurrency;
use App\Billing;
use App\Subscription;
use App\SubscriptionPlan;
use App\PlanCoverage;
use App\CrmLoad;
use Redirect;
use App\FbaRefund;
use DvK\Laravel\Vat\Facades\Rates;
use DvK\Laravel\Vat\Facades\Validator;
use DvK\Laravel\Vat\Facades\Countries;
use Countries as CountriesModel;
use App\BillingInvoice;
use \Config;
use App\Mail\Invoicing;
use Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\PromoCode;
use App\PromoCodeA;
use App\PromoSubscription;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;
use App\BillingInvoiceItem;
use App\FbaPreCalculation;
use App\ProductReviewsSeller;

class SubscriptionController extends Controller
{
    use \App\Http\Traits\SubscriptionTraits;

    public function __construct()
    {
        $this->middleware('auth',['except' => 'convertBoostBSBaseSubs']);
    }

    public function index()
    {    
        $active_checker = '';
        $with_records = 0;
        $payment_method = '';
        $pillar_with_plans = Pillar::select('pillars.*')
              ->join('plans', 'plans.pillar_id', '=', 'pillars.id')
              ->groupBy('plans.pillar_id')
              ->get();

        $load = "0";
        $currency = "gbp";
        $dataIscheck = 0;
        $seller_id = Auth::user()->seller_id;
        $is_trial = Auth::user()->seller->is_trial; 
        $loads = CrmLoad::where('seller_id', '=', $seller_id)->get();
        foreach ($loads as $l) {
            if ($l->id > 0) {
                $load = $l->credit;
            }
        }

        $billing = Billing::where('seller_id', '=', $seller_id)->first();
        if (isset($billing)) {
            $preferred_currency = $billing->preferred_currency;
        }

        $fba_refund = FbaRefund::where('seller_id', '=', $seller_id)->first();
        if (!isset($fba_refund)) {
            $active_checker = '';
        } else {
            if ($fba_refund->is_activated == 1) {
                $active_checker = 'checked';
            } else {
                $active_checker = '';
            }
        }

        if (isset($preferred_currency)) {
            if ($preferred_currency == '' || $preferred_currency == null) {
                $currency_symbol = '£';
                $preferred_currency = 'gbp';
                $country_id = 826;
            }
            if ($preferred_currency == 'usd') {
                $currency_symbol = '$';
                $country_id = 840;
            } else if ($preferred_currency == 'eur') {
                $currency_symbol = '€';
                $country_id = 724;
            } else if ($preferred_currency == 'gbp') {
                $currency_symbol = '£';
                $country_id = 826;
            }
        } else {
            $currency_symbol = '£';
            $preferred_currency = 'gbp';
            $country_id = 826;
        }

        $plans_currency = PlanCurrency::where('country_id', '=', $country_id)->get();

        $sizes = array('XS','S','M','L','XL');

        $current_plans = $this->getSubscriptionPlan();

        $user_trial_end = date_format(date_create(Auth::user()->seller->trialperiod->trial_end_date), 'Y-m-d');
        $user_trial_end_nextday = date_format(date_create(Auth::user()->seller->trialperiod->trial_end_date)->modify('+1 day'), 'Y-m-d');

        // check if there is existing promo code active
        $ps = PromoSubscription::where('seller_id',$seller_id)
                               ->where('is_used',0)
                               ->first();
        //dd($ps);
        if(isset($ps))
        {
            $voucher = $ps->voucher_code;

            $pc = PromoCodeA::where('voucher_code',$voucher)
                              ->first();

            //validate if it is not expired
            if($pc->voucher_type == 'date')
            {
            $da = $pc->days_applied;
            $ca = $ps->created_at;
            $now = Carbon::now();

            //$test = Carbon::create(2018,6,30);
            $diff = ($now)->diffInDays($ca);
            $days_left = $da - $diff;

                if($diff >= $da)
                {
                    $ps->is_used = 1;
                    $ps->save();

                    return redirect('/subscription');
                    // response()->json([
                    // 'status' => 'failed',
                    // 'message' => $voucher.'<br>promo code has expired',
                    // 'coupon' => $pc
                    // ]);
                }
            }

            if($pc->discount_type == 'value')
            {
                $days_left = null;
                $promocode_currency = $pc->currency;
                $discount_value = $pc->discount_value;
                $promocode_amount = currency($discount_value, strtoupper($promocode_currency),strtoupper($preferred_currency),false);
                $promocode_amount = round($promocode_amount,2);
            }
            else
            {
                $promocode_amount = null;
            }
            //if expired then set promo subscription "is_used to 1"
        }
        else
        {
            $voucher = false;
            $ps = null;
            $pc = null;
            $days_left = null;
            $promocode_amount = null;
        } 

        $data = $this->callBaseSubscriptionName($seller_id);
        $load = $load + $data->bonus_load;
        /*
        added by jason 7/14/2017
        1 or 0 has_subscription if the user has subscription
        */

        $has_subscription = 0;

        if(Auth::user()->seller->basesubscription->count() > 0)
        {
            $has_subscription = 1;
        }

        if(Auth::user()->seller->is_trial == 1)
        {
            $has_subscription = 1;
        }

        $conversion = '';
        if ($preferred_currency != 'usd') {
            $con = DB::table('currencies')->where('code', '=', strtoupper($preferred_currency))->first();
            $conversion = 'Conversion Rate: 1 USD = '.$con->exchange_rate.' '.strtoupper($preferred_currency);
        }

        $fba_refund = FbaRefund::where('seller_id', '=', $seller_id)->first();
        if (!isset($fba_refund)) {
                $active_checker = '';
                $manage_checker = '';
                $diy_checker = '';
            } else {
            if ($fba_refund->is_activated == 1) {
                $active_checker = 'checked';
            } else {
                $active_checker = '';
            }

            if ($fba_refund->fba_mode == 'MANAGE') {
                $manage_checker = 'checked';
                $diy_checker = '';
            } else {
                $manage_checker = '';
                $diy_checker = 'checked';
            }
        }

        $billing = Billing::where('seller_id', '=', $seller_id)->first();
        if (isset($billing)) {
            $payment_method = $billing->payment_method;
            $preferred_currency = $billing->preferred_currency;
            if ($payment_method == '' || $payment_method == null) {
            }
        }

        $fpc = FbaPreCalculation::where('seller_id', '=', $seller_id)
                              ->get();

        $with_records = count($fpc);

        return view('trendle.subscription.index')
              ->with('pillar_with_plans', $pillar_with_plans)
              ->with('sizes', $sizes)
              ->with('plans_currency', $plans_currency)
              ->with('load', $load)
              ->with('currency', $preferred_currency)
              ->with('dataIscheck', $dataIscheck)
              ->with('current_plans', $current_plans)
              ->with('user_trial_end', $user_trial_end)
              ->with('user_trial_end_nextday', $user_trial_end_nextday)
              ->with('active_checker', $active_checker)
              ->with('currency_symbol', $currency_symbol)
              ->with('voucher', $voucher)
              ->with('ps',$ps)
              ->with('pc',$pc)
              ->with('days_left',$days_left)
              ->with('promocode_amount',$promocode_amount)
              ->with('bs',$data->base_subscription)
              ->with('xs_checker',$data->xs_checker)
              ->with('s_checker',$data->s_checker)
              ->with('m_checker',$data->m_checker)
              ->with('l_checker',$data->l_checker)
              ->with('xl_checker',$data->xl_checker)
              ->with('has_subscription',$has_subscription)
              ->with('is_trial',$is_trial)
              ->with('conversion',$conversion)
              ->with('manage_checker',$manage_checker)
              ->with('diy_checker',$diy_checker)
              ->with('with_records', $with_records)
              ->with('payment_method',$payment_method);
    }
    // For review -RJ
    private function hasExpired()
    {

    }

    private function sendInvoice($fname, $lname, $email, $token)
    {
      $token = Crypt::encryptString($token);
      //backup default config
      $backup = Config::get('mail');
      //set new config for sparkpost
      if(env('SPARKPOST_MAIL_DRIVER') != ""){
        Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));
      }

      Mail::to($email)->send(new Invoicing($fname, $lname, $token));

      //restore default config
      Config::set('mail', $backup);
    }


    private function calculateVat($seller_id, $amount, $vat_num, $country_id, $country_code,  $currency)
    {

        $bill = new Billing;
        $cc = $bill->country_code($country_id);
        $inEurope = Countries::inEurope($cc);
        
        if ($inEurope) 
        {
            if($country_code != false)
            {
                $vcc = $bill->country_code($country_code);
                $vat_number = $vcc.$vat_num;
                $v = Validator::validate($vat_number);
                if ($v == true)
                {
                    $vat = 0;
                    $status = "Valid Vat Number";
                }
                else
                {
                    $rate = Rates::country($cc); 
                    $vat = $amount * ($rate / 100);
                    $status = "Invalid Vat Number";
                }
            }
            else
            {
                $rate = Rates::country($cc); 
                $vat = $amount * ($rate / 100);
                $status = "Invalid Vat Number";
            }
        }
        else 
        {
            $vat = 0;        
            $status = "Non-EU";
        }

      $latestID = DB::table('billing_invoices')->select('id')->orderBy('id', 'DESC')->first();
      if (is_null($latestID)) {
        $latestID = 0;
      } else {
        $latestID = (int)$latestID->id;
      }

      $data = array(
        'latestID' => $latestID,
        'amount' => $amount,
        'vat' => $vat,
        'cc' => $cc,
        'status' => $status
        );

      return $data;
    }

    public function purchase(Request $request)
    {
        //get seller_id
        $seller_id = Auth::user()->seller_id;
        
        //charge customer
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $billing = Billing::find(Auth::user()->seller->billing->id);
        $preferred_currency = ($billing->preferred_currency != null) ? $billing->preferred_currency : 'usd';
        $country_code = $billing->vat_country_code;
        $country_id = $billing->country_id;
        $vat_num = $billing->vat_number;
        $customer = \Stripe\Customer::retrieve($billing->stripe_id);

        if ($request->planSize == 'S') {
            $request->amount = 500;
            $description = 'S';
            $credit = 1000;
        } else if ($request->planSize == 'M') {
            $request->amount = 1000;
            $description = 'M';
            $credit = 5000;
        } else if ($request->planSize == 'L') {
            $request->amount = 2500;
            $description = 'L';
            $credit = 50000;
        }

        $amount = currency($request->amount, 'USD', strtoupper($preferred_currency), false);
        $amount_item = $amount/100;
        $promocode = NULL;
        $promocode_discount = 0;

        $ps = PromoSubscription::where('seller_id',$seller_id)
                               ->where('is_used',0)
                               ->first();

        if(isset($ps))
            {
                $voucher_code = $ps->voucher_code;

                $pc = PromoCodeA::where('voucher_code', $voucher_code)
                                ->first();

                $promocode = $voucher_code;
                $promocode_currency = $pc->currency;
                $promocode_amount = $pc->discount_value;
                $promocode_discount_type = $pc->discount_type;
                    if($promocode_discount_type == 'percent')
                    {
                        $promocode_discount = $amount*($promocode_amount / 100);
                        $amount = $amount - $promocode_discount;
                    }
                    else if($promocode_discount_type == 'value')
                    {
                        $promocode_amount = currency($promocode_amount, strtoupper($promocode_currency),strtoupper($preferred_currency),
                            false);
                        $promocode_discount = round($promocode_amount,2)*100;

                        if ($promocode_discount > $amount) {
                            $promocode_discount = $amount;
                            $amount = 0;
                        } else {
                           $amount = $amount - $promocode_discount;
                        }
                    }
            }

        $data = $this->calculateVat($seller_id, $amount/100, $vat_num, $country_id, $country_code, $preferred_currency);
        $amount = $amount + ($data['vat']*100);

        if ($amount != 0) {
            try {
                $charge = \Stripe\Charge::create(array(
                    "amount" => (int)($amount),
                    "currency" => $preferred_currency,
                    "customer" => $customer->id,
                    "description" => $description.' CRM'
                ));

            } catch(\Stripe\Error\Card $e) {
                return 'false';
            }
        }

        
        //if seller has credit get the amount
        //add the newly purchased load
        //then update credit
        //else insert new crm load
        $total_credit = $credit;
        $loads = CrmLoad::where('seller_id', '=', $seller_id)->first();
        DB::transaction(function() use ($seller_id,$data,$credit,$total_credit,$loads,$preferred_currency,$description,$promocode,$promocode_discount,$amount_item) {
            $bi = new BillingInvoice;
            $bi->seller_id = $seller_id;
            $bi->invoice_number = $data['latestID'] + 1;
            $bi->product_description = $description.' CRM';
            $bi->product_subscription = $description.' CRM';
            $bi->amount = $data['amount'];
            $bi->vat = $data['vat'];
            $bi->country_code = $data['cc'];
            $bi->currency = $preferred_currency;
            $bi->promocode = $promocode;
            $bi->promocode_discount = $promocode_discount/100;
            $bi->status = $data['status'];
            $bi->created_at = Carbon::now();
            $bi->updated_at = Carbon::now();
            if ($bi->save()) {
                $bii1 = new BillingInvoiceItem;
                $bii1->bi_id = $bi->id;
                $bii1->product_description = $description.' CRM';
                $bii1->item_amount = $amount_item;
                $bii1->currency = $preferred_currency;
                $bii1->created_at = Carbon::now();
                $bii1->updated_at = Carbon::now();
                $bii1->save();
            }

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
        });

        $fname = Auth::user()->seller->firstname;
        $lname = Auth::user()->seller->lastname;
        $email = Auth::user()->seller->email;
        $token = $data['latestID'] + 1;
        $this->sendInvoice($fname, $lname, $email, $token);

        if(isset($ps))
        {
            $voucher_code = $ps->voucher_code;

            $pc = PromoCodeA::where('voucher_code', $voucher_code)
                                ->first();

            if($pc->voucher_type == 'num')
            {
                $ps->is_used = 1;
                $ps->save();
            }
        }

        flash('Successfully purchased credits.', 'success');
        return 'true';
    }

    public function getSubscriptionPlan()
    {
        $subscription_plans = SubscriptionPlan::select('plan_id')->where('seller_id', Auth::user()->seller->id)->get();
        $plans = array_flatten($subscription_plans->toArray());

        return $plans;
    }

    public function getPromo(Request $request)
    {
        $seller_id = Auth::user()->seller->id;
        $pc = PromoCodeA::where('voucher_code',$request->coupon)
                            ->where('is_active',1)
                            ->first();


        $ps = PromoSubscription::where('voucher_code', $request->coupon)
                                ->where('is_used',0)
                                ->count();
        if(isset($pc))
        {
        $rb = $pc->redeem_by;
        $mytime = Carbon::now();

        $rb = strtotime($rb);
        $mytime = strtotime($mytime);
        $check = ($rb >= $mytime) ? true : false;
        }
        else
        {
            $check = null;
        }

        $mr = PromoSubscription::where('voucher_code', $request->coupon)
                                 ->count();

        //check redeem date validation and check promo if exists
        if(count($pc) == 0)
        {
            return response()->json([
                    'status' => 'failed',
                    'message' => $request->coupon.'<br>promo code is not valid'
                ]);
        }
        else if($check == false)
        {
           return response()->json([
                    'status' => 'failed',
                    'message' => $request->coupon.'<br>promo code has expired'
                ]);
        }
        else if($ps > 0)
        {
             return response()->json([
                    'status' => 'failed',
                    'message' => $request->coupon.'<br>promo code is already active'
                ]);
        }
        else if($pc->max_redemption <= $mr)
        {
             return response()->json([
                    'status' => 'failed',
                    'message' => $request->coupon.'<br>promo code is denied'
                ]);
        }
        else
        {
            return response()->json([
                    'status' => 'success',
                    'coupon' => $pc
                ]);
        }
    }

    public function planCoverage(Request $request)
    {
        $planCoverage = PlanCoverage::where('plan_id', $request->plan_id)->first();
        return response()->json(['coverage' => $planCoverage->coverage]);
    }

    public function hasBillingCard(){
        $hasCard = false;

        if (Auth::user()->seller->billing)
          $hasCard = Auth::user()->seller->billing->hasStripeId() == true ? true : false;

        return  $hasCard ? 'true' : 'false';
    }

    private function setSellerId()
    {
        $subscriptions = Subscription::where('billing_id', Auth::user()->seller->billing->id)->get();

        foreach ($subscriptions as $key => $subscription) {
            $subscription->seller_id = Auth::user()->seller->id;
            $subscription->save();
        }
    }

    ### START BASE SUBSCRIPTION ###
    /**
     *
     * Gets the bs_name from base_subscription_sellers table
     * and adds a checker for the radio buttons of the view
     *
     * @param    integer    $seller_id
     * @return   object     $data
     *
     */
    private function callBaseSubscriptionName($seller_id) {
        $data = (object) null;

        $data->base_subscription = '';
        $base_subscription_alyas = '';
        $data->bonus_load = 0;
        $data->xs_checker = '';
        $data->s_checker = '';
        $data->m_checker = '';
        $data->l_checker = '';
        $data->xl_checker = '';
        $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller_id)->first();
        if (isset($bss)) {
            $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                        ->where('currently_used', '=', true)
                                                        ->first();
            if (isset($bsst)) {
                $data->base_subscription = $bsst->bs_name;
                $data->bonus_load = $bsst->bonus_mail - $bsst->mail_used;
            } else {
                $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                        ->where('up_next', '=', true)
                                                        ->first();                                                        
                $data->base_subscription = $bsst->bs_name;
            }

            switch ($data->base_subscription) {
                case 'XS':
                    $data->xs_checker = 'checked';
                    $base_subscription_alyas = 'Start-up';
                    break;
                
                case 'S':
                    $data->s_checker = 'checked';
                    $base_subscription_alyas = 'Growing Business';
                    break;
                
                case 'M':
                    $data->m_checker = 'checked';
                    $base_subscription_alyas = 'Pro';
                    break;
                
                case 'L':
                    $data->l_checker = 'checked';
                    $base_subscription_alyas = 'High-Flyer';
                    break;
                
                case 'XL':
                    $data->xl_checker = 'checked';
                    $base_subscription_alyas = '';
                    break;
                
                default:
                    $data->xs_checker = '';
                    $data->s_checker = '';
                    $data->m_checker = '';
                    $data->l_checker = '';
                    $data->xl_checker = '';
                    $base_subscription_alyas = '';
                    break;
            }
        }
        $data->base_subscription = $base_subscription_alyas;
        return $data;
    }


    /**
     *
     * Inserts or updates data to base_subscription_sellers table
     *
     * @param    Request    $request
     * @return   
     *
     */
    public function selectBaseSubscription(Request $request) {
        $seller_id = Auth::user()->seller->id;
        $is_trial = Auth::user()->seller->is_trial;
        $base_subscription = $request->base_subscription;
        $bonus_mail = 0;
        $atp = 0;
        $compare_one = 0;
        $bigger = false;
        switch ($base_subscription) {
            case 'XS':  
                $bonus_mail = 1000;
                $atp = 20;
                $compare_one = 1;
                $base_subscription_alyas = 'Start-up';
                break;
            
            case 'S':  
                $bonus_mail = 3000;
                $atp = 50;
                $compare_one = 2;
                $base_subscription_alyas = 'Growing Business';
                break;
            
            case 'M':  
                $bonus_mail = 10000;
                $atp = 100;
                $compare_one = 3;
                $base_subscription_alyas = 'Pro';
                break;
            
            case 'L':  
                $bonus_mail = 40000;
                $atp = 200;
                $compare_one = 4;
                $base_subscription_alyas = 'High-Flyer';
                break;
            
            case 'XL':  
                $bonus_mail = 100000;
                $atp = 400;
                $compare_one = 5;
                $base_subscription_alyas = '';
                break;
            
            default:  
                $bonus_mail = 0;
                $atp = 0;
                $compare_one = 0;
                $base_subscription_alyas = '';
                break;
        }

    
        $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller_id)->first();
        if (isset($bss)) {

            if ($is_trial == 1) {

                $bsst_un = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                            ->where('up_next', '=', true)
                                                            ->first();
                if (isset($bsst_un)) {
                    $bsst_un->delete();
                }

                $bsst_new = new BaseSubscriptionSellerTransaction;
                $bsst_new->bss_id = $bss->id;
                $bsst_new->bs_name = $base_subscription;
                $bsst_new->bonus_mail = $bonus_mail;
                $bsst_new->email_used = 0;
                $bsst_new->amount_to_pay = $atp;
                $bsst_new->is_pro_rated = false;
                $bsst_new->currently_used = false;
                $bsst_new->up_next = true;
                $bsst_new->created_at = Carbon::now();
                $bsst_new->updated_at = Carbon::now();
                $bsst_new->save();

                $this->createProductReviewSeller();
                Session::flash('success', 'Base Subscription ('.$base_subscription_alyas.') saved successfully. This will be activated after the next billing cycle.');
                return Redirect::back();

            } else {
                $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                        ->where('currently_used', '=', true)
                                                        ->first();
                $bs_name = $bsst->bs_name;
                $email_used = $bsst->email_used;
                $amount_to_pay = $bsst->amount_to_pay;
                $compare_two = 0;
                if ($bs_name == 'XS') {
                    $compare_two = 1;
                } else if ($bs_name == 'S') {
                    $compare_two = 2;
                } else if ($bs_name == 'M') {
                    $compare_two = 3;
                } else if ($bs_name == 'L') {
                    $compare_two = 4;
                } else if ($bs_name == 'XL') {
                    $compare_two = 5;
                }
                
                if ($compare_one > $compare_two) {
                    $bigger = true;
                    $bsst_un = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                            ->where('up_next', '=', true)
                                                            ->first();
                    if (isset($bsst_un)) {
                        $bsst_un->delete();
                    }

                    $dt = Carbon::parse($bsst->created_at);
                    $diff = $dt->diffInDays(Carbon::now());
                    $bsst_du = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                            ->get();
                    $diff2 = 0;
                    foreach ($bsst_du as $val) {
                        $diff2 += $val->days_used;
                    }

                    if ($diff == 0) {
                        $bsst->amount_to_pay = 0;
                        $bsst->days_used = 0;
                        $bsst->is_pro_rated = false;
                    } else {
                        $set_atp = $amount_to_pay/30.42*(int)$diff;
                        $bsst->amount_to_pay = $set_atp;
                        $bsst->days_used = $diff;
                        $bsst->is_pro_rated = true;
                    }
                    $bsst->currently_used = false;
                    if ($bsst->save()) {

                        $bsst_new = new BaseSubscriptionSellerTransaction;
                        $bsst_new->bss_id = $bss->id;
                        $bsst_new->bs_name = $base_subscription;
                        $bsst_new->bonus_mail = $bonus_mail;
                        $bsst_new->email_used = $email_used;
                        if ($diff2 == 0) {
                            $diff = $diff;
                        } else {
                            $diff = $diff+$diff2;
                        }
                        if ($diff == 0) {
                            $bsst_new->amount_to_pay = $atp;
                            $bsst_new->is_pro_rated = false;
                        } else {
                            $set_atp = $atp/30.42*(30.42-(int)$diff);
                            $bsst_new->amount_to_pay = $set_atp;
                            $bsst_new->is_pro_rated = true;
                        }
                        $bsst_new->currently_used = true;
                        $bsst_new->up_next = false;
                        $bsst_new->created_at = Carbon::now();
                        $bsst_new->updated_at = Carbon::now();
                        $bsst_new->save();

                        $this->createProductReviewSeller();
                    }

                } else {
                    $bigger = false;
                    $bsst_un = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                            ->where('up_next', '=', true)
                                                            ->first();
                    if (isset($bsst_un)) {
                        $bsst_un->delete();
                    }

                    $bsst_new = new BaseSubscriptionSellerTransaction;
                    $bsst_new->bss_id = $bss->id;
                    $bsst_new->bs_name = $base_subscription;
                    $bsst_new->bonus_mail = $bonus_mail;
                    $bsst_new->email_used = 0;
                    $bsst_new->amount_to_pay = $atp;
                    $bsst_new->is_pro_rated = false;
                    $bsst_new->currently_used = false;
                    $bsst_new->up_next = true;
                    $bsst_new->created_at = Carbon::now();
                    $bsst_new->updated_at = Carbon::now();
                    $bsst_new->save();

                    $this->createProductReviewSeller();
                }

                $bss->updated_at = Carbon::now();
                $bss->save();
                if ($bigger == true) {
                    Session::flash('success', 'Base Subscription ('.$base_subscription_alyas.') activated successfully.');
                } else {
                    Session::flash('success', 'Base Subscription ('.$base_subscription_alyas.') saved successfully. This will be activated after the next billing cycle.');                
                }
                return Redirect::back();
            }
        } else { // new base subscription
            $y = date_format(date_create(date('Y-m-d')), 'Y');
            $m = date_format(date_create(date('Y-m-d')), 'm');
            $d = date_format(date_create(Auth::user()->seller->trialperiod->trial_start_date), 'd');
            if ($m == '12') {
                $y = (int)$y+1;
                $y = (string)$y;
                $m = '01';
            } else {            
                $m = (int)$m+1;
                $m = (string)$m;
                if (strlen($m) == 1) {
                    $m = '0'.$m;
                }
            }
            if ($d == '29' || $d == '30' || $d == '31') {
                $d = '01';
            }
            $nbd = $y.'-'.$m.'-'.$d;
            $bss_new = new BaseSubscriptionSeller;
            $bss_new->seller_id = $seller_id;
            $bss_new->next_billing_date = $nbd;
            $bss_new->is_active = 1;
            $bss_new->created_at = Carbon::now();
            $bss_new->updated_at = Carbon::now();
            if ($bss_new->save()) {
                if ($is_trial == 1) {
                    $bsst_new = new BaseSubscriptionSellerTransaction;
                    $bsst_new->bss_id = $bss_new->id;
                    $bsst_new->bs_name = $base_subscription;
                    $bsst_new->bonus_mail = $bonus_mail;
                    $bsst_new->email_used = 0;
                    $bsst_new->amount_to_pay = $atp;
                    $bsst_new->is_pro_rated = false;
                    $bsst_new->currently_used = false;
                    $bsst_new->up_next = true;
                    $bsst_new->created_at = Carbon::now();
                    $bsst_new->updated_at = Carbon::now();
                    $bsst_new->save();

                    $this->createProductReviewSeller();

                    Session::flash('success', 'Base Subscription ('.$base_subscription_alyas.') saved successfully. This will be activated after the next billing cycle.');
                    return Redirect::back();

                } else {
                    $bsst_new = new BaseSubscriptionSellerTransaction;
                    $bsst_new->bss_id = $bss_new->id;
                    $bsst_new->bs_name = $base_subscription;
                    $bsst_new->bonus_mail = $bonus_mail;
                    $bsst_new->email_used = 0;
                    $bsst_new->amount_to_pay = $atp;
                    $bsst_new->is_pro_rated = false;
                    $bsst_new->currently_used = true;
                    $bsst_new->up_next = false;
                    $bsst_new->created_at = Carbon::now();
                    $bsst_new->updated_at = Carbon::now();
                    $bsst_new->save();

                    $this->createProductReviewSeller();
                    Session::flash('success', 'Base Subscription ('.$base_subscription_alyas.') activated successfully.');
                    return Redirect::back();
                }
            }
        }

    }

    /**
     *
     * Converts base subscription amount to GBP and EURO
     *
     * @param    Request    $request
     * @return   json       $response;
     *
     */
    public function convertBS() {
        $response = [];

        $response['xs_gbp'] = currency(20, 'USD', 'GBP', false);
        $response['s_gbp'] = currency(50, 'USD', 'GBP', false);
        $response['m_gbp'] = currency(100, 'USD', 'GBP', false);
        $response['l_gbp'] = currency(200, 'USD', 'GBP', false);
        $response['xl_gbp'] = currency(400, 'USD', 'GBP', false);
        $response['xs_eur'] = currency(20, 'USD', 'EUR', false);
        $response['s_eur'] = currency(50, 'USD', 'EUR', false);
        $response['m_eur'] = currency(100, 'USD', 'EUR', false);
        $response['l_eur'] = currency(200, 'USD', 'EUR', false);
        $response['xl_eur'] = currency(400, 'USD', 'EUR', false);

        return json_encode($response);
    }

    /**
     * Added by jason 7/13/2017
     * Converts boost base subscription amount to GBP and EURO
     *
     * @param    Request    $request
     * @return   json       $response;
     *
     */
    public function convertBoostBS() {
        $response = [];


        $response['s_gbp'] = currency(5, 'USD', 'GBP', false);
        $response['m_gbp'] = currency(10, 'USD', 'GBP', false);
        $response['l_gbp'] = currency(25, 'USD', 'GBP', false);
        $response['s_eur'] = currency(5, 'USD', 'EUR', false);
        $response['m_eur'] = currency(10, 'USD', 'EUR', false);
        $response['l_eur'] = currency(25, 'USD', 'EUR', false);

        return json_encode($response);
    }

    /**
     * Added by jason 7/13/2017
     * Converts boost base subscription amount to GBP and EURO
     *
     * @param    Request    $request
     * @return   json       $response;
     *
     */
    public function convertFbaRate() {
        $response = [];

        $response['gbp_fba'] = currency(30, 'USD', 'GBP', false);
        $response['eur_fba'] = currency(30, 'USD', 'EUR', false);

        return json_encode($response);
    }

    /**
     * Added by jason 7/13/2017
     * Converts landing page rates amount to GBP and EURO
     *
     * @param    Request    $request
     * @return   json       $response;
     *
     */
    public function convertBoostBSBaseSubs(){
        $response = [];

        $response['xs_gbp'] = currency(20, 'USD', 'GBP', false);
        $response['s_gbp'] = currency(50, 'USD', 'GBP', false);
        $response['m_gbp'] = currency(100, 'USD', 'GBP', false);
        $response['l_gbp'] = currency(200, 'USD', 'GBP', false);
        $response['xl_gbp'] = currency(400, 'USD', 'GBP', false);
        $response['xs_eur'] = currency(20, 'USD', 'EUR', false);
        $response['s_eur'] = currency(50, 'USD', 'EUR', false);
        $response['m_eur'] = currency(100, 'USD', 'EUR', false);
        $response['l_eur'] = currency(200, 'USD', 'EUR', false);
        $response['xl_eur'] = currency(400, 'USD', 'EUR', false);

        $response['s_gbp_crm'] = currency(5, 'USD', 'GBP', false);
        $response['m_gbp_crm'] = currency(10, 'USD', 'GBP', false);
        $response['l_gbp_crm'] = currency(25, 'USD', 'GBP', false);
        $response['s_eur_crm'] = currency(5, 'USD', 'EUR', false);
        $response['m_eur_crm'] = currency(10, 'USD', 'EUR', false);
        $response['l_eur_crm'] = currency(25, 'USD', 'EUR', false);

        $response['gbp_fba'] = currency(30, 'USD', 'GBP', false);
        $response['eur_fba'] = currency(30, 'USD', 'EUR', false);

        return json_encode($response);
    }

    public function createProductReviewSeller()
    {
        $seller_id = Auth::user()->seller_id;
        //For ProductReviews module created by Altsi 9/6/2017
        $prs = ProductReviewsSeller::where('seller_id', $seller_id)->first();

        $bs = DB::table('base_subscription_sellers as bss')
                        ->leftJoin('base_subscription_seller_transactions as bst','bss.id', '=', 
                        'bst.bss_id')
                        ->where('bss.seller_id', $seller_id)
                        ->where('currently_used', 1)
                        ->select('bst.id','seller_id','bs_name')
                        ->first();

        if(isset($bs))
        {
            if(!isset($prs))
            {
                $psr_new = new ProductReviewsSeller;
                $psr_new->bst_id = $bs->id;
                $psr_new->seller_id = $seller_id;
                $psr_new->schedule = Carbon::now();
                $psr_new->is_active = 1;
                $psr_new->save();
            }
            else
            {
                if(is_null($prs->bst_id))
                {
                    ProductReviewsSeller::destroy($prs->id);

                    $psr_new = new ProductReviewsSeller;
                    $psr_new->bst_id = $bs->id;
                    $psr_new->seller_id = $seller_id;
                    $psr_new->schedule = Carbon::now();
                    $psr_new->is_active = 1;
                    $psr_new->save();
                }
                else
                {
                    $prs->bst_id = $bs->id;
                    $prs->save();
                }

            }

        }
    }
    ### END BASE SUBSCRIPTION ###
}
