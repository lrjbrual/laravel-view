<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Countries as CountriesModel;
use Carbon\Carbon;
use Route;
use Auth;
use App\Country;
use App\Billing;
use App\Seller;
use App\CrmLoad;
use Illuminate\Support\Facades\Input;
use Redirect;
use App\PromoCodeA;
use App\PromoSubscription;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;
use App\BillingInvoiceItem;
use App\FbaRefund;
use App\MarketplaceAssign;
use App\FbaRefundTran;
use App;
use DB;
use Mail;
use DvK\Laravel\Vat\Facades\Rates;
use DvK\Laravel\Vat\Facades\Validator;
use DvK\Laravel\Vat\Facades\Countries;
use App\Mail\Invoicing;
use Illuminate\Support\Facades\Crypt;
use App\BillingInvoice;
use \Config;
use App\FbaRefundDiyTran;
use App\Mail\InsufficientNotification;
use Session;


class BillingController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth',['except' => ['billing_invoice']]);

    }
    public function index()
    {   
        $countries = CountriesModel::getListForSelect();
        $now = Carbon::now();
        $current_year = $now->year;
       if(Auth::user()->seller->billing){
            $billing = Billing::find(Auth::user()->seller->billing->id);
        } else {
            $billing = array();
        }

        $CountryCode = $this->getCountryCode();
        $seller = Seller::find(Auth::user()->seller_id);

        $from = Input::get('from');

        $load = "0";
        $loads = CrmLoad::where('seller_id', '=', $seller->id)->get();
        foreach ($loads as $l) {
            if ($l->id > 0) {
                //for user's current credit
                $load = $l->credit;
            }
        }

        /*
        added by jason 07/06/2017
        flagging for payment valid
        0 = invalid payment
        1 = success payment
        -1 = insufficient payment
        */

        //added payment method validation -Altsi
        $amount_payable = null;
        $payment_valid = null;
        $preferred_currency = null;
        $payment_valid = null;
        $dayCount = null;
        $diff = 3;
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

            if($payment_valid == -1)
            {
            $dayCount = $diff;
            $pay = $this->payMethod();
            $amount_payable = number_format($pay['fees_paid'],2);
            $preferred_currency = $pay['currency_symbol'];
            }
            //
        }

        if(Auth::user()->seller->is_trial == 1)
        {
            $payment_valid = 1;
        }

        if(Auth::user()->seller->basesubscription->count() > 0)
        {
            $has_subscription = 1;
        }
        
        $seller_id = Auth::user()->seller_id;
        $data = $this->callBaseSubscriptionName($seller_id);
        $load = $load + $data->bonus_load;
        return view('trendle.billing.index')
            ->with('countries', $countries)
            ->with('current_year', $current_year)
            ->with('billing', $billing)
            ->with('from', $from)
            ->with('load', $load)
            ->with('seller', $seller)
            ->with('amount_payable', $amount_payable)
            ->with('payment_valid', $payment_valid)
            ->with('dayCount', $dayCount)
            ->with('preferred_currency',$preferred_currency)
            ->with('has_subscription', $has_subscription)
            ->with('bs',$data->base_subscription)
            ->with('CountryCode', $CountryCode);
    }

    public function registerCard(Request $request)
    {
        $billing = $this->setBillingAction();

        if ($this->billingHasStripeId()) {
            $billing->deleteCards(); // remove previously added card if exist
                $b = $billing->updateCard($request->stripeToken);
                $this->updateStripeCustomer();
        } else {
            $data = ['email' => Auth::user()->seller->email, 'description' => Auth::user()->seller->company];
            $billing->createAsStripeCustomer($request->stripeToken, $data);
            $this->updateStripeCustomer();
        }

        $this->saveCard($billing, $request);

        if (isset($request->from) && $request->from='refund'){
            if (isset($billing->payment_method)&&isset($billing->preferred_currency)) {
                return redirect('refund');
            } else {
                return Redirect::to(url('billing').'?from=refund');
            }
        } else {
            $charge = $this->setValidity();
            if($charge['status'] == false)
            {
                flash('We tried to charge '.$charge['currency_symbol'].' '.number_format($charge['fees_paid'],2).' but your card was declined.', 'danger');
            }
            else
            {
                flash('Payment method successfully saved.', 'success');
            }

            if(isset($b))
            {
                if(!is_null($b['error']['type']))
                {
                    flash('Your card was declined', 'danger');
                }
            }

        }
        return redirect('billing');
        
    }

    public function getCountryCode()
    {
        $country = Country::all()->sortBy('iso_3166_2');
        $eu = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GB',
        'GR',
        'HU',
        'HR',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK'
        ];
        $datac = array();
        foreach($country as $c)
        {
            if(in_array($c->iso_3166_2, $eu))
            {
                $datac[$c->id] = $c->iso_3166_2;
            }
        }

        return $datac;
    }

    public function setValidity()
    {   
        $data = array();
        $trial_start_date = Auth::user()->seller->trialperiod->trial_start_date;
        $seller_id = Auth::user()->seller_id;
        $u = Billing::where('seller_id',$seller_id)
                  ->first();
        if(isset($u))
        {
            $pid = $u->payment_invalid_date;
            $now = Carbon::now();
            if(!is_null($pid))
            {
              $invalidDate = Carbon::parse($pid);
              $diff = (($now)->diffInDays($invalidDate));
              if($diff >= 3)
              {
                $pm = $this->payMethod();

                $data['fees_paid'] = $pm['fees_paid'];
                $data['currency_symbol'] = $pm['currency_symbol'];
                $pay = $this->payRefunds($seller_id,$pm);
                if($pay == true)
                {
                    if(isset($pm['fr_id']))
                    {
                        $this->updateFBARefunds($pm['fr_id'], 'nxt_date', $trial_start_date);
                    }
                    if ($pm['has_bss'] == true) {
                            $this->updateBssTrans($pm['bss_id'], $trial_start_date);
                        }
                    if ($pm['has_fba'] == true) {
                        $this->updateDiyTrans($seller_id);                     
                    }
                    
                }
                else
                {
                    if(!is_null($pid))
                    {
                        $this->sendInsufficientNotification($seller_id , $pm['fees_paid'] , $pm['currency_symbol'], 0);
                    }
                    $data['status'] = false;
                    return $data;
                }
              }
            }
            $u->payment_valid = true;
            $u->save();
            $data['status'] = true;
            return $data;
        }
    }

    public function notValid(Request $req)
    {
        $u = Billing::where('seller_id',$req->id)
              ->first();
        if(isset($u))
        {
            $u->payment_valid = false;
            if(Auth::user()->seller->is_trial == 0)
            {
                $u->payment_invalid_date = Carbon::now();
            }
            $u->save();
        }
    }


    private function verifyVat($vat_num, $country_id)
    {
        $bill = new Billing;
        if($country_id != '' || $country_id != null)
        {
            $cc = $bill->country_code($country_id);
            $inEurope = Countries::inEurope($cc);
            if ($inEurope) {
                if ($vat_num == '' || $vat_num == null) {
                    $message = ' Vat number is not provided.';
                    return $message;
                } else {
                    $vat_number = $cc.$vat_num;
                    $v = Validator::validate($vat_number);
                    if ($v == true) {
                      $message = " Vat Number is valid.";
                    } else {
                      $message = " Vat Number is invalid.";
                    } 
                }
            } else {    
                $message = " Country is not from Europe.";
            }
        }
        else
        {
            $message = ' Vat number is not provided.';
            return $message;
        }

        return $message;
    }

    public function store(Request $request)
    {
        $billing = new Billing;

        $billing->seller_id = Auth::user()->seller->id;
        $this->saveContact($billing, $request);
        $m = $this->verifyVat($request->vat_number, $request->vat_country_code);
        $this->updateStripeCustomer();

        if (isset($request->from) && $request->from='refund'){
            if (isset($billing->preferred_currency)) {
                return redirect('refund');
            } else {
                return Redirect::to(url('billing').'?from=refund');
            }
        } else {
            if($m == " Vat Number is invalid.")
            {
                flash('Billing information successfully saved.', 'success');
                Session::flash('error', 'Vat Number is invalid.');
            }
            else
            {
                flash('Billing information successfully saved.'.$m, 'success');
            }
            return redirect('billing');
        }
    }

    public function update(Request $request, $id)
    {
        $billing = Billing::find($id);
        $this->saveContact($billing, $request);
        $m = $this->verifyVat($request->vat_number, $billing->vat_country_code);
        $this->updateStripeCustomer();

        if (isset($request->from) && $request->from='refund'){
            if (isset($billing->preferred_currency)) {
                return redirect('refund');
            } else {
                return Redirect::to(url('billing').'?from=refund');
            }
        } else {
            if($m == " Vat Number is invalid.")
            {
                flash('Billing information successfully updated.', 'success');
                Session::flash('error', 'Vat Number is invalid.');
            }
            else
            {
                flash('Billing information successfully updated.'.$m, 'success');
            }
            return redirect('billing');
        }
    }

    public function getBillingField($field)
    {
        $billing = Billing::find(Auth::user()->seller->billing->id);

        return $billing->$field;
    }

    public function updateStripeCustomer()
    {
        if ($this->billingHasStripeId()) {
            $billing = Billing::find(Auth::user()->seller->billing->id);

            $data = [
                'Firstname'   => $billing->firstname,
                'Lastname'    => $billing->lastname,
                'Address 1'   => $billing->address1,
                'Address 2'   => $billing->address2,
                'City'        => $billing->city,
                'Postal Code' => $billing->postal_code,
                'State'       => $billing->state,
                'Country'     => $this->getCountryName($billing->country_id),
                'VAT Number'  => $billing->vat_number
            ];

            $customer = $billing->asStripeCustomer();

            $customer->description = $billing->company;
            $customer->metadata = $data;

            $customer->save();
        }
    }

    public function getCountryName($id)
    {
        $country = CountriesModel::getOne($id);

        return $country['name'];
    }

    private function saveContact($billing, $request)
    {   
        $billing->firstname = $request->firstname;
        $billing->lastname = $request->lastname;
        $billing->company = $request->company;
        $billing->address1 = $request->address1;
        $billing->address2 = $request->address2;
        $billing->city = $request->city;
        $billing->postal_code = $request->postal_code;
        $billing->state = $request->state;
        $billing->country_id = $request->country_id;
        $billing->vat_number = $request->vat_number;
        $billing->vat_country_code = $request->vat_country_code;
        $billing->save();
    }

    private function saveCard($billing, $request)
    {
        $billing->card_holder_name = $request->card_holder_name;
        $billing->card_expiry_month = $request->expiry_month;
        $billing->card_expiry_year = $request->expiry_year;
        $billing->save();
    }

    private function setBillingAction()
    {
        if (Auth::user()->seller->billing) {
            // update record
            $billing = Billing::find(Auth::user()->seller->billing->id);
        } else {
            // add new record
            $billing = new Billing;
            $billing->seller_id = Auth::user()->seller->id;
        }

        return $billing;
    }

    private function billingHasStripeId()
    {
      return Auth::user()->seller->billing ? Auth::user()->seller->billing->hasStripeId() : null;
    }

    public function storePreferredPayment(Request $request)
    {
        $billing = $this->setBillingAction();

        //$billing->payment_method = $request->payment_method;
        $billing->payment_method = 'card';
        $billing->preferred_currency = $request->preferred_currency;
        $billing->save();

        if (isset($request->from) && $request->from='refund'){
            if (isset($billing->stripe_id)) {
                return redirect('refund');
            } else {
                return Redirect::to(url('billing').'?from=refund');
            }
        } else {
            return redirect('billing');
        }
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
        $has_bss = false;
        $has_fba = false;
            //get first item
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
                    $req_array['bss_id'] = $bss->id;
                    $has_bss = true;
                }
            }

        $fr = FbaRefund::where('seller_id', '=', $seller_id)
                                            ->where('is_activated', '=', 1)
                                            ->first();
            if (isset($fr)) {
                $scheduled_date = date("Y-m-d", strtotime($fr->nxt_date));
                $today = date("Y-m-d");
                if ($scheduled_date <= $today) {
                    $data2 = $this->getFbaRefunTrans($fr->seller_id, $scheduled_date, $preferred_currency);
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


        $req_array['has_bss'] = $has_bss;
        $req_array['has_fba'] = $has_fba;
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
        if (isset($bsst)) {
            foreach ($bsst as $val) {
                $atp += $val->amount_to_pay;
            }
        }
        $data->atp = $atp;
        return $data;
    }

    private function updateBssTrans($bss_id, $trial_start_date) {
        $bsst_un = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss_id)
                                                    ->where('up_next', '=', 1)
                                                    ->first();
        if (isset($bsst_un)) { // if there is a pending subscription to be used
            $base_subscription = $bsst_un->bs_name;
            $bonus_mail = 0;
            $atp = 0;
            switch ($base_subscription) {
                case 'XS':  
                    $bonus_mail = 100;
                    $atp = 20;
                    break;
                
                case 'S':  
                    $bonus_mail = 300;
                    $atp = 50;
                    break;
                
                case 'M':  
                    $bonus_mail = 2000;
                    $atp = 100;
                    break;
                
                case 'L':  
                    $bonus_mail = 5000;
                    $atp = 200;
                    break;
                
                case 'XL':  
                    $bonus_mail = 10000;
                    $atp = 400;
                    break;
                
                default:  
                    $bonus_mail = 0;
                    $atp = 0;
                    break;
            }

            $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss_id)->get();
            foreach ($bsst as $val) {
                $bsst_del = BaseSubscriptionSellerTransaction::find($val->id);
                $bsst_del->delete();
            }

            $bsst_new = new BaseSubscriptionSellerTransaction;
            $bsst_new->bss_id = $bss_id;
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
        } else { // if there is no pending subscription to be used
            $bsst_cu = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss_id)
                                                    ->where('currently_used', '=', 1)
                                                    ->first();
            $base_subscription = $bsst_cu->bs_name;
            $bonus_mail = 0;
            $atp = 0;
            switch ($base_subscription) {
                case 'XS':  
                    $bonus_mail = 100;
                    $atp = 20;
                    break;
                
                case 'S':  
                    $bonus_mail = 300;
                    $atp = 50;
                    break;
                
                case 'M':  
                    $bonus_mail = 2000;
                    $atp = 100;
                    break;
                
                case 'L':  
                    $bonus_mail = 5000;
                    $atp = 200;
                    break;
                
                case 'XL':  
                    $bonus_mail = 10000;
                    $atp = 400;
                    break;
                
                default:  
                    $bonus_mail = 0;
                    $atp = 0;
                    break;
            }

            $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss_id)->get();
            foreach ($bsst as $val) {
                $bsst_del = BaseSubscriptionSellerTransaction::find($val->id);
                $bsst_del->delete();
            }

            $bsst_new = new BaseSubscriptionSellerTransaction;
            $bsst_new->bss_id = $bss_id;
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
        }

        // update billing date
        $y = date_format(date_create(date('Y-m-d')), 'Y');
        $m = date_format(date_create(date('Y-m-d')), 'm');
        $d = date_format(date_create($trial_start_date), 'd');
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
        $bss = BaseSubscriptionSeller::find($bss_id);
        $bss->next_billing_date = $nbd;
        $bss->save();
    }

    private function getFbaRefunTrans($seller_id, $scheduled_date, $preferred_currency) {       
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

    private function payRefunds($seller_id, $req)
    {
        $bill = Billing::where('seller_id', '=', $seller_id)->first();
        $currency = 'gbp';
        if (isset($bill)) {
            $bill_id = $bill->id;
            $currency = $bill->preferred_currency;
        } else {
            return false;
        }
        $amount_us = (isset($req['amount_us'])) ? $req['amount_us'] : 0;
        $amount_ca = (isset($req['amount_ca'])) ? $req['amount_ca'] : 0;
        $amount_uk = (isset($req['amount_uk'])) ? $req['amount_uk'] : 0;
        $amount_fr = (isset($req['amount_fr'])) ? $req['amount_fr'] : 0;
        $amount_de = (isset($req['amount_de'])) ? $req['amount_de'] : 0;
        $amount_es = (isset($req['amount_es'])) ? $req['amount_es'] : 0;
        $amount_it = (isset($req['amount_it'])) ? $req['amount_it'] : 0;
        $diy_fee = (isset($req['diy_fee'])) ? $req['diy_fee'] : 0;
        $bss_atp_converted = (isset($req['bss_atp_converted'])) ? $req['bss_atp_converted'] : 0;
        $temp_amount1 = $amount_us + $amount_ca + $amount_uk + $amount_fr + $amount_de + $amount_es + $amount_it + $diy_fee;
        $temp_amount = $amount_us + $amount_ca + $amount_uk + $amount_fr + $amount_de + $amount_es + $amount_it + $diy_fee + $bss_atp_converted;
        $amount = ($temp_amount)*100;

        // PAKI REMOVE FOR TESTING ONLY
        //\Stripe\Stripe::setVerifySslCerts(false); // PAKI REMOVE FOR TESTING ONLY
        // PAKI REMOVE FOR TESTING ONLY

        //charge customer
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $billing = Billing::find($bill_id);

        if(isset($billing))
        {
            $country_id = $billing->country_id;
            $country_code = $billing->vat_country_code;
            $vat_num = $billing->vat_number;
            $customer = \Stripe\Customer::retrieve($billing->stripe_id);
        }

        $promocode = NULL;
        $promocode_discount = 0;

        $ps = PromoSubscription::where('seller_id',$seller_id)
                               ->where('is_used',0)
                               ->first();

        if(isset($ps))
        {
            $voucher_code = $ps->voucher_code;
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
                $promocode_amount = currency($promocode_amount, strtoupper($promocode_currency),strtoupper($currency),
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

        $data = $this->calculateVat($seller_id, $amount/100, $vat_num, $country_id, $country_code, $req['currency']);
        $amount = $amount + ($data['vat']*100);

        $b = Billing::where('seller_id', $seller_id)
                            ->first();
        if(isset($b))
        {
            if ($amount != 0) {
                try {
                  $charge = \Stripe\Charge::create(array(
                      "amount" => (int)($amount),
                      "currency" => $currency,
                      "customer" => $customer->id,
                      "description" => 'FBA Refunds'
                  ));
                } catch(\Stripe\Error\Card $e) {
                        $b->payment_valid = -1;

                        if(is_null($b->payment_invalid_date))
                        {
                            $b->payment_invalid_date = Carbon::now();
                        }
                        $b->save();
                        return false;
                    }
                }
            $b->payment_valid = 1;
            $b->payment_invalid_date = null;
            $b->save();
        }
                  DB::transaction(function() use ($seller_id,$req,$data,$amount_us,$amount_ca,$amount_uk,$amount_fr,$amount_de,$amount_es,$amount_it,$promocode,$promocode_discount,$temp_amount1,$bss_atp_converted) {
                    $bi = new BillingInvoice;
                    $bi->seller_id = $seller_id;
                    $bi->invoice_number = $data['latestID'] + 1;
                    $bi->product_description = 'Monthly Billing';
                    $bi->product_subscription = 'Monthly Billing';
                    $bi->amount = $data['amount'];
                    $bi->vat = $data['vat'];
                    $bi->country_code = $data['cc'];
                    $bi->currency = $req['currency'];
                    $bi->promocode = $promocode;
                    $bi->promocode_discount = $promocode_discount/100;
                    $bi->status = $data['status'];
                    $bi->created_at = Carbon::now();
                    $bi->updated_at = Carbon::now();
                    if ($bi->save()) {
                        if ($temp_amount1 > 0) {
                            $bii1 = new BillingInvoiceItem;
                            $bii1->bi_id = $bi->id;
                            $bii1->product_description = 'FBA Refunds';
                            $bii1->item_amount = $temp_amount1;
                            $bii1->currency = $req['currency'];
                            $bii1->created_at = Carbon::now();
                            $bii1->updated_at = Carbon::now();
                            $bii1->save();
                        }
                        if ($bss_atp_converted > 0) {
                            $bii2 = new BillingInvoiceItem;
                            $bii2->bi_id = $bi->id;
                            $bii2->product_description = 'Base Subscription';
                            $bii2->item_amount = $bss_atp_converted;
                            $bii2->currency = $req['currency'];
                            $bii2->created_at = Carbon::now();
                            $bii2->updated_at = Carbon::now();
                            $bii2->save();
                        }
                    }

                    if ($amount_us) {                  
                      $fba_tran = new FbaRefundTran;
                      $fba_tran->seller_id = $seller_id;
                      $fba_tran->total_amount_owed = $amount_us;
                      $fba_tran->amount_reimbursed = 0;
                      $fba_tran->fees_paid = $amount_us*0.1;
                      $fba_tran->fees_deducted = 0;
                      $fba_tran->currency = $req['currency'];
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
                      $fba_tran->currency = $req['currency'];
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
                      $fba_tran->currency = $req['currency'];
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
                      $fba_tran->currency = $req['currency'];
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
                      $fba_tran->currency = $req['currency'];
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
                      $fba_tran->currency = $req['currency'];
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
                      $fba_tran->currency = $req['currency'];
                      $fba_tran->country_code = 'it';
                      $fba_tran->created_at = Carbon::now();
                      $fba_tran->updated_at = Carbon::now();
                      $fba_tran->save();
                    }
                  });
                  
                  $s = Seller::find($seller_id);
                  $fname = $s->firstname;
                  $lname = $s->lastname;
                  $email = $s->email;
                  $token = $data['latestID'] + 1;
                  if($amount != 0)
                  {
                  $this->sendInvoice($fname, $lname, $email, $token);
                  }

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

          return true;
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

    private function updateFBARefunds($id, $reason, $trial_start_date)
    {   
        $y = date_format(date_create(date('Y-m-d')), 'Y');
        $m = date_format(date_create(date('Y-m-d')), 'm');
        $d = date_format(date_create($trial_start_date), 'd');
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
        if ($reason = "nxt_date") {
            $fba_refund = FbaRefund::find($id);
            $fba_refund->payment_status = 'good';
            $fba_refund->nxt_date = Carbon::now()->addDays(30);
            $fba_refund->save();
            return $fba_refund;
        } elseif ($reason = "low_fees") {
            $fba_refund = FbaRefund::find($id);
            $fba_refund->payment_status = 'low collection';
            $fba_refund->save();
            return false;
        } elseif ($reason = "with_bill") {
            $fba_refund = FbaRefund::find($id);
            $fba_refund->payment_status = 'with balance';
            $fba_refund->nxt_date = $nbd;
            $fba_refund->save();
            return false;
        }
        return 1;
    }

    private function updateDiyTrans($seller_id) {
        $frdt = FbaRefundDiyTran::where('seller_id', '=', $seller_id)->get();
        
        if (isset($frdt)) {
            foreach ($frdt as $val) {
                $frdt_del = FbaRefundDiyTran::find($val->id);
                $frdt_del->delete();
            }
        }

        $fba = FbaRefund::where('seller_id', '=', $seller_id)->first();
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

    private function sendInsufficientNotification($seller_id, $fees_paid , $currency_symbol , $daysCount)
    {
        $s = Seller::find($seller_id);
        $fname = $s->firstname;
        $lname = $s->lastname;
        $email = $s->email;
        //backup default config
        $backup = Config::get('mail');
        //set new config for sparkpost
        if(env('SPARKPOST_MAIL_DRIVER') != ""){
            Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));
        }

        Mail::to($email)->send(new InsufficientNotification($fname, $lname, $fees_paid, $currency_symbol , $daysCount));

        //restore default config
        Config::set('mail', $backup);
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
      $data->bonus_load = 0;
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
            $data->bonus_load = $bsst->bonus_mail - $bsst->mail_used;
        }
      }

      return $data;
    }

    static function routes()
    {
       Route::resource('billing', 'Trendle\BillingController');

       Route::group(array('prefix' => 'billing'), function() {
          Route::post('registerCard', 'Trendle\BillingController@registerCard');
          Route::post('storePreferredPayment', 'Trendle\BillingController@storePreferredPayment');
       });
    }
}
