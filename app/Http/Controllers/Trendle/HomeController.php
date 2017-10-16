<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\SellerReview;
use App\CrmLoad;
use App\FbaRefundTran;
use App\Billing;
use App\FbaPreCalculation;
use App\AdminSeller;
use Carbon\Carbon;
use App\Todo;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;
use App\FbaRefund;
use App\PromoSubscription;
use DvK\Laravel\Vat\Facades\Countries;
use DvK\Laravel\Vat\Facades\Rates;
use DvK\Laravel\Vat\Facades\Validator;
use DB;
use App\FbaRefundDiyTran;
use App\PromoCodeA;
use App\MarketplaceAssign;
use App\AmazonSellerDetail;
use App\ProductReviewsReviews;
use App\Seller;


class HomeController extends Controller
{
  protected function guard()
  {
      return Auth::guard();

  }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $seller_id = Auth::user()->seller_id;
        $q = Billing::where('seller_id', $seller_id)->get();
        $currency = '';
        if(count($q)>0) $currency = $q[0]->preferred_currency;
        if(strtolower($currency) == 'eur') $currency = '€';
        else if(strtolower($currency) == 'gbp') $currency = '£';
        else $currency = '$';

        $email = Auth::user()->seller->email;
        $preferred_currency = Auth::user()->seller->preferred_currency;
        $curr = '';
        $total_owed = 0;
        $total_reimburse = 0;
        $total_refund_gbp = 0; 
        $total_refund_usd = 0;
        $total_refund_eur = 0;
        $total_saved_gbp = 0;
        $total_saved_usd = 0;
        $total_saved_eur = 0;
        $fpc = FbaPreCalculation::where('seller_id', '=', $seller_id)
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
        $total_owed = $total_refund_gbp + $total_refund_usd + $total_refund_eur;

        $as = AdminSeller::where('seller_email', '=', $email)
                                 ->get();

        if (count($as) > 0) {
            foreach ($as as $val) {
                if ($val->currency == 'gbp') {
                    $total_saved_gbp += $val->total_saved;
                } elseif ($val->currency == 'usd') {
                    $total_saved_usd += $val->total_saved;
                } elseif ($val->currency == 'eur') {
                    $total_saved_eur += $val->total_saved;
                }
            }
        }

        $total_saved_gbp = currency($total_saved_gbp, 'GBP', strtoupper($preferred_currency), false);
        $total_saved_usd = currency($total_saved_usd, 'USD', strtoupper($preferred_currency), false);
        $total_saved_eur = currency($total_saved_eur, 'EUR', strtoupper($preferred_currency), false);
        $total_reimburse = $total_saved_gbp + $total_saved_usd + $total_saved_eur;

        $products = ProductReviewsReviews::where('seller_id', $seller_id)
                                         ->where('archieved',0)
                                         ->count();

        $q = new SellerReview();
        $q = $q->setConnection('mysql2');
        $reviews = $q->where('seller_id', $seller_id)->count();

        $q = new CrmLoad();
        $credit = $q->where('seller_id', $seller_id)->first();
        if(count($credit)>0){
            $number_sent = $credit->number_email_sent;
            $credit = $credit->credit;
        }
        else{
            $number_sent = 0;
            $credit = 0;
        }

        $bs = new BaseSubscriptionSeller;
        $get_bs = $bs->where('seller_id', $seller_id)
                        ->where('is_active', 1)
                        ->first();
        
        $monthly_remaining = 0;
        if(isset($get_bs))
        {
            $bst = BaseSubscriptionSellerTransaction::where('bss_id', $get_bs->id)
                                                    ->where('currently_used' , 1)
                                                    ->get();

            if(isset($bst))
            {
                foreach($bst as $bs)
                {
                    $monthly_remaining = $bs->bonus_mail - $bs->email_used;
                }

                if($monthly_remaining < 0)
                {
                    $monthly_remaining = 0;
                }
            }
        }

        $todos = Todo::where('seller_id', $seller_id)->orderBy('created_at', 'DESC')->get();

        /*
        added by jason 07/06/2017
        flagging for payment valid
        0 = invalid payment
        1 = success payment
        -1 = insufficient payment
        */
        //added payment method validation -Altsi
        $diff = 3;
        $now = Carbon::now();
        $payment_valid = null;
        $dayCount = null;
        $preferred_currency = null;
        $amount_payable = null;
        $has_subscription = 0;

        if(isset(Auth::user()->seller->billing))
        {
            $pid = Auth::user()->seller->billing->payment_invalid_date;

            if(!is_null($pid))
            {
              $invalidDate = Carbon::parse($pid);
              $diff = $diff - (($now)->diffInDays($invalidDate));
              if($diff <= 0)
              {
                $bill = Auth::user()->seller->billing;
                $bill->payment_valid = 0;
                $bill->save();
              }
            }

            $pv = Auth::user()->seller->billing->payment_valid;
            $payment_valid = $pv;
            $dayCount = $diff;
            $pay = $this->payMethod();
            $amount_payable = round($pay['fees_paid'],2);
            $preferred_currency = $pay['currency_symbol'];
        }

        if(Auth::user()->seller->is_trial == 1)
        {
            $payment_valid = 1;
        }

        if(Auth::user()->seller->basesubscription->count() > 0)
        {
            $has_subscription = 1;
        }
        
        $data = $this->callBaseSubscriptionName($seller_id);
        //
        return view('trendle.dashboard.home')
                ->with('product_review_count', $products)
                ->with('seller_review_count', $reviews)
                ->with('credit', $credit)
                ->with('number_sent', $number_sent)
                ->with('total_owed', $total_owed-$total_reimburse)
                ->with('total_reimburse', $total_reimburse)
                ->with('currency', $currency)
                ->with('todos', $todos)
                ->with('amount_payable', $amount_payable)
                ->with('payment_valid', $payment_valid)
                ->with('dayCount', $dayCount)
                ->with('preferred_currency',$preferred_currency)
                ->with('has_subscription', $has_subscription)
                ->with('bs',$data->base_subscription)
                ->with('monthly_remaining', $monthly_remaining);
    }

    public function get_on_board(){
        $seller_id = Auth::user()->seller_id;
        $mkpassign_model = new MarketplaceAssign();
        $f=array("*");
        $c=array('seller_id'=>$seller_id);
        $o=array();
        $onboard = array();
        $q=$mkpassign_model->getRecords(config('constant.tables.mkp'),$f,$c,$o);
        if(count($q) > 0) $onboard['mkp'] =  "true";
        else $onboard['mkp'] =  "false";

        $amz = AmazonSellerDetail::where('seller_id', $seller_id)->get();
        if(count($amz) > 0) $onboard['login_amazon'] =  "true";
        else $onboard['login_amazon'] =  "false";

        $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller_id)->get()->first();
        if(count($bss) > 0) $onboard['subscription'] =  "true";
        else $onboard['subscription'] =  "false";

        $bill = Seller::where('id', $seller_id)->get()->first();
        if($bill->is_onboarded_to_billing == 0){
            $onboard['billing'] =  "true";
        }
        else $onboard['billing'] =  "false";

        $data[] = $onboard;
        echo json_encode($data);

    }

    public function update_onboaring_billing(){
        $seller_id = Auth::user()->seller_id;
        $bill = Seller::where('id', $seller_id)->get()->first();
        if($bill->is_onboarded_to_billing == 0){
            $s = Seller::find($seller_id);
            $s->is_onboarded_to_billing = 1;
            $s->save();
        }
    }

    public function createTodo(Request $request)
    {
        $todo = new Todo;
        $todo->color_class = $request->color_class;
        $todo->item = $request->item;
        $todo->is_striked = $request->is_striked;
        $todo->seller_id = Auth::user()->seller_id;
        $todo->save();
    }

    public function updateTodo(Request $request)
    {
        $todo = Todo::find($request->id);
        $todo->item = isset($request->item)? $request->item : $todo->item;
        $todo->save();
    }

    public function deleteTodo(Request $request)
    {
        $todo = Todo::find($request->id);
        $todo->delete();
    }

    public function updateStatus(Request $request)
    {
        $todo = Todo::find($request->id);
        $todo->is_striked = $request->is_striked;
        $todo->save();
    }

    public function payMethod()
    {

        $seller_id = Auth::user()->seller_id;
        
        $preferred_currency = $this->getPreferredCurrency($seller_id);
            // start currency symbol
            if (isset($preferred_currency)) {
                if ($preferred_currency == '' || $preferred_currency == null) {
                    $currency_symbol = '£';
                    $preferred_currency = 'gbp';
                }
                if ($preferred_currency == 'usd') {
                    $currency_symbol = '$';
                } else if ($preferred_currency == 'eur') {
                    $currency_symbol = '€';
                } else if ($preferred_currency == 'gbp') {
                    $currency_symbol = '£';
                }
            } else {
                $currency_symbol = '£';
                $preferred_currency = 'gbp';
            }
            // end symbol

            $req_array = array('currency' => $preferred_currency);
            $fees_paid = 0;

        $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller_id)
                                            ->where('is_active', '=', 1)
                                            ->first();
            if (isset($bss)) {
                $scheduled_date = date("Y-m-d", strtotime($bss->next_billing_date));
                $today = date("Y-m-d");
                if ($scheduled_date <= $today) {                    
                    $data1 = $this->getBaseSubscriptionSellerTransactions($bss->id);
                    $atp_converted = currency($data1->atp, 'USD', strtoupper($preferred_currency), false);
                    $fees_paid += $atp_converted;
                    $req_array['bss_atp'] = $data1->atp;
                    $req_array['bss_atp_converted'] = $atp_converted;
                }
            }

        $fr = FbaRefund::where('seller_id', '=', $seller_id)
                                            ->where('is_activated', '=', 1)
                                            ->first();
            if (isset($fr)) {
                $scheduled_date = date("Y-m-d", strtotime($fr->nxt_date));
                $today = date("Y-m-d");
                if ($scheduled_date <= $today) {
                    $data2 = $this->getFbaRefundTrans($fr->seller_id, $scheduled_date, $preferred_currency);
                    $fees_paid += ($data2->total_reimbursed_us + $data2->total_reimbursed_ca + $data2->total_reimbursed_uk + $data2->total_reimbursed_fr + $data2->total_reimbursed_de + $data2->total_reimbursed_es + $data2->total_reimbursed_it);    
                    $req_array['amount_us'] = $data2->total_reimbursed_us;
                    $req_array['amount_ca'] = $data2->total_reimbursed_ca;
                    $req_array['amount_uk'] = $data2->total_reimbursed_uk;
                    $req_array['amount_fr'] = $data2->total_reimbursed_fr;
                    $req_array['amount_de'] = $data2->total_reimbursed_de;
                    $req_array['amount_es'] = $data2->total_reimbursed_es;
                    $req_array['amount_it'] = $data2->total_reimbursed_it;
                    $fees_paid += currency($data2->atp, 'USD', strtoupper($preferred_currency), false);
                    $req_array['diy_fee'] = currency($data2->atp, 'USD', strtoupper($preferred_currency), false);
                    $has_fba = true;
                    $req_array['fr_id'] = $fr->id;
                }
            }

        $ps = PromoSubscription::where('seller_id',$seller_id)
                           ->where('is_used',0)
                           ->first();

            if(isset($ps))
            {          
                $voucher = $ps->voucher_code;

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
                $voucher_code = $pn->voucher_code;
                $pc = PromoCodeA::where('voucher_code', $voucher_code)
                                ->first();

                $promocode = $voucher_code;
                $promocode_currency = $pc->currency;
                $promocode_amount = $pc->discount_value;
                $promocode_discount_type = $pc->discount_type;


                if($promocode_discount_type == 'percent')
                {
                    $promocode_discount = $fees_paid*($promocode_amount / 100);
                    $fees_paid = $fees_paid - $promocode_discount;
                }
                else if($promocode_discount_type == 'value')
                {
                    $promocode_amount = currency($promocode_amount, strtoupper($promocode_currency),strtoupper($preferred_currency),
                        false);
                    $promocode_discount = round($promocode_amount,2)*100;

                    if ($promocode_discount > $fees_paid) {
                        $promocode_discount = $fees_paid;
                        $fees_paid = 0;
                    } else {
                       $fees_paid = $fees_paid - $promocode_discount;
                    }
                }
            }

        $fees_paid_ui = $fees_paid;
            if ($fees_paid > 0) {

                $bill = Billing::where('seller_id', '=', $seller_id)->first();
                if(isset($bill))
                {
                    $country_id = $bill->country_id;
                    $country_code = $bill->vat_country_code;
                    $vat_num = $bill->vat_number;
                    $data = $this->calculateVat($seller_id, $fees_paid_ui, $vat_num, $country_id, $country_code, $preferred_currency);
                    $fees_paid_ui = $fees_paid_ui + $data['vat'];
                }
            }

        
        $req_array['fees_paid'] = $fees_paid_ui;
        $req_array['currency_symbol'] = $currency_symbol;
        return $req_array;
    }

    private function getPreferredCurrency($seller_id) {
        $preferred_currency = "gbp";

        $billing = Billing::where('seller_id', '=', $seller_id)->first();
        if (isset($billing)) {
            $preferred_currency = $billing->preferred_currency;
        }

        return $preferred_currency;
    }

    private function getBaseSubscriptionSellerTransactions($bss_id) {       
        $data = (object) null;

        $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss_id)
                                                    ->where('up_next', '=', 0)
                                                    ->get();
        $atp = 0;
        foreach ($bsst as $val) {
            $atp += $val->amount_to_pay;
        }
        $data->atp = $atp;
        return $data;
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

    private function getFbaRefundTrans($seller_id, $scheduled_date, $preferred_currency) {       
        $data = (object) null;

        $mkp_assign = array();
        $where = array('seller_id'=>$seller_id);
        $q= new MarketplaceAssign();
        $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());
        $countries = array();

        foreach ($mkp_assign as $value) {
        $mkp = array();
        if($value->marketplace_id == 1) $mkp = config('constant.amz_keys.na.marketplaces');
            if($value->marketplace_id == 2) $mkp = config('constant.amz_keys.eu.marketplaces');
            foreach ($mkp as $key => $mkp_data) {
                array_push($countries,$key);
            }
        }

        $total_reimbursed_us = 0;
        $total_reimbursed_ca = 0;
        $total_reimbursed_uk = 0;
        $total_reimbursed_de = 0;
        $total_reimbursed_it = 0;
        $total_reimbursed_es = 0;
        $total_reimbursed_fr = 0;

        $dt = Carbon::parse($scheduled_date);
        $diff = $dt->subMonth();
        foreach ($countries as $key => $country) {
            $frt = FbaRefundTran::where('seller_id', '=', $seller_id)
                                        ->where('country_code', '=', $country)
                                        ->whereRaw('(DATE(created_at) BETWEEN "'.$diff.'" AND "'.$scheduled_date.'")')
                                        ->get();
            if (isset($frt)) {
                foreach ($frt as $val) {
                    if ($country == 'us') {
                        $total_reimbursed_us += currency($val->amount_reimbursed, strtoupper($val->currency), strtoupper($preferred_currency), false);
                    } else if ($country == 'ca') {
                        $total_reimbursed_ca += currency($val->amount_reimbursed, strtoupper($val->currency), strtoupper($preferred_currency), false);
                    } else if ($country == 'uk') {
                        $total_reimbursed_uk += currency($val->amount_reimbursed, strtoupper($val->currency), strtoupper($preferred_currency), false);
                    } else if ($country == 'de') {
                        $total_reimbursed_de += currency($val->amount_reimbursed, strtoupper($val->currency), strtoupper($preferred_currency), false);
                    } else if ($country == 'it') {
                        $total_reimbursed_it += currency($val->amount_reimbursed, strtoupper($val->currency), strtoupper($preferred_currency), false);
                    } else if ($country == 'es') {
                        $total_reimbursed_es += currency($val->amount_reimbursed, strtoupper($val->currency), strtoupper($preferred_currency), false);
                    } else if ($country == 'fr') {
                        $total_reimbursed_fr += currency($val->amount_reimbursed, strtoupper($val->currency), strtoupper($preferred_currency), false);
                    }
                }
            }
        }
        $data->total_reimbursed_us = $total_reimbursed_us*0.1;
        $data->total_reimbursed_ca = $total_reimbursed_ca*0.1;
        $data->total_reimbursed_uk = $total_reimbursed_uk*0.1;
        $data->total_reimbursed_de = $total_reimbursed_de*0.1;
        $data->total_reimbursed_it = $total_reimbursed_it*0.1;
        $data->total_reimbursed_es = $total_reimbursed_es*0.1;
        $data->total_reimbursed_fr = $total_reimbursed_fr*0.1;

        $frdt = FbaRefundDiyTran::where('seller_id', '=', $seller_id)->get();

        $atp = 0;
        if (isset($frdt)) {
            foreach ($frdt as $val) {
                $atp += $val->amount_to_pay;
            }
        }
        $data->atp = $atp;

        return $data;
    }

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
