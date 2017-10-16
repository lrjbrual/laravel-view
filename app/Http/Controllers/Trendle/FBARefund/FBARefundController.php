<?php

namespace App\Http\Controllers\Trendle\FBARefund;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Seller;
use App\Billing;
use App\FbaRefund;
use App\FbaRefundTran;
use App\InventoryAdjustmentReport;
use App\MarketplaceAssign;
use App\CronMasterList;
use App\SellerCronSchedule;
use App\FinancialEventsReport;
use Session;
use Redirect;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Currency;
use App\FbaPreCalculation;

use DvK\Laravel\Vat\Facades\Rates;
use DvK\Laravel\Vat\Facades\Validator;
use DvK\Laravel\Vat\Facades\Countries;
use App\BillingInvoice;
use \Config;
use App\Mail\Invoicing;
use Mail;
use Illuminate\Support\Facades\Crypt;
use App\Reimbursement;
use App\OrderIdClaim;
use App\FnskuClaim;
use App\PromoCode;
use App\PromoCodeA;
use App\PromoSubscription;
use App\AdminSeller;
use App\FbaRefundDiyTran;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;

class FBARefundController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth');
      $this->middleware('checkStripe');
    }

    public function index(){

      $seller_id = Auth::user()->seller_id;
      $mkp_assign = array();
      $where = array('seller_id'=>$seller_id);
      $q= new MarketplaceAssign();
      $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());
      $countries = '';

      $warn_bill = false;
      $warn_pay_method = false;
      $warn_pref_currency = false;
      $preferred_currency = "";
      $active_checker = '';
      $payment_method = '';
      $with_records = 0;
      $billing = Billing::where('seller_id', '=', $seller_id)->first();
      if (isset($billing)) {
        $payment_method = $billing->payment_method;
        $preferred_currency = $billing->preferred_currency;
        if ($payment_method == '' || $payment_method == null) {
          $warn_pay_method = true;
        }
      } else {
        $warn_bill = true;
      }
      $fpc = FbaPreCalculation::where('seller_id', '=', $seller_id)
                              ->get();
      $with_records = count($fpc);

      if ($preferred_currency == '' || $preferred_currency == null) {
        $currency = '£';
        $preferred_currency = 'gbp';
        $warn_pref_currency = true;
      }
      if ($preferred_currency == 'usd') {
        $currency = '$';

      } else if ($preferred_currency == 'eur') {
        $currency = '€';

      } else if ($preferred_currency == 'gbp') {
        $currency = '£';
      }
      $total_reimbursed = 0;
      $total_fees_deducted = 0;

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

      $fba_refund_tran = FbaRefundTran::where('seller_id', '=', $seller_id)->get();
      if (!isset($fba_refund)) {
        $total_reimbursed = 0;
      } else {
        foreach ($fba_refund_tran as $val) {
          $total_reimbursed += $val->amount_reimbursed;
          $total_fees_deducted += $val->fees_deducted;
        }
      }

      foreach ($mkp_assign as $value) {
        $mkp = array();
        if($value->marketplace_id == 1) $mkp = config('constant.amz_keys.na.marketplaces');
        if($value->marketplace_id == 2) $mkp = config('constant.amz_keys.eu.marketplaces');
        foreach ($mkp as $key => $mkp_data) {
          $countries .= $key."-";
        }
      }
      $countries = rtrim($countries,"-");
      $sc_email = Seller::where('id', "=", Auth::user()->seller_id)->first()->email_for_sc;
      $data = $this->callBaseSubscriptionName($seller_id);
      return view('trendle.fbarefund.index')
          ->with('countries', $countries)
          ->with('warn_bill',$warn_bill)
          ->with('warn_pay_method',$warn_pay_method)
          ->with('warn_pref_currency',$warn_pref_currency)
          ->with('preferred_currency',$preferred_currency)
          ->with('currency',$currency)
          ->with('active_checker',$active_checker)
          ->with('total_reimbursed', $total_reimbursed)
          ->with('payment_method',$payment_method)
          ->with('total_fees_deducted', $total_fees_deducted)
          ->with('with_records', $with_records)
          ->with('sc_email', $sc_email)
          ->with('manage_checker',$manage_checker)
          ->with('diy_checker',$diy_checker)
          ->with('bs',$data->base_subscription);
    }

    public function getFBADetailsByCountry(Request $request){
      $seller_id = Auth::user()->seller_id;
      $total_refund = 0;
      $total_refund_gbp = 0;
      $total_refund_usd = 0;
      $total_refund_eur = 0;
      $preferred_currency = $request->currency;
      $with_records = 0;

      ini_set('memory_limit', '1024M');
      ini_set("zlib.output_compression", 0);  // off
      ini_set("implicit_flush", 1);  // on 
      ini_set("max_execution_time", 0);  // on

      $fpc = FbaPreCalculation::where('seller_id', '=', $seller_id)
                              ->where('country_code', '=', $request->country)
                              ->get();
      if (count($fpc) > 0) {
        foreach ($fpc as $val) {
          if ($val->currency == 'gbp') {
            $total_refund_gbp += $val->total_owed;
          } elseif ($val->currency == 'usd') {
            $total_refund_usd += $val->total_owed;
          } elseif ($val->currency == 'eur') {
            $total_refund_eur += $val->total_owed;
          }
        }
      }
      $total_refund_gbp = currency($total_refund_gbp, 'GBP', strtoupper($preferred_currency), false);
      $total_refund_usd = currency($total_refund_usd, 'USD', strtoupper($preferred_currency), false);
      $total_refund_eur = currency($total_refund_eur, 'EUR', strtoupper($preferred_currency), false);
      $total_refund = $total_refund_gbp + $total_refund_usd + $total_refund_eur;

      $total_saved = $this->getTotalSaved($seller_id, $request->country, $preferred_currency);
      $total_collected = $this->getTotalCollected($seller_id, $request->country, $preferred_currency);
      $total_deduct = $this->getTotalDeduct($seller_id, $request->country, $preferred_currency);
      $total_owed = $total_refund - $total_saved;
      $total_owed_to_collect = ($total_owed*(0.1))-($total_collected-$total_deduct);

      $data = array(
        'total_refund_country' => number_format($total_owed,2),
        'total_owed_to_collect' => round(($total_owed_to_collect*10),2),
        'country' => $request->country,
        'country_name' => config('constant.country_list.'.$request->country),
        'total_reimbursed' => number_format($total_saved,2),
        'total_refund_country_percent' => number_format($total_collected,2)
        );
      echo json_encode($data);

    }

    private function getTotalSaved($seller_id, $country, $preferred_currency) {
      $total_saved = 0;
      $email = Auth::user()->seller->email;
      $curr = '';

      $as = AdminSeller::where('seller_email', '=', $email)
                                 ->where('country_code', '=', $country)
                                 ->get();

      foreach($as as $v)
      {
        $total_saved += $v->total_saved;
        $curr = $v->currency;
      }
      
      $total_saved = currency($total_saved, strtoupper($curr), strtoupper($preferred_currency), false);

      return $total_saved;
    }

    private function getTotalCollected($seller_id, $country, $preferred_currency) {
      $total_collected = 0;
      $total_collected_gbp = 0;
      $total_collected_usd = 0;
      $total_collected_eur = 0;

      $frt = FbaRefundTran::where('seller_id', '=', $seller_id)
                              ->where('country_code', '=', $country)
                              ->get();
      if (count($frt) > 0) {
        foreach ($frt as $val) {
            if ($val->currency == 'gbp') {
              $total_collected_gbp += $val->fees_paid;
            } elseif ($val->currency == 'usd') {
              $total_collected_usd += $val->fees_paid;
            } elseif ($val->currency == 'eur') {
              $total_collected_eur += $val->fees_paid;
            }
        }
      }
      $total_collected_gbp = currency($total_collected_gbp, 'GBP', strtoupper($preferred_currency), false);
      $total_collected_usd = currency($total_collected_usd, 'USD', strtoupper($preferred_currency), false);
      $total_collected_eur = currency($total_collected_eur, 'EUR', strtoupper($preferred_currency), false);

      $total_collected = $total_collected_gbp + $total_collected_usd + $total_collected_eur;

      return $total_collected;
    }

    private function getTotalDeduct($seller_id, $country, $preferred_currency) {
      $total_deduct = 0;
      $total_deduct_gbp = 0;
      $total_deduct_usd = 0;
      $total_deduct_eur = 0;

      $frt = FbaRefundTran::where('seller_id', '=', $seller_id)
                                ->where('country_code', '=', $country)
                              ->get();
      if (count($frt) > 0) {
        foreach ($frt as $val) {
            if ($val->currency == 'gbp') {
              $total_deduct_gbp += $val->fees_deducted;
            } elseif ($val->currency == 'usd') {
              $total_deduct_usd += $val->fees_deducted;
            } elseif ($val->currency == 'eur') {
              $total_deduct_eur += $val->fees_deducted;
            }
        }
      }
      $total_deduct_gbp = currency($total_deduct_gbp, 'GBP', strtoupper($preferred_currency), false);
      $total_deduct_usd = currency($total_deduct_usd, 'USD', strtoupper($preferred_currency), false);
      $total_deduct_eur = currency($total_deduct_eur, 'EUR', strtoupper($preferred_currency), false);

      $total_deduct = $total_deduct_gbp + $total_deduct_usd + $total_deduct_eur;

      return $total_deduct;
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


    private function calculateVat($seller_id, $amount, $vat_num, $country_id, $country_code, $currency)
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

    public function payRefund(Request $request)
    {
      //get seller_id
      $seller_id = Auth::user()->seller_id;

      $fba_refund = FbaRefund::where('seller_id', '=', $seller_id)->first();

      $amount_us = (isset($request->amount_us)) ? $request->amount_us : 0;
      $amount_ca = (isset($request->amount_ca)) ? $request->amount_ca : 0;
      $amount_uk = (isset($request->amount_uk)) ? $request->amount_uk : 0;
      $amount_fr = (isset($request->amount_fr)) ? $request->amount_fr : 0;
      $amount_de = (isset($request->amount_de)) ? $request->amount_de : 0;
      $amount_es = (isset($request->amount_es)) ? $request->amount_es : 0;
      $amount_it = (isset($request->amount_it)) ? $request->amount_it : 0;
      $temp_amount = $amount_us + $amount_ca + $amount_uk + $amount_fr + $amount_de + $amount_es + $amount_it;
      $amount = ($temp_amount*0.1)*100;

      if (!isset($fba_refund)) {
        if (!isset(Auth::user()->seller->billing->id)) {
          Session::flash('error', 'You do not have billing credentials.');
          return Redirect::back();
        } 
        else 
        {
          //charge customer
          if (($temp_amount*0.1) >= 50) 
          {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $billing = Billing::find(Auth::user()->seller->billing->id);
            $country_id = $billing->country_id;
            $country_code = $billing->vat_country_code;
            $vat_num = $billing->vat_number;
            $customer = \Stripe\Customer::retrieve($billing->stripe_id);

            $promocode = NULL;
            $promocode_discount = 0;

          $ps = PromoSubscription::where('seller_id',$seller_id)
                               ->where('is_used',0)
                               ->first();

          if(isset($ps))
          {
            $voucher_code = $ps->voucher_code;

            $pc = PromoCodeA::where('voucher_code',$voucher)
                                  ->first();
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
                }
            }
          }

          $pn = PromoSubscription::where('seller_id',$seller_id)
                                   ->where('is_used',0)
                                   ->first();

          if(isset($pn))
          {
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
              else
              {
                  $promocode_amount = current($promocode_amount, strtoupper($promocode_currency),
                      strtoupper($preferred_currency),false);

                  if($amount >= $promocode_amount)
                  {
                      $amount = $amount - $promocode_amount;
                  }
                  else
                  {
                      $amount = 0;
                  }
              }
            }

            $data = $this->calculateVat($seller_id, $amount/100, $vat_num, $country_id, $country_code, $request->currency);
            $amount = $amount + ($data['vat']*100);

            if ($amount != 0) {
              try {
                  $charge = \Stripe\Charge::create(array(
                      "amount" => (int)($amount),
                      "currency" => $request->currency,
                      "customer" => $customer->id,
                      "description" => 'FBA Refunds'
                  ));

                  Session::flash('success', 'FBA Refunds activated successfully. Payment successfully done.');

              } catch(\Stripe\Error\Card $e) {

                  Session::flash('error', 'Your credit card was been declined. Please try again or contact us.');
              }
            } else {

              Session::flash('success', 'FBA Refunds reactivated successfully. Payment successfully done.');
            }

                  $data = $this->calculateVat($seller_id, $amount/100, $vat_num, $country_id, $country_code, $request->currency);
                  DB::transaction(function() use ($seller_id,$request,$data,$amount_us,$amount_ca,$amount_uk,$amount_fr,$amount_de,$amount_es,$amount_it,$promocode,$promocode_discount) {
                    $bi = new BillingInvoice;
                    $bi->seller_id = $seller_id;
                    $bi->invoice_number = $data['latestID'] + 1;
                    $bi->product_description = 'FBA Refunds';
                    $bi->product_subscription = 'FBA Refunds';
                    $bi->amount = $data['amount'];
                    $bi->vat = $data['vat'];
                    $bi->country_code = $data['cc'];
                    $bi->currency = $request->currency;
                    $bi->promocode = $promocode;
                    $bi->promocode_discount = $promocode_discount/100;
                    $bi->status = $data['status'];
                    $bi->created_at = Carbon::now();
                    $bi->updated_at = Carbon::now();
                    $bi->save();

                    $fba = new FbaRefund;
                    $fba->seller_id = $seller_id;
                    $fba->is_activated = true;
                    $fba->payment_status = 'good';
                    $fba->nxt_date = Carbon::now()->addDays(30);
                    $fba->created_at = Carbon::now();
                    $fba->updated_at = Carbon::now();
                    $fba->save();

                    if ($amount_us) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_us;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_us*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'us';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_ca) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_ca;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_ca*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'ca';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_uk) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_uk;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_uk*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'uk';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_fr) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_fr;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_fr*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'fr';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_de) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_de;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_de*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'de';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_es) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_es;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_es*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'es';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_it) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_it;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_it*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'it';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                  });
                
                $fname = Auth::user()->seller->firstname;
                $lname = Auth::user()->seller->lastname;
                $email = Auth::user()->seller->email;
                $token = $data['latestID'] + 1;
                $this->sendInvoice($fname, $lname, $email, $token);

                //set promo subscription to is used = 1 after billing
                if(isset($pn))
                {
                $voucher_code = $pn->voucher_code;

                $pc = PromoCodeA::where('voucher_code', $voucher_code)
                                    ->first();

                  if($pc->voucher_type == 'num')
                  {
                    $pn->is_used = 1;
                    $pn->save();
                  }
                }
                return Redirect::back();
          } else {
            if ($request->with > 0) {
              $fba = new FbaRefund;
              $fba->seller_id = $seller_id;
              $fba->is_activated = true;
              $fba->payment_status = 'with records';
              $fba->nxt_date = Carbon::now()->addDays(30);
              $fba->created_at = Carbon::now();
              $fba->updated_at = Carbon::now();
              $fba->save();
            } else {
              $fba = new FbaRefund;
              $fba->seller_id = $seller_id;
              $fba->is_activated = true;
              $fba->payment_status = 'without records';
              $fba->nxt_date = Carbon::now();              
              $fba->created_at = Carbon::now();
              $fba->updated_at = Carbon::now();
              $fba->save();
            }


            Session::flash('success', 'FBA Refunds activated successfully.');
            return Redirect::back();
          }
        }
      } else {
        $fba_refund_id = $fba_refund->id;
        
        if ($fba_refund->is_activated == true) {
          return Redirect::back();  
        } else {
          // if user is deactivated and wants to activate again
          //charge customer
          if (($temp_amount*0.1) >= 50) {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $billing = Billing::find(Auth::user()->seller->billing->id);            
            $country_id = $billing->country_id;
            $vat_num = $billing->vat_number;
            $country_code = $billing->vat_country_code;
            $customer = \Stripe\Customer::retrieve($billing->stripe_id);

            $promocode = NULL;
            $promocode_discount = 0;

            $ps = PromoSubscription::where('seller_id',$seller_id)
                               ->where('is_used',0)
                               ->first();

          if(isset($ps))
          {
            $voucher_code = $ps->voucher_code;

            $pc = PromoCodeA::where('voucher_code',$voucher)
                                  ->first();
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
                }
            }
          }

          $pn = PromoSubscription::where('seller_id',$seller_id)
                                   ->where('is_used',0)
                                   ->first();

          if(isset($pn))
          {
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
              else
              {
                  $promocode_amount = current($promocode_amount, strtoupper($promocode_currency),
                      strtoupper($preferred_currency),false);

                  if($amount >= $promocode_amount)
                  {
                      $amount = $amount - $promocode_amount;
                  }
                  else
                  {
                      $amount = 0;
                  }
              }
            }

            $data = $this->calculateVat($seller_id, $amount/100, $vat_num, $country_id, $country_code, $request->currency);
            $amount = $amount + ($data['vat']*100);

            if ($amount != 0) {
              try {
                  $charge = \Stripe\Charge::create(array(
                      "amount" => (int)($amount),
                      "currency" => $request->currency,
                      "customer" => $customer->id,
                      "description" => 'FBA Refunds'
                  ));

                  Session::flash('success', 'FBA Refunds reactivated successfully. Payment successfully done.');

              } catch(\Stripe\Error\Card $e) {

                  Session::flash('error', 'Your credit card was been declined. Please try again or contact us.');
              }
            } else {
              Session::flash('success', 'FBA Refunds reactivated successfully. Payment successfully done.');
            }

                  $data = $this->calculateVat($seller_id, $amount/100, $vat_num, $country_id, $country_code, $request->currency);
                  DB::transaction(function() use ($seller_id,$request,$data,$amount_us,$amount_ca,$amount_uk,$amount_fr,$amount_de,$amount_es,$amount_it,$promocode,$promocode_discount,$fba_refund_id) {
                    $bi = new BillingInvoice;
                    $bi->seller_id = $seller_id;
                    $bi->invoice_number = $data['latestID'] + 1;
                    $bi->product_description = 'FBA Refunds';
                    $bi->product_subscription = 'FBA Refunds';
                    $bi->amount = $data['amount'];
                    $bi->vat = $data['vat'];
                    $bi->country_code = $data['cc'];
                    $bi->currency = $request->currency;
                    $bi->promocode = $promocode;
                    $bi->promocode_discount = $promocode_discount/100;
                    $bi->status = $data['status'];
                    $bi->created_at = Carbon::now();
                    $bi->updated_at = Carbon::now();
                    $bi->save();

                    $fba_refund = FbaRefund::find($fba_refund_id);
                    $fba_refund->is_activated = true;
                    $fba_refund->payment_status = 'good';
                    $fba_refund->nxt_date = Carbon::now()->addDays(30);
                    $fba_refund->updated_at = Carbon::now();
                    $fba_refund->save();

                    if ($amount_us) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_us;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_us*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'us';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_ca) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_ca;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_ca*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'ca';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_uk) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_uk;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_uk*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'uk';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_fr) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_fr;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_fr*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'fr';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_de) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_de;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_de*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'de';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_es) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_es;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_es*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'es';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                    if ($amount_it) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_it;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_it*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $request->currency;
                      $fba_tran->country_code = 'it';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                  });

                $fname = Auth::user()->seller->firstname;
                $lname = Auth::user()->seller->lastname;
                $email = Auth::user()->seller->email;
                $token = $data['latestID'] + 1;
                $this->sendInvoice($fname, $lname, $email, $token);

                //set promo subscription to is used = 1 after billing
                if(isset($pn))
                {
                $voucher_code = $pn->voucher_code;

                $pc = PromoCodeA::where('voucher_code', $voucher_code)
                                    ->first();

                  if($pc->voucher_type == 'num')
                  {
                    $pn->is_used = 1;
                    $pn->save();
                  }
                }

                return Redirect::back();
          } else {
            if ($request->with > 0) {
              $fba_refund = FbaRefund::find($fba_refund_id);
              $fba_refund->is_activated = true;
              $fba_refund->payment_status = 'with records';
              $fba_refund->nxt_date = Carbon::now()->addDays(30);
              $fba_refund->updated_at = Carbon::now();
              $fba_refund->save();
            } else {
              $fba_refund = FbaRefund::find($fba_refund_id);
              $fba_refund->is_activated = true;
              $fba_refund->payment_status = 'without records';
              $fba_refund->nxt_date = Carbon::now();
              $fba_refund->updated_at = Carbon::now();
              $fba_refund->save();
            }

            Session::flash('success', 'FBA Refunds reactivated successfully.');
            return Redirect::back();
          }
        }
      }
    }

  public function deactivate()
  {
    //get seller_id
    $seller_id = Auth::user()->seller_id;

    $fba_refund = FbaRefund::where('seller_id', '=', $seller_id)->first();

    if (isset($fba_refund)) {
      $fba_refund->is_activated = false;
      $fba_refund->save();

      // COMMENTED TEMPORARY FOR REMOVING DIY UPON ACTIVATING
      /*Session::flash('success', 'FBA Refunds deactivated successfully.');
      return Redirect::back();*/

      return  response()
                ->json([
                    'message' => 'success',
                ]);

    } else {
      // COMMENTED TEMPORARY FOR REMOVING DIY UPON ACTIVATING
      /*return Redirect::back();*/

      return  response()
                ->json([
                    'message' => 'fail',
                ]);

    }
  }

  public function activate(Request $request)
  {
    //get seller_id
    $seller_id = Auth::user()->seller_id;

    /*ADDED BY JASON FOR TEMPORARY REMOVE DIY OPTION UPON ACTIVATING FBA BOTH SUBSCRIPTION AND FBA MODULE*/
    $payment_method = '';
    $billing = Billing::where('seller_id', '=', $seller_id)->first();
    if (isset($billing)) {
      $payment_method = $billing->payment_method;
    }else{
      return  response()
                ->json([
                    'message' => 'fail',
                ]);
    }
    /*END*/

    $fba_refund = FbaRefund::where('seller_id', '=', $seller_id)->first();

    if (!isset($fba_refund)) {
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
      $fba = new FbaRefund;
      $fba->seller_id = $seller_id;
      $fba->is_activated = true;
      $fba->payment_status = '';
      $fba->nxt_date = $nbd;
      $fba->created_at = Carbon::now();
      $fba->updated_at = Carbon::now();
      $fba->fba_mode = $request->fba_mode;
      if ($fba->save()) {
        if ($fba->fba_mode == 'DIY') {
          $frdt_new = new FbaRefundDiyTran;
          $frdt_new->seller_id = $seller_id;
          $frdt_new->amount_to_pay = 30;
          $frdt_new->is_pro_rated = false;
          $frdt_new->created_at = Carbon::now();
          $frdt_new->updated_at = Carbon::now();
          $frdt_new->save();
        }
      }

      // COMMENTED TEMPORARY FOR REMOVING DIY UPON ACTIVATING
      /*Session::flash('success', 'FBA Refunds activated successfully.');
      return Redirect::back();*/

      return  response()
                ->json([
                    'message' => 'success',
                ]);

    } else {
      $fba_refund_id = $fba_refund->id;

      if ($fba_refund->is_activated == true) {
        return Redirect::back();  
      } else {
        // if user is deactivated and wants to activate again
        $fba_refund = FbaRefund::find($fba_refund_id);
        $fba_refund->is_activated = true;
        $fba_refund->updated_at = Carbon::now();
        $fba_refund->fba_mode = $request->fba_mode;
        if ($fba_refund->save()) {
          if ($fba_refund->fba_mode == 'DIY') {
            $frdt_new = new FbaRefundDiyTran;
            $frdt_new->seller_id = $seller_id;
            $frdt_new->amount_to_pay = 30;
            $frdt_new->is_pro_rated = false;
            $frdt_new->created_at = Carbon::now();
            $frdt_new->updated_at = Carbon::now();
            $frdt_new->save();
          }
        }

        // COMMENTED TEMPORARY FOR REMOVING DIY UPON ACTIVATING
        /*Session::flash('success', 'FBA Refunds reactivated successfully.');
        return Redirect::back();*/

        return  response()
                ->json([
                    'message' => 'success',
                ]);
      }
    }
  }

  public function updateFbaMode(Request $request)
  {
    //get seller_id
    $seller_id = Auth::user()->seller_id;

    $fba_refund = FbaRefund::where('seller_id', '=', $seller_id)->first();

    $fba_refund_mode = $fba_refund->fba_mode;
    $fba = FbaRefund::find($fba_refund->id);
    $fba->updated_at = Carbon::now();
    $fba->fba_mode = $request->fba_mode;

    if ($fba->save()) {

      $dt = Carbon::parse($fba->nxt_date);
      $diff = $dt->diffInDays(Carbon::now());
      $diff = $diff+1;
      $set_atp = 30/30.42*(int)$diff;

      if ($fba->fba_mode == 'MANAGE' && $fba_refund_mode != 'MANAGE'){
        $frdt_new = new FbaRefundDiyTran;
        $frdt_new->seller_id = $seller_id;
        $frdt_new->amount_to_pay = $set_atp*(-1);
        $frdt_new->is_pro_rated = true;
        $frdt_new->created_at = Carbon::now();
        $frdt_new->updated_at = Carbon::now();
        $frdt_new->save();
      } else if($fba->fba_mode == 'DIY' && $fba_refund_mode != 'DIY'){
        $frdt_new = new FbaRefundDiyTran;
        $frdt_new->seller_id = $seller_id;
        $frdt_new->amount_to_pay = $set_atp;
        $frdt_new->is_pro_rated = true;
        $frdt_new->created_at = Carbon::now();
        $frdt_new->updated_at = Carbon::now();
        $frdt_new->save();
      }
    }

    Session::flash('success', 'FBA Refunds filing mode updated.');
    return Redirect::back();
  }

  ### START FBA TABLES ###
    public function getfbasellers(){
        $email = Auth::user()->seller->email;
        $as = AdminSeller::where('seller_email', '=', $email)->get();
        $response = array();
        foreach ($as as $val) {
            $s = Seller::where('email', '=', $val->seller_email)->first();
            $data = array();
            $data['rowId'] = $val->id;
            $data['DT_RowId'] = $val->id;
            $data['id_seller'] = $s->id;
            $data['country'] = $val->country_code;
            $data['fba_mode'] = $val->fba_mode;
            $difference = $val->total_owed - $val->total_saved;
            if ($val->country_code == 'us') $country = 'United States';
            elseif ($val->country_code == 'ca') $country = 'Canada';
            elseif ($val->country_code == 'uk') $country = 'United Kingdom';
            elseif ($val->country_code == 'fr') $country = 'France';
            elseif ($val->country_code == 'de') $country = 'Germany';
            elseif ($val->country_code == 'it') $country = 'Italy';
            elseif ($val->country_code == 'es') $country = 'Spain';
            $data[] = $country;
            if ($val->currency == 'usd') $curr = '$';
            elseif ($val->currency == 'gbp') $curr = '£';
            elseif ($val->currency == 'eur') $curr = '€';
            $data[] = $curr.' '.number_format($difference, 2);
            $data[] = $curr.' '.number_format($val->total_saved, 2);
            $response[] = $data;
        }
        echo json_encode($response);
    }

    public function getfbasellersFiltered(Request $request){
        $companyName = $request->companyname;
        $country = $request->country;
        $email = Auth::user()->seller->email;

        if((!empty($companyName)) && (!empty($country)))
        {
            $as = DB::table('admin_sellers')
                  ->where('company_name', 'like', '%'.$companyName.'%')
                  ->where('country_code', '=', $country)
                  ->where('seller_email', '=', $email)
                  ->get();
        }
        else if(empty($companyName) && (!empty($country)))
        {
            $as = DB::table('admin_sellers')
                  ->where('country_code','=', $country)
                  ->where('seller_email', '=', $email)
                  ->get();
        }
        else if(!empty($companyName) && (empty($country)))
        {
            $as = DB::table('admin_sellers')
                  ->where('company_name', 'like', '%'.$companyName.'%')
                  ->where('seller_email', '=', $email)
                  ->get();
        }
        else
        {
            $as = AdminSeller::where('seller_email', '=', $email)->get();
        }

          $response = array();
          foreach ($as as $val) {
          $s = Seller::where('email', '=', $val->seller_email)->first();
          $data = array();
          $data['rowId'] = $val->id;
          $data['DT_RowId'] = $val->id;
          $data['id_seller'] = $s->id;
          $data['country'] = $val->country_code;
          $data['fba_mode'] = $val->fba_mode;
          $total_saved = (int)$val->total_saved;
          $difference = $val->total_owed - $val->total_saved;
          if ($val->country_code == 'us') $country = 'United States';
          elseif ($val->country_code == 'ca') $country = 'Canada';
          elseif ($val->country_code == 'uk') $country = 'United Kingdom';
          elseif ($val->country_code == 'fr') $country = 'France';
          elseif ($val->country_code == 'de') $country = 'Germany';
          elseif ($val->country_code == 'it') $country = 'Italy';
          elseif ($val->country_code == 'es') $country = 'Spain';
          $data[] = $country;
          if ($val->currency == 'usd') $curr = '$';
          elseif ($val->currency == 'gbp') $curr = '£';
          elseif ($val->currency == 'eur') $curr = '€';
          $data[] = $curr.' '.number_format($difference, 2);
          $data[] = $curr.' '.number_format($val->total_saved, 2);
          $response[] = $data;
        }
        echo json_encode($response);
    }

    public function update_adminsellers(Request $request){
      $id = $request->row_id;
      $colindex = $request->column;
      $newval = $request->newval;

      $col = $this->_get_adminseller_column_by_UItable_columnindex($colindex);

      $a = $this->_make_adminsellers_update_array($id,$col,$newval);

      $this->adminsellers->updateRecord($a);

      return $newval;
    }

    public function update_adminOIC(Request $request){
      $response = (object) null;

      $id = $request->row_id;
      $colindex = $request->column;
      $newval = $request->newval;
      $order_id = $request->order_id;
      $claim_amount = $request->claim_amount;

      if ($colindex == 4) {
        $case_id = $newval;
        $r = Reimbursement::where('case_id', '=', $case_id)
                          ->get();

        $rid = array();
        $i = 0;
        $tot_amount_r = 0;
        foreach ($r as $v) {
            $i++;
            $rid[$i] = $v->reimbursement_id;
            if ($v->amount_total == 0) {
                $tot_amount_r += ($v->amount_per_unit*$v->quantity_reimbursed_total);
            } else {
                $tot_amount_r += $v->amount_total;
            }
        }
      }

      $oic = OrderIdClaim::find($id);
      if (!isset($oic)) {
        $oic = new OrderIdClaim;
        $oic->id = $id;
      }
      $status = $oic->status;

      if (isset($rid[1])) {
        $oic->reimbursement_id1 = $rid[1];
        $response->rid1 = $rid[1];
      } else {
        $response->rid1 = '';
      }
      if (isset($rid[2])) {
        $oic->reimbursement_id2 = $rid[2];
        $response->rid2 = $rid[2];
      } else {
        $response->rid2 = '';
      }
      if (isset($rid[3])) {
        $oic->reimbursement_id3 = $rid[3];
        $response->rid3 = $rid[3];
      } else {
        $response->rid3 = '';
      }
      if (isset($tot_amount_r)) {
        if ($tot_amount_r == 0) {
            $response->tar = 0;
            $response->dif = round($claim_amount,2);
        } else {
            $oic->total_amount_reimbursed = round($tot_amount_r,2);
            $oic->difference =  (double)$claim_amount - round($tot_amount_r,2);
            $response->tar = round($tot_amount_r,2);
            $response->dif = round((double)$claim_amount - round($tot_amount_r,2),2);
        }
      } else {
        $response->tar = 0;
        $response->dif = round($claim_amount);
      }

      if ($status == 'All Ok' || $status == 'Refund issued by seller' || $status == 'Amz won'."'".'t refund difference') {        
        $response->dif = 0;
      }

      if ($colindex == 4) {
        $oic->support_ticket = $newval;
      } elseif ($colindex == 11) {
        $oic->comments = $newval;
      }

      $oic->save();

      $response->value = $newval;
      return json_encode($response);
    }

    public function update_adminFNSKU(Request $request){
      $response = (object) null;

      $id = $request->row_id;
      $colindex = $request->column;
      $newval = $request->newval;
      $fnsku = $request->fnsku;
      $total_owed = $request->total_owed;

      if ($colindex == 4) {
        $case_id = $newval;

        $r = Reimbursement::where('case_id', '=', $case_id)
                          ->get();

        $rid = array();
        $i = 0;
        $tot_amount_r = 0;
        foreach ($r as $v) {
            $i++;
            $rid[$i] = $v->reimbursement_id;            
            if ($v->amount_total == 0) {
                $tot_amount_r += ($v->amount_per_unit*$v->quantity_reimbursed_total);
            } else {
                $tot_amount_r += $v->amount_total;
            }
        }
      }

      $fc = FnskuClaim::find($id);
      if (!isset($fc)) {
        $fc = new FnskuClaim;
        $fc->id = $id;
      }
      $status = $fc->status;

      if (isset($rid[1])) {
        $fc->reimbursement_id1 = $rid[1];
        $response->rid1 = $rid[1];
      } else {
        $response->rid1 = '';
      }
      if (isset($rid[2])) {
        $fc->reimbursement_id2 = $rid[2];
        $response->rid2 = $rid[2];
      } else {
        $response->rid2 = '';
      }
      if (isset($rid[3])) {
        $fc->reimbursement_id3 = $rid[3];
        $response->rid3 = $rid[3];
      } else {
        $response->rid3 = '';
      }
      if (isset($tot_amount_r)) {
        if ($tot_amount_r == 0) {
            $response->tar = 0;
            $response->dif = round($total_owed,2);
        } else {
            $fc->total_amount_reimbursed = round($tot_amount_r,2);
            $fc->difference =  (double)$total_owed - round($tot_amount_r,2);
            $response->tar = round($tot_amount_r,2);
            $response->dif = round((double)$total_owed - round($tot_amount_r,2),2);
        }
      } else {
        $response->tar = 0;
        $response->dif = round($total_owed,2);
      }

      if ($status == 'All Ok' || $status == 'Refund issued by seller' || $status == 'Amz won'."'".'t refund difference') {        
        $response->dif = 0;
      }

      if ($colindex == 4) {
        $fc->support_ticket = $newval;
      } elseif ($colindex == 11) {
        $fc->comments = $newval;
      }

      $fc->save();

      $response->value = $newval;
      return json_encode($response);
    }

    private function _get_adminseller_column_by_UItable_columnindex($i){
      $r = '';
      switch($i){
        case 3: $r = 'central_login_email'; break;
        case 4: $r = 'central_login_password'; break;
        case 5: $r = 'support_cases'; break;
        case 10: $r = 'status'; break;
      }
      return $r;
    }

    private function _make_adminsellers_update_array($id,$col,$newval){
      return array(
        'id'=>$id,
        $col=>$newval
      );
    }

    public function updateStatusOIC(Request $request){
      
      $oci = OrderIdClaim::where('order_id', '=', $request->id)
                        ->first();
      $oci->status = $request->value;
      $oci->save();

      $seller_id = $oci->seller_id;

      $s = Seller::where('id', $seller_id)
                        ->first();

      $email = $s->email;
      $country = $oci->country_code;
      $total_saved = $this->getTotalSaved1($email,$seller_id,$country);
    }


    public function getSellerOIC(Request $request){
        $seller_id = $request->seller_id;
        $country = $request->country;
        $oic = OrderIdClaim::where('seller_id', '=', $seller_id)
                        ->where('country_code', '=', $country)
                        ->get();
        $response = array();
        foreach ($oic as $val) {
            if ($val->total_refunded < 0) {

            $open = ($val->status == 'Open') ? 'selected' : '';
            $ok = ($val->status == 'All Ok') ? 'selected' : '';
            $refund = ($val->status == 'Refund issued by seller') ? 'selected' : '';
            $amz = ($val->status == 'Amz won'."'".'t refund difference') ? 'selected' : '';

            $status =   '<div>'
                    .'<select class="form-control" onchange="oicupdateStatus(this)" id="'.$val->order_id.'" style="font-size: 12px;padding: 0px 0px">'
                      .'<option '.$open.'>Open</option>'
                      .'<optgroup label="Closed">'
                      .'<option '.$ok.'>All Ok</option>'
                      .'<option '.$refund.'>Refund issued by seller</option>'
                      .'<option '.$amz.'>Amz won'."'".'t refund difference</option>'
                      .'</optgroup>'
                    .'</select>'
                .'</div>';
                
                $array = ['All Ok','Refund issued by seller','Amz won'."'".'t refund difference'];

                
                $hidden = '<input type="hidden" id="clipboard-'.$val->order_id.'" value="'.$val->claim_type.'" />';
                $hidden0 = '<input type="hidden" id="clipboard0-'.$val->order_id.'" value="'.$val->status.'" />';
                $hidden1 = '<input type="hidden" id="clipboard1-'.$val->order_id.'" value="Order ID: '.$val->order_id.'" />';
                $hidden2 = '<input type="hidden" id="clipboard2-'.$val->order_id.'" value="Amount due: '.$val->claim_amount.'" />';
                $hidden3 = '<input type="hidden" id="clipboard3-'.$val->order_id.'" value="Reason: '.$val->detailed_disposition.'" />';
                $hidden4 = '<input type="hidden" id="clipboard4-'.$val->order_id.'" value="Reimbursement reason: '.$val->detailed_disposition.'" />';
                $hidden5 = '<input type="hidden" id="clipboard5-'.$val->order_id.'" value="Reimbursement amount: '.$val->total_adjusted.'" />';
                $hidden6 = '<input type="hidden" id="clipboard6-'.$val->order_id.'" value="'.$val->claim_amount.'" />';
                $hidden7 = '<input type="hidden" id="clipboard7-'.$val->order_id.'" value="Difference: '.round($val->difference,2).'" />';
                $hidden8 = '<input type="hidden" id="clipboard8-'.$val->order_id.'" value="'.$val->return_reason.'" />';
                $hidden9 = '<input type="hidden" id="clipboard9-'.$val->order_id.'" value="'.$val->quantity_unsellable.'" />';
                $hidden10 = '<input type="hidden" id="clipboard10-'.$val->order_id.'" value="'.$country.'" />';
                $hidden11 = '<input type="hidden" id="clipboard11-'.$val->order_id.'" value="'.$val->claim_amount.'" />';
                $calculation1 = ($val->total_refunded*(-1));
                $calculation = (string)$calculation1.'-'.(string)$val->total_ordered;
                $hidden12 = '<input type="hidden" id="clipboard12-'.$val->order_id.'" value="'.$calculation.'" />';          

                $fnsku_string = '';
                $total_owed = 0;
                if ($val->return_reason == 'MISSED_ESTIMATED_DELIVERY' && $val->quantity_unsellable > 0) {
                    $dd_arr = array(); 
                    $dd_arr[] = 'DEFECTIVE';        
                    $dd_arr[] = 'CUSTOMER_DAMAGED';        
                    $dd_arr[] = 'CUSTOMERDAMAGED';

                    $query = DB::connection('mysql2')
                        ->table('returns_reports')
                        ->where('returns_reports.order_id', '=', $val->order_id)
                        ->where('returns_reports.reason', '=', 'MISSED_ESTIMATED_DELIVERY')
                        ->whereIn('returns_reports.detailed_disposition', $dd_arr)
                        ->get([
                          'returns_reports.fnsku',
                          'returns_reports.asin',
                          'returns_reports.quantity'                          
                        ]);

                    $fnsku_arr = array();
                    $asin_arr = array();
                    foreach ($query as $val) {
                        $fnsku_arr[] = $val->fnsku;
                        $asin_arr[$val->asin] += $val->quantity;
                    }
                    $fnsku_string = implode(", ",$fnsku_arr);

                    if ($country == 'us' || $country == 'ca') {
                        $mkp = 1;
                    } else {
                        $mkp = 2;
                    }

                    foreach ($asin_arr as $key => $value) {
                        $iar = new InventoryAdjustmentReport;
                        $gno = $iar->getNbOrder($key, $mkp, $country);
                        if ($gno->quantity != 0) {
                            $total_owed += round((round($gno->revenue, 2)/$gno->quantity)*($value),2);
                        } else {
                            $total_owed += 0;
                        }
                    }
                }
                $hidden13 = '<input type="hidden" id="clipboard13-'.$val->order_id.'" value="'.$fnsku_string.'" />';          
                $hidden14 = '<input type="hidden" id="clipboard14-'.$val->order_id.'" value="'.$val->quantity_unsellable.'" />';            
                $hidden15 = '<input type="hidden" id="clipboard15-'.$val->order_id.'" value="'.round($total_owed,2).'" />';

                $clip = '<button class="btn btn-primary btn-sm no-radius" id="clip-'.$val->order_id.'" onclick="oicClip(this)"> <i class="fa fa-clipboard"></i></button>';

                $data = array();
                $data['DT_RowId'] = $val->id;
                $data[] = $clip.' '.$val->order_id.$hidden.$hidden0.$hidden1.$hidden2.$hidden3.$hidden4.$hidden5.$hidden6.$hidden7.$hidden8.$hidden9.$hidden10.$hidden11.$hidden12.$hidden13.$hidden14.$hidden15;
                $data[] = $val->claim_type;
                $data[] = $val->detailed_disposition;
                $data[] = $val->claim_amount;
                $data[] = $val->support_ticket;
                $data[] = $val->reimbursement_id1;
                $data[] = $val->reimbursement_id2;
                $data[] = $val->reimbursement_id3;
                $data[] = $val->total_amount_reimbursed;
                (in_array($val->status,$array) == true) ? $data[] = 0 : $data[] = round($val->difference,2);
                $data[] = $status;
                $data[] = $val->comments;
                $response[] = $data;
            }
        }
        echo json_encode($response);
    }

    public function getSellerOICFiltered(Request $request){
        $seller_id = $request->seller_id;
        $country = $request->country;

        $claimType = $request->claimtype;
        $supportTicket = $request->support_ticket;
        $status = $request->status;
        $orderId = $request->orderid;

       $oic = DB::connection('mysql2')->table('order_id_claims')
            ->where(function($query) use ($seller_id,$country,$status,$supportTicket,$orderId){
            $query->where('seller_id',$seller_id);
            $query->where('country_code',$country);

            if(!empty($claimType))
            {
                $query->where('claim_type',$claimType);
            }

            if(!empty($status) && $status == 'Open')
            {
                $query->where(function ($query) {
                $query->whereNull('status');
                $query->OrWhere('status','Open');
                });
            }

            else if(!empty($status) && $status != 'Open')
            {
                $query->where('status', $status);
            }

            if(!empty($supportTicket))
            {
                $query->where('support_ticket',$supportTicket);
            }

            if(!empty($orderId))
            {
                $query->where('order_id',$orderId);
            }

           })
        ->get();
    

        $response = array();

        foreach ($oic as $val) {            
            if ($val->total_refunded < 0) {
                
                $open = ($val->status == 'Open') ? 'selected' : '';
                $ok = ($val->status == 'All Ok') ? 'selected' : '';
                $refund = ($val->status == 'Refund issued by seller') ? 'selected' : '';
                $amz = ($val->status == 'Amz won'."'".'t refund difference') ? 'selected' : '';

                $status =   '<div>'
                        .'<select class="form-control" onchange="oicupdateStatus(this)" id="'.$val->order_id.'" style="font-size: 12px;padding: 0px 0px">'
                          .'<option '.$open.'>Open</option>'
                          .'<optgroup label="Closed">'
                          .'<option '.$ok.'>All Ok</option>'
                          .'<option '.$refund.'>Refund issued by seller</option>'
                          .'<option '.$amz.'>Amz won'."'".'t refund difference</option>'
                          .'</optgroup>'
                        .'</select>'
                    .'</div>';

                $array = ['All Ok','Refund issued by seller','Amz won'."'".'t refund difference'];

                
                $hidden = '<input type="hidden" id="clipboard-'.$val->order_id.'" value="'.$val->claim_type.'" />';
                $hidden0 = '<input type="hidden" id="clipboard0-'.$val->order_id.'" value="'.$val->status.'" />';
                $hidden1 = '<input type="hidden" id="clipboard1-'.$val->order_id.'" value="Order ID: '.$val->order_id.'" />';
                $hidden2 = '<input type="hidden" id="clipboard2-'.$val->order_id.'" value="Amount due: '.$val->claim_amount.'" />';
                $hidden3 = '<input type="hidden" id="clipboard3-'.$val->order_id.'" value="Reason: '.$val->detailed_disposition.'" />';
                $hidden4 = '<input type="hidden" id="clipboard4-'.$val->order_id.'" value="Reimbursement reason: '.$val->detailed_disposition.'" />';
                $hidden5 = '<input type="hidden" id="clipboard5-'.$val->order_id.'" value="Reimbursement amount: '.$val->total_adjusted.'" />';
                $hidden6 = '<input type="hidden" id="clipboard6-'.$val->order_id.'" value="'.$val->claim_amount.'" />';
                $hidden7 = '<input type="hidden" id="clipboard7-'.$val->order_id.'" value="Difference: '.round($val->difference,2).'" />';
                $hidden8 = '<input type="hidden" id="clipboard8-'.$val->order_id.'" value="'.$val->return_reason.'" />';
                $hidden9 = '<input type="hidden" id="clipboard9-'.$val->order_id.'" value="'.$val->quantity_unsellable.'" />';
                $hidden10 = '<input type="hidden" id="clipboard10-'.$val->order_id.'" value="'.$country.'" />';
                $hidden11 = '<input type="hidden" id="clipboard11-'.$val->order_id.'" value="'.$val->claim_amount.'" />';
                $calculation1 = ($val->total_refunded*(-1));
                $calculation = (string)$calculation1.'-'.(string)$val->total_ordered;
                $hidden12 = '<input type="hidden" id="clipboard12-'.$val->order_id.'" value="'.$calculation.'" />';          

                $fnsku_string = '';
                $total_owed = 0;
                if ($val->return_reason == 'MISSED_ESTIMATED_DELIVERY' && $val->quantity_unsellable > 0) {
                    $dd_arr = array(); 
                    $dd_arr[] = 'DEFECTIVE';        
                    $dd_arr[] = 'CUSTOMER_DAMAGED';        
                    $dd_arr[] = 'CUSTOMERDAMAGED';

                    $query = DB::connection('mysql2')
                        ->table('returns_reports')
                        ->where('returns_reports.order_id', '=', $val->order_id)
                        ->where('returns_reports.reason', '=', 'MISSED_ESTIMATED_DELIVERY')
                        ->whereIn('returns_reports.detailed_disposition', $dd_arr)
                        ->get([
                          'returns_reports.fnsku',
                          'returns_reports.asin',
                          'returns_reports.quantity'                          
                        ]);

                    $fnsku_arr = array();
                    $asin_arr = array();
                    foreach ($query as $val) {
                        $fnsku_arr[] = $val->fnsku;
                        $asin_arr[$val->asin] += $val->quantity;
                    }
                    $fnsku_string = implode(", ",$fnsku_arr);

                    if ($country == 'us' || $country == 'ca') {
                        $mkp = 1;
                    } else {
                        $mkp = 2;
                    }

                    foreach ($asin_arr as $key => $value) {
                        $iar = new InventoryAdjustmentReport;
                        $gno = $iar->getNbOrder($key, $mkp, $country);
                        if ($gno->quantity != 0) {
                            $total_owed += round((round($gno->revenue, 2)/$gno->quantity)*($value),2);
                        } else {
                            $total_owed += 0;
                        }
                    }
                }
                $hidden13 = '<input type="hidden" id="clipboard13-'.$val->order_id.'" value="'.$fnsku_string.'" />';          
                $hidden14 = '<input type="hidden" id="clipboard14-'.$val->order_id.'" value="'.$val->quantity_unsellable.'" />';            
                $hidden15 = '<input type="hidden" id="clipboard15-'.$val->order_id.'" value="'.round($total_owed,2).'" />';

                $clip = '<button class="btn btn-primary btn-sm no-radius" id="clip-'.$val->order_id.'" onclick="oicClip(this)"> <i class="fa fa-clipboard"></i></button>';

                $data = array();
                $data['DT_RowId'] = $val->id;
                $data[] = $clip.' '.$val->order_id.$hidden.$hidden0.$hidden1.$hidden2.$hidden3.$hidden4.$hidden5.$hidden6.$hidden7.$hidden8.$hidden9.$hidden10.$hidden11.$hidden12.$hidden13.$hidden14.$hidden15;
                $data[] = $val->claim_type;
                $data[] = $val->detailed_disposition;
                $data[] = $val->claim_amount;
                $data[] = $val->support_ticket;
                $data[] = $val->reimbursement_id1;
                $data[] = $val->reimbursement_id2;
                $data[] = $val->reimbursement_id3;
                $data[] = $val->total_amount_reimbursed;
                (in_array($val->status,$array) == true) ? $data[] = 0 : $data[] = round($val->difference,2);
                $data[] = $status;
                $data[] = $val->comments;
                $response[] = $data;
  
           }//end if
       }//end forloop
      echo json_encode($response);
    }//end method

    public function updateStatusFNSKU(Request $request){
    
      $fnsku = $request->id;
      $fc = FnskuClaim::where('fnsku', '=', $request->id)
                        ->first();
      $fc->status = $request->value;
      $fc->save();

      $seller_id = $fc->seller_id;

      $s = Seller::where('id', $seller_id)
                        ->first();

      $email = $s->email;
      $country = $fc->country_code;
      $this->getTotalSaved1($email,$seller_id,$country);
    }

    public function getTotalSaved1($email,$seller_id,$country)
    {
        $total_saved = 0;
        $oic = OrderIdClaim::where('seller_id', '=', $seller_id)
                                   ->where('country_code', '=', $country)
                                   ->whereIn('status', ['All Ok', 'Refund issued by seller', 'Amz won\'t refund difference'])
                                   ->get();

        foreach($oic as $oc)
        {
            $total_saved += $oc->total_amount_reimbursed;
        }

        $fnsku = FnskuClaim::where('seller_id', '=', $seller_id)
                                   ->where('country_code', '=', $country)
                                   ->whereIn('status', ['All Ok', 'Refund issued by seller', 'Amz won\'t refund difference'])
                                   ->get();

        foreach($fnsku as $fs)
        {
            $total_saved += $fs->total_amount_reimbursed;
        }

        $curr = '';
        if($country == 'usd')
        {
            $curr = 'USD';
        }
        else if($country == 'ca')
        {
            $curr = 'CAD';
        }
        else if($country == 'uk')
        {
            $curr = 'GBP';
        }
        else if($country == 'de' || $country == 'it' || $country == 'es' || $country == 'fr')
        {
            $curr = 'EUR';
        }

        $ad = AdminSeller::where('seller_email', $email)
                        ->where('country_code', $country)
                         ->first();
        $currency = $ad->currency;
        $total_saved = currency($total_saved, $curr, strtoupper($currency), false);
        $ad->total_saved = $total_saved;
        $ad->save();
        return $total_saved;
    }

    public function getSellerFNSKU(Request $request){
        $seller_id = $request->seller_id;
        $country = $request->country;

        $fc = FnskuClaim::where('seller_id', '=', $seller_id)
                        ->where('country_code', '=', $country)
                        ->get();

        $response = array();

        foreach ($fc as $val) {
              
            $open = ($val->status == 'Open') ? 'selected' : '';
            $ok = ($val->status == 'All Ok') ? 'selected' : '';
            $refund = ($val->status == 'Refund issued by seller') ? 'selected' : '';
            $amz = ($val->status == 'Amz won'."'".'t refund difference') ? 'selected' : '';

                $status =   '<div>'
                    .'<select class="form-control" onchange="fnskuUpdateStatus(this)" id="'.$val->fnsku.'" style="font-size: 12px;padding: 0px 0px">'
                      .'<option '.$open.'>Open</option>'
                      .'<optgroup label="Closed">'
                      .'<option '.$ok.'>All Ok</option>'
                      .'<option '.$refund.'>Refund issued by seller</option>'
                      .'<option '.$amz.'>Amz won'."'".'t refund difference</option>'                      
                      .'</optgroup>'
                    .'</select>'
                .'</div>';
            
            $array = ['All Ok','Refund issued by seller','Amz won'."'".'t refund difference'];

            $hidden = '<input type="hidden" id="clipboard-'.$val->fnsku.'" value="'.$val->is_third_scenario.'" />';
            $hidden0 = '<input type="hidden" id="clipboard0-'.$val->fnsku.'" value="'.$val->status.'" />';
            $hidden1 = '<input type="hidden" id="clipboard1-'.$val->fnsku.'" value="FnSKU: '.$val->fnsku.'" />';
            $hidden2 = '<input type="hidden" id="clipboard2-'.$val->fnsku.'" value="Value per item: '.$val->average_value.'" />';
            $hidden3 = '<input type="hidden" id="clipboard3-'.$val->fnsku.'" value="Number of items Lost: '.$val->items_lost.'" />';
            $hidden4 = '<input type="hidden" id="clipboard4-'.$val->fnsku.'" value="Number of items Damaged: '.$val->items_damaged.'" />';
            $hidden5 = '<input type="hidden" id="clipboard5-'.$val->fnsku.'" value="Total amount owed: '.$val->total_owed.'" />';

            $clip = '<button class="btn btn-primary btn-sm no-radius" id="clip-'.$val->fnsku.'" data-clip-id="'.$val->fnsku.'" onclick="skuClip(this)"> <i class="fa fa-clipboard"></i></button>';

            $data = array();
            $data['DT_RowId'] = $val->id;
            $data[] = $clip.''.$hidden.$hidden0.$hidden1.$hidden2.$hidden3.$hidden4.$hidden5.$val->fnsku;
            $data[] = $val->summation + $val->reimbursed_units;
            $data[] = $val->average_value;
            $data[] = $val->total_owed;
            $data[] = $val->support_ticket;
            $data[] = $val->reimbursement_id1;
            $data[] = $val->reimbursement_id2;
            $data[] = $val->reimbursement_id3;
            $data[] = $val->total_amount_reimbursed;
            (in_array($val->status,$array) == true) ? $data[] = 0 : $data[] = round($val->difference,2);
            $data[] = $status;
            $data[] = $val->comments;
            $response[] = $data;
        }
        echo json_encode($response);
    }

    public function getSellerFNSKUFiltered(Request $request){
        $seller_id = $request->seller_id;
        $country = $request->country;

        $supportTicket = $request->support_ticket;
        $status = $request->status;
        $fnsku = $request->fnsku;

        $fc = DB::connection('mysql2')->table('fnsku_claims')
            ->where(function($query) use ($seller_id,$country,$status,$supportTicket,$fnsku){
            $query->where('seller_id',$seller_id);
            $query->where('country_code',$country);

            if(!empty($status) && $status == 'Open')
            {
                $query->where(function ($query) {
                $query->whereNull('status');
                $query->OrWhere('status','Open');
                });
            }

            else if(!empty($status) && $status != 'Open')
            {
                $query->where('status', $status);
            }

            if(!empty($supportTicket))
            {
                $query->where('support_ticket',$supportTicket);
            }

            if(!empty($fnsku))
            {
                $query->where('fnsku',$fnsku);
            }

           })
        ->get();

        $response = array();
        
        foreach ($fc as $val) {
            
            $open = ($val->status == 'Open') ? 'selected' : '';
            $ok = ($val->status == 'All Ok') ? 'selected' : '';
            $refund = ($val->status == 'Refund issued by seller') ? 'selected' : '';
            $amz = ($val->status == 'Amz won'."'".'t refund difference') ? 'selected' : '';

                $status =   '<div>'
                    .'<select class="form-control" onchange="fnskuUpdateStatus(this)" id="'.$val->fnsku.'" style="font-size: 12px;padding: 0px 0px">'
                      .'<option '.$open.'>Open</option>'
                      .'<optgroup label="Closed">'
                      .'<option '.$ok.'>All Ok</option>'
                      .'<option '.$refund.'>Refund issued by seller</option>'
                      .'<option '.$amz.'>Amz won'."'".'t refund difference</option>'                      
                      .'</optgroup>'
                    .'</select>'
                .'</div>';
            
            $array = ['All Ok','Refund issued by seller','Amz won'."'".'t refund difference'];

            $hidden = '<input type="hidden" id="clipboard-'.$val->fnsku.'" value="'.$val->is_third_scenario.'" />';
            $hidden0 = '<input type="hidden" id="clipboard0-'.$val->fnsku.'" value="'.$val->status.'" />';
            $hidden1 = '<input type="hidden" id="clipboard1-'.$val->fnsku.'" value="FnSKU: '.$val->fnsku.'" />';
            $hidden2 = '<input type="hidden" id="clipboard2-'.$val->fnsku.'" value="Value per item: '.$val->average_value.'" />';
            $hidden3 = '<input type="hidden" id="clipboard3-'.$val->fnsku.'" value="Number of items Lost: '.$val->items_lost.'" />';
            $hidden4 = '<input type="hidden" id="clipboard4-'.$val->fnsku.'" value="Number of items Damaged: '.$val->items_damaged.'" />';
            $hidden5 = '<input type="hidden" id="clipboard5-'.$val->fnsku.'" value="Total amount owed: '.$val->total_owed.'" />';

            $clip = '<button class="btn btn-primary btn-sm no-radius" id="clip-'.$val->fnsku.'" data-clip-id="'.$val->fnsku.'" onclick="skuClip(this)"> <i class="fa fa-clipboard"></i></button>';
            
            $data = array();
            $data['DT_RowId'] = $val->id;
            $data[] = $clip.''.$hidden.$hidden0.$hidden1.$hidden2.$hidden3.$hidden4.$hidden5.$val->fnsku;
            $data[] = $val->summation + $val->reimbursed_units;
            $data[] = $val->average_value;
            $data[] = $val->total_owed;
            $data[] = $val->support_ticket;
            $data[] = $val->reimbursement_id1;
            $data[] = $val->reimbursement_id2;
            $data[] = $val->reimbursement_id3;
            $data[] = $val->total_amount_reimbursed;
            (in_array($val->status,$array) == true) ? $data[] = 0 : $data[] = round($val->difference,2);
            $data[] = $status;
            $data[] = $val->comments;
            $response[] = $data;
        }
        echo json_encode($response);
    }
  ### END FBA TABLES ###
    
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
      $is_trial = Auth::user()->seller->is_trial;

      if ($is_trial == 1) {
        $data->base_subscription = 'XL';
      } else {
        $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller_id)->first();
        if (isset($bss)) {
            $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                        ->where('currently_used', '=', true)
                                                        ->first();
            $data->base_subscription = $bsst->bs_name;
        }
      }

      return $data;
  }
}
