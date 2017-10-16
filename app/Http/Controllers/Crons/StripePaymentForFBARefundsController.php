<?php

namespace App\Http\Controllers\Crons;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Billing;
use App\FbaRefund;
use App\FbaRefundTran;
use App\InventoryAdjustmentReport;
use App\FinancialEventsReport;
use Mail;
use App\Mail\CronNotification;
use Carbon\Carbon;
use App\MarketplaceAssign;
use App\FbaPreCalculation;
use DvK\Laravel\Vat\Facades\Rates;
use DvK\Laravel\Vat\Facades\Validator;
use DvK\Laravel\Vat\Facades\Countries;
use App\BillingInvoice;
use App\Seller;
use \Config;
use App\Mail\Invoicing;
use App\Mail\InsufficientNotification;
use Illuminate\Support\Facades\Crypt;
use App\PromoCode;
use App\PromoCodeA;
use App\PromoSubscription;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;
use App\BillingInvoiceItem;
use App\FbaRefundDiyTran;
use Illuminate\Support\Facades\DB;

class StripePaymentForFBARefundsController extends Controller
{
	public function index()
	{
		try {
		ini_set('memory_limit', '1024M');
        ini_set("zlib.output_compression", 0);  // off
        ini_set("implicit_flush", 1);  // on
        ini_set("max_execution_time", 0);  // on

		$start_run_time = time();
		$isError = false;
    	$total_records = 0;

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Stripe Payment for FBA Refunds', true));

		$sellers = Seller::all();

		foreach ($sellers as $seller) {
			$trial_start_date = $seller->trialperiod->trial_start_date;
			$preferred_currency = $this->getPreferredCurrency($seller->id);
		    if ($preferred_currency == '' || is_null($preferred_currency)) {
		      $preferred_currency = 'usd';
		    }
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
		    $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller->id)
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
		    		$has_bss = true;
		    	}
		    }

		    //get second item
		    $fr = FbaRefund::where('seller_id', '=', $seller->id)
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
		    	}
		    }

			//start show amount - discount + vat

	        $seller_id = $seller->id;
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

				$bill = Billing::where('seller_id', '=', $seller->id)->first();
				if(isset($bill))
				{
		    		$country_code = $bill->vat_country_code;
		    		$country_id = $bill->country_id;
		    		$vat_num = $bill->vat_number;
					$data = $this->calculateVat($seller->id, $fees_paid_ui, $vat_num, $country_id, $country_code, $preferred_currency);
					$fees_paid_ui = $fees_paid_ui + $data['vat'];
					// end show amount - discount + vat

					$pay = $this->payRefund($seller->id, $req_array);
				    if ($pay == true) {
				    	if(isset($fr))
					    {
					    	$this->updateFBARefund($fr->id, 'nxt_date', $trial_start_date);
					    }
				    	echo "<p style='color:red;'><b>Seller #".$seller->id." successfully paid ".$currency_symbol." ".round($fees_paid_ui,2)."</b></p>";
				    	if ($has_bss == true) {
				    		$this->updateBssTrans($bss->id, $trial_start_date);
				    	}
				    	if ($has_fba == true) {
				    		$this->updateDiyTrans($seller->id);			    		
				    	}
				    	$total_records ++;
				    } else {
				    	$billing = Billing::where('seller_id', '=', $seller->id)->first();
				    	$pid = $billing->payment_invalid_date;
				    	if(is_null($pid))
				    	{
				    		$billing->payment_invalid_date = Carbon::now();
				    		$pid = Carbon::now();
				    	}
				    	$billing->payment_valid = -1;
				    	$billing->save();
				    	$now = Carbon::now();
				    	$diff = 3;
				        if(!is_null($pid))
				        {
					        $invalidDate = Carbon::parse($pid);
					        $diff = $diff - (($now)->diffInDays($invalidDate));
					        if($diff >= 0 && $diff <= 3)
	                   		{
								$this->sendInsufficientNotification($seller_id , $fees_paid_ui , $currency_symbol, $diff);
							}
				        }
				    	echo "<p style='color:red;'><b>Error in seller #".$seller->id."</b></p>";
				    }
				}
			}
        }

		$end_run_time = time();
    	$message['message'] = "Stripe Payment for FBA Refunds Cron Script Run Successfully!";
		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = 1;
		$message['total_records'] = $total_records;
		$message['isError'] = $isError;
		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Stripe Payment for FBA Refunds', false, $message));
		} catch (\Exception $e) {
			$end_run_time = time();
			$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
			$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
			$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
			$message['tries'] = 1;
			$message['total_records'] = (isset($total_records) ? $total_records : 0);
			$message['isError'] = $isError;
			$message['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Stripe Payment for FBA Refunds(error)', false, $message));
		}
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
	                $bonus_mail = 1000;
	                $atp = 20;
	                break;
	            
	            case 'S':  
	                $bonus_mail = 3000;
	                $atp = 50;
	                break;
	            
	            case 'M':  
	                $bonus_mail = 10000;
	                $atp = 100;
	                break;
	            
	            case 'L':  
	                $bonus_mail = 40000;
	                $atp = 200;
	                break;
	            
	            case 'XL':  
	                $bonus_mail = 100000;
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
	                $bonus_mail = 1000;
	                $atp = 20;
	                break;
	            
	            case 'S':  
	                $bonus_mail = 3000;
	                $atp = 50;
	                break;
	            
	            case 'M':  
	                $bonus_mail = 10000;
	                $atp = 100;
	                break;
	            
	            case 'L':  
	                $bonus_mail = 40000;
	                $atp = 200;
	                break;
	            
	            case 'XL':  
	                $bonus_mail = 100000;
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

    private function getPreferredCurrency($seller_id) {
    	$preferred_currency = "gbp";

		$billing = Billing::where('seller_id', '=', $seller_id)->first();
		if (isset($billing)) {
			$preferred_currency = $billing->preferred_currency;
		}

		return $preferred_currency;
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

	private function payRefund($seller_id, $req)
    {
	    $bill = Billing::where('seller_id', '=', $seller_id)->first();
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
		\Stripe\Stripe::setVerifySslCerts(false); // PAKI REMOVE FOR TESTING ONLY
		// PAKI REMOVE FOR TESTING ONLY

		//charge customer
		\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
		$billing = Billing::find($bill_id);
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
				      "currency" => $req['currency'],
				      "customer" => $customer->id,
				      "description" => 'FBA Refunds'
				  ));
				} catch(\Stripe\Error\Card $e) {
					if($b->payment_valid == 1)
					{
						$b->payment_valid = -1;
						if(!is_null($b->payment_invalid_date))
						{
							$b->payment_invalid_date = Carbon::now();
						}
						$b->save();

					}
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
		          $this->sendInvoice($fname, $lname, $email, $token);


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

    private function updateFBARefund($id, $reason, $trial_start_date)
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
    		$fba_refund->nxt_date = $nbd;
    		$fba_refund->save();
    	}
    }
}
