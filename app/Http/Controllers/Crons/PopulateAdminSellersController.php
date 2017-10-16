<?php

namespace App\Http\Controllers\Crons;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
use App\Seller;
use App\AdminSeller;
use App\OrderIdClaim;
use App\FnskuClaim;

class PopulateAdminSellersController extends Controller
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

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Populate Admin Sellers', true));

		$sellers = Seller::all();
		
		foreach ($sellers as $seller) {
			
			
			//checker for invalid payment -Altsi
	        $now = Carbon::now();
	        
	        $b = Billing::where('seller_id', $seller->id)
	        			  ->first(['payment_invalid_date']);

	        if(isset($b))
	        {
		        $pd = $b->payment_invalid_date;
		        if(!is_null($pd))
			        {
			        $invalidDate = Carbon::parse($pd);

			        $diff = ($now)->diffInDays($invalidDate);

			        if($diff >= 30)
			        {
			          echo "<p style='color:red;'><b>SELLER has invalid payment method for 30 days or more!</b></p>";
			          continue;
			        }
			    }
			}
	        //
			$preferred_currency = $this->getPreferredCurrency($seller->id);
		    if ($preferred_currency == '' || is_null($preferred_currency)) {
		      $preferred_currency = 'usd';
		    }
      		$mkp_assign = array();
      		$where = array('seller_id'=>$seller->id);
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

			$fba_refund = FbaRefund::where('seller_id', '=', $seller->id)->first();
			if (isset($fba_refund)) {
				if ($fba_refund->is_activated == 1) {
					$fba_mode = $fba_refund->fba_mode;
				} else {
					$fba_mode = 'Deactivated';
				}
			} else {
				$fba_mode = 'Not Activated';
			}

			echo "<p style='color:red;'><b>Inserting data for seller #".$seller->id."</b></p>";
			foreach ($countries as $key => $country) {

				echo "<p style='color:red;'><b>With country code of ".$country."</b></p>";

				$total_owed = $this->getTotalOwed($seller->id, $country, $preferred_currency);
				$total_saved = $this->getTotalSaved($seller->id, $country, $preferred_currency);
				$total_collected = $this->getTotalCollected($seller->id, $country, $preferred_currency);
				$total_deduct = $this->getTotalDeduct($seller->id, $country, $preferred_currency);
				$total_saved_orig = $total_owed; 
				$total_owed = $total_owed - $total_saved;
				$total_owed_to_collect = ($total_owed*(0.1))-($total_collected-$total_deduct);

				$array = array(
					'company_name' => $seller->company,
					'seller_email' => $seller->email,
					'country_code' => $country,
					'total_owed' => round($total_saved_orig,2),
					'total_saved' => round($total_saved,2),
					'total_collected' => round($total_collected,2),
					'total_owed_to_collect' => round($total_owed_to_collect,2),
					'currency' => $preferred_currency,
					'fba_mode' => $fba_mode
				);

				$s = Seller::find($seller->id);
				$s_email = $s->email;
				$as = AdminSeller::where('seller_email', '=', $s_email)
									    ->where('country_code', '=', $country)
									    ->first();
				if ($total_saved_orig > 0) {
					if (isset($as)) {
						$this->updateAdminSeller($as->id, $array);
					} else {
						$this->saveAdminSeller($array);
					}
					echo "<p style='color:red;'><b>Successfully inserted data for the country code of ".$country."</b></p>";
				}
			}
		}

		$end_run_time = time();
    	$message['message'] = "Populate Admin Sellers Cron Script Run Successfully!";
		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = 1;
		$message['total_records'] = $total_records;
		$message['isError'] = $isError;
		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Populate Admin Sellers', false, $message));
		} catch (\Exception $e) {
			$end_run_time = time();
			$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
			$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
			$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
			$message['tries'] = 1;
			$message['total_records'] = (isset($total_records) ? $total_records : 0);
			$message['isError'] = $isError;
			$message['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Populate Admin Sellers(error)', false, $message));
		}
	}

	private function saveAdminSeller($data) {
		$as = new AdminSeller;
    	$as->company_name = $data['company_name'];
    	$as->seller_email = $data['seller_email'];
    	$as->country_code = $data['country_code'];
    	$as->total_owed = $data['total_owed'];
    	$as->total_saved = $data['total_saved'];
    	$as->total_collected = $data['total_collected'];
    	$as->total_owed_to_collect = $data['total_owed_to_collect'];
    	$as->currency = $data['currency'];
    	$as->created_at = Carbon::now();
    	$as->updated_at = Carbon::now();
    	$as->fba_mode = $data['fba_mode'];;
    	$as->save();
    }

    private function updateAdminSeller($id, $data) {
		$as = AdminSeller::find($id);
    	$as->company_name = $data['company_name'];
    	$as->seller_email = $data['seller_email'];
    	$as->country_code = $data['country_code'];
    	$as->total_owed = $data['total_owed'];
    	$as->total_saved = $data['total_saved'];
    	$as->total_collected = $data['total_collected'];
    	$as->total_owed_to_collect = $data['total_owed_to_collect'];
    	$as->currency = $data['currency'];
    	$as->updated_at = Carbon::now();
    	$as->fba_mode = $data['fba_mode'];;
    	$as->save();
    }

	private function getSellerDetails($seller_id) {
		$seller = Seller::find($seller_id);
		$company = $seller->company;
		$email = $seller->email;
    	$data = array(
			'company' => $company,
			'email' => $email
		);
		return $data;
    }

    private function getTotalOwed($seller_id, $country, $preferred_currency) {
		$total_owed = 0;
		$total_owed_gbp = 0;
		$total_owed_usd = 0;
		$total_owed_eur = 0;

		$fpc = FbaPreCalculation::where('seller_id', '=', $seller_id)
	                            ->where('country_code', '=', $country)
	                            ->whereIn('status', ['active','bug fix', 'closed claim'])
	                            ->get();
	    if (count($fpc) > 0) {
	    	foreach ($fpc as $val) {
	        	if ($val->currency == 'gbp') {
	        		$total_owed_gbp += $val->total_owed;
	        	} elseif ($val->currency == 'usd') {
	        		$total_owed_usd += $val->total_owed;
	        	} elseif ($val->currency == 'eur') {
	        		$total_owed_eur += $val->total_owed;
	        	}
	    	}
	    }
	    $total_owed_gbp = currency($total_owed_gbp, 'GBP', strtoupper($preferred_currency), false);
	    $total_owed_usd = currency($total_owed_usd, 'USD', strtoupper($preferred_currency), false);
	    $total_owed_eur = currency($total_owed_eur, 'EUR', strtoupper($preferred_currency), false);

	    $total_owed = $total_owed_gbp + $total_owed_usd + $total_owed_eur;

	    return $total_owed;
    }

    private function getTotalSaved($seller_id, $country, $preferred_currency) {
		$total_saved = 0;
		
		$oic = OrderIdClaim::where('seller_id', '=', $seller_id)
		                           ->where('country_code', '=', $country)
		                       	   ->whereIn('status', ['All Ok', 'Refund issued by seller', 'Amz won\'t refund difference'])
		                       	   ->whereDate('updated_at', date('Y-m-d'))
	 	                           ->get();
	 	foreach($oic as $oc) {
	 		$total_saved += $oc->total_amount_reimbursed;
	 	}

	 	$fnsku = FnskuClaim::where('seller_id', '=', $seller_id)
		                           ->where('country_code', '=', $country)
		                       	   ->whereIn('status', ['All Ok', 'Refund issued by seller', 'Amz won\'t refund difference'])
		                       	   ->whereDate('updated_at', date('Y-m-d'))
	 	                           ->get();

	 	foreach($fnsku as $fc) {
	 		$total_saved += $fc->total_amount_reimbursed;
	 	}

	 	$curr = '';
	 	if($country == 'us')
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

	 	$total_saved = currency($total_saved, $curr, strtoupper($preferred_currency), false);
	 	if ($total_saved > 0) {
	 		$this->saveDailyReimbursements($seller_id, $total_saved, $country, $preferred_currency);
	 	}

	 	$total_saved_all = 0;
	 	$frt = FbaRefundTran::where('seller_id', '=', $seller_id)
	 							->where('country_code', '=', $country)
	 							->get();
	 	if (isset($frt)) {
		 	foreach ($frt as $v) {
		 		$total_saved_all += currency($v->amount_reimbursed, strtoupper($v->currency), strtoupper($preferred_currency), false);
		 	}
		}
		
		$claim_amount = 0;
		$oic1 = OrderIdClaim::where('seller_id', '=', $seller_id)
		                           ->where('country_code', '=', $country)
		                       	   ->whereIn('status', ['All Ok', 'Refund issued by seller', 'Amz won\'t refund difference'])
	 	                           ->get();
	 	foreach($oic1 as $oc) {
	 		$claim_amount += $oc->claim_amount;
	 	}

	 	$fnsku1 = FnskuClaim::where('seller_id', '=', $seller_id)
		                           ->where('country_code', '=', $country)
		                       	   ->whereIn('status', ['All Ok', 'Refund issued by seller', 'Amz won\'t refund difference'])
	 	                           ->get();

	 	foreach($fnsku1 as $fc) {
	 		$claim_amount += $fc->total_owed;
	 	}

	 	$claim_amount = currency($claim_amount, $curr, strtoupper($preferred_currency), false);
	 	if ($claim_amount > 0) {
	 		$this->saveClosedClaimsAmount($seller_id, $claim_amount, $country, $preferred_currency);
	 	}

	    return $total_saved_all;
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

    private function getPreferredCurrency($seller_id) {
    	$preferred_currency = "gbp";

		$billing = Billing::where('seller_id', '=', $seller_id)->first();
		if (isset($billing)) {
			$preferred_currency = $billing->preferred_currency;
		}

		return $preferred_currency;
    }

    private function saveDailyReimbursements($seller_id, $total_saved, $country, $currency) {
    	$frp = new FbaRefundTran;
    	$frp->seller_id = $seller_id;
    	$frp->amount_reimbursed = $total_saved;
    	$frp->currency = $currency;
    	$frp->country_code = $country;
    	$frp->created_at = Carbon::now();
    	$frp->updated_at = Carbon::now();
    	$frp->save();
    }

    private function saveClosedClaimsAmount($seller_id, $claim_amount, $country, $currency) {
    	$claim_amount = $claim_amount*(-1);
		$fbc = FbaPreCalculation::where('seller_id', '=', $seller_id)
        		->where('country_code', '=', $country)
        		->where('status', '=', 'closed claim')
        		->first();
        if (isset($fbc)) {
	    	$fbc->total_owed = $claim_amount;
	    	$fbc->currency = $currency;
	    	$fbc->updated_at = Carbon::now();
	    	$fbc->save();
        } else {
        	$fbc = new FbaPreCalculation;
	    	$fbc->seller_id = $seller_id;
	    	$fbc->total_owed = $claim_amount;
	    	$fbc->country_code = $country;
	    	$fbc->currency = $currency;
	    	$fbc->status = 'closed claim';
	    	$fbc->created_at = Carbon::now();
	    	$fbc->updated_at = Carbon::now();
	    	$fbc->save();
        }
    }
}
