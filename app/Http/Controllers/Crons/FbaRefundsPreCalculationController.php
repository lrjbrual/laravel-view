<?php

namespace App\Http\Controllers\Crons;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

use App\Billing;
use App\FbaRefund;
use App\InventoryAdjustmentReport;
use App\FinancialEventsReport;
use Mail;
use App\Mail\CronNotification;
use Carbon\Carbon;
use App\MarketplaceAssign;
use App\FbaPreCalculation;
use App\Seller;
use App\Reimbursement;
use App\OrderIdClaim;
use App\FnskuClaim;
use App\TrendleChecker;

class FbaRefundsPreCalculationController extends Controller
{
	public function index()
	{
	  try {
		ini_set('memory_limit', '1024M');
    ini_set("zlib.output_compression", 0);  // off
    ini_set("implicit_flush", 1);  // on
    ini_set("max_execution_time", 0);  // on
		ob_start();
		header('X-Accel-Buffering: no');
		$start_run_time = time();
		$isError = false;
    $total_records = 0;



    if( Input::get('seller_id') == null OR Input::get('seller_id') == "" ) {
      echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
        exit;
    } else {
      $seller_id = trim(Input::get('seller_id'));
    }

    if( Input::get('mkp') == null OR Input::get('mkp') == "" ) {
      echo "<p style='color:red;'><b>MKP is required as part of the parameter in the url to run this cron script</b></p>";
        exit;
    } else {
      $getmkp = trim(Input::get('mkp'));
    }

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('FBA Refunds PreCalculation for seller'.$seller_id.' mkp'.$getmkp, true));

    //checker for invalid payment -Altsi
    $seller = Seller::find($seller_id);

    $now = Carbon::now();
		if(isset($seller->billing->payment_invalid_date)){
			$pid = $seller->billing->payment_invalid_date;
				if(!is_null($pid))
				{
					$invalidDate = Carbon::parse($pid);

					$diff = ($now)->diffInDays($invalidDate);

					if($diff >= 30)
					{
						echo "<p style='color:red;'><b>SELLER has invalid payment method for 30 days or more!</b></p>";
						exit;
					}
			}
		}

		$preferred_currency = $this->getPreferredCurrency($seller_id);
    if ($preferred_currency == '' || is_null($preferred_currency)) {
      $preferred_currency = 'usd';
    }
    $mkp_assign = array();
    $where = array('seller_id'=>$seller_id,'marketplace_id'=>$getmkp);
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

		echo "<p style='color:red;'><b>Pre calculating seller #".$seller_id."</b></p>";
		flush();
		ob_flush();

    // get the highest sales in mkp 2
    if ($getmkp == 2) {
      $data = $this->getHighestSales($seller_id);
      $highest_eu_sales = $data->result;
    }

		foreach ($countries as $key => $country) {
      $total_records_country = 0;
			echo "<p style='color:red;'><b>With country code of ".$country."</b></p>";
			flush();
			ob_flush();

			if ($getmkp == 1) {
        $data = $this->getFBADetailsByCountry($seller_id, $country, $preferred_currency, $getmkp);
        $this->getReversedReimbursements($seller_id, $getmkp, $country);
      } else {
        $data = $this->getFBADetailsByCountry($seller_id, $country, $preferred_currency, $getmkp, $highest_eu_sales);
        $this->getReversedReimbursements($seller_id, $getmkp, $highest_eu_sales);
      }
			$total_refund = $data['total_refund_country'];
			$ctry = $data['country'];
			$curr = $data['curr'];

			$fpc = FbaPreCalculation::where('seller_id', '=', $seller_id)
            ->whereDate('created_at', '=', date('Y-m-d'))
            ->where('country_code', '=', $country)
            ->where('status', '=', 'active')
            ->first();

			if ($total_refund > 0) {
				if (isset($fpc)) {
					$this->updateFbaPreCalculation($fpc->id, $total_refund, $curr);
					$total_records ++;
          $total_records_country ++;
				} else {
					$this->saveFbaPreCalculation($seller_id, $total_refund, $ctry, $curr);
					$total_records ++;
          $total_records_country ++;
				}
				echo "<p style='color:red;'><b>Successfully pre calculated the country code of ".$country."</b></p>";
			} else {
				echo "<p style='color:red;'><b>No pre calculations for the country code of ".$country."</b></p>";
			}

      if ($total_records_country > 0) {
        $tc = TrendleChecker::where('seller_id', '=', $seller_id)
              ->where('checker_name', '=', 'fba_precalc')
              ->where('checker_country', '=', $country)
              ->first();
        if (isset($tc)) {
          $tc->checker_date = Carbon::now();
          $tc->updated_at = Carbon::now();
          $tc->save();
        } else {
          $tc_new = new TrendleChecker;
          $tc_new->seller_id = $seller_id;
          $tc_new->checker_name = 'fba_precalc';
          $tc_new->checker_date = Carbon::now();
          $tc_new->checker_country = $country;
          $tc_new->created_at = Carbon::now();
          $tc_new->updated_at = Carbon::now();
          $tc_new->save();
        }
      }
		}
		flush();
		ob_flush();

		echo 'cron end.';
		flush();
		ob_flush();
		$end_run_time = time();
    $message['message'] = "FBA Refunds PreCalculation Cron Script Run Successfully!";
		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = 1;
		$message['total_records'] = $total_records;
		$message['isError'] = $isError;
		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('FBA Refunds PreCalculation for seller'.$seller_id.' mkp'.$getmkp, false, $message));
		} catch (\Exception $e) {
			$end_run_time = time();
			$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
			$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
			$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
			$message['tries'] = 1;
			$message['total_records'] = (isset($total_records) ? $total_records : 0);
			$message['isError'] = $isError;
			$message['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('FBA Refunds PreCalculation for seller'.$seller_id.' mkp'.$getmkp.' (error)', false, $message));
		}
	}

    private function getPreferredCurrency($seller_id) {
    	$preferred_currency = "gbp";

		$billing = Billing::where('seller_id', '=', $seller_id)->first();
		if (isset($billing)) {
			$preferred_currency = $billing->preferred_currency;
		}

		return $preferred_currency;
    }

	private function getFBADetailsByCountry($seller_id, $country, $preferred_currency, $mkp,  $highest_eu_sales=null) {
      $total_refund = 0;
      $sr = new FinancialEventsReport;
      $tc = TrendleChecker::where('seller_id', '=', $seller_id)
              ->where('checker_name', '=', 'fba_precalc')
              ->where('checker_country', '=', $country)
              ->first();
      if (isset($tc)) {
        $dt = date_format(date_create($tc->checker_date)->modify('+1 day'), 'Y-m-d');
        $oic = $sr->get_oic($seller_id, $country, 'NO', $dt);
      } else {
        $oic = $sr->get_oic($seller_id, $country, 'YES');
      }
      $response = array();
			echo 'loop oic<br>';
			flush();
			ob_flush();

      foreach ($oic as $val) {
				echo '.';
				flush();
				ob_flush();
        $over_45days = $sr->over_45days($val->order_id);
        if (isset($over_45days)) {
          $over_45 = $over_45days->over_45days;
        } else {
          $over_45 = null;
        }
        if ($over_45 == 'YES') {
          $gsc = $sr->getSalesChannel($val->order_id);
          if ($gsc->sales_channel != null) {
            if ($gsc->sales_channel != 'Non-Amazon') {
              // set $total_amount_ordered
              $go = $sr->getOrdered($val->order_id);
              $total_amount_ordered = $go->total_ordered;

              // set $value_of_item
              $ave_total = 0;
              $gar = $sr->getAsinRefund($val->order_id);
              if (isset($gar)) {
                foreach ($gar as $key => $v) {
                  $nb = $sr->getNbOrder($key, $country);
                  if (isset($nb)) {
                    $ave_total += ($nb->average * $v);
                  }
                  // $fmv = $sr->getFMV($key, $country);
                  // $fmv_sales = $fmv->sales;
                  // $fmv_cost = $fmv->cost;
                  // $fmv_quantity = $fmv->quantity;
                  // $fmv3 = $sr->getFMV3months($key, $country);
                  // $fmv3_sales = $fmv3->sales;
                  // $fmv3_cost = $fmv3->cost;
                  // $fmv3_quantity = $fmv3->quantity;
                }
              }
              $value_of_item = $ave_total;


              $ga = $sr->getAdjusted($val->order_id);
              // set $original_claim_amount
              if ($ga->fmv_good == false) {
                $original_claim_amount = $total_amount_ordered;
              } else {
                if ($val->quantity_refunded == $go->quantity_ordered) {
                  $original_claim_amount = ($total_amount_ordered > $value_of_item) ? $total_amount_ordered : $value_of_item;
                } else {
                  $original_claim_amount = $value_of_item * $val->quantity_refunded;
                }                
              }

              // set the $claim_amount and $claim_name
              $claim_amount = 0;
              $claim_name = '';

              // set the values needed from returns_reports
              $gret = $sr->getReturns($val->order_id);
              $has_valid_dd = false;
              $has_invalid_dd = false;
              $quantity_returned = 0;
              $date_of_return = '';
              $detailed_disposition = 'Item Not Returned';
              $return_reason = '';
              $quantity_unsellable = 0;
              $quantity_rr = 0;

              if (isset($gret)) {
                foreach ($gret as $v1) {

                  $return_reason = $v1['rr'];
                  $quantity_rr += $v1['quantity_rr'];
                  $quantity_unsellable += $v1['quantity_unsellable'];

                  if ($v1['dd'] == 'DAMAGED' || $v1['dd'] == 'CARRIER_DAMAGED') {
                    $quantity_returned += $v1['quantity_dd'];
                    $date_of_return = $v1['date'];
                    $detailed_disposition = $v1['dd'];
                    $has_valid_dd = true;
                  } else {
                    $detailed_disposition = $v1['dd'];
                    $quantity_returned += $v1['quantity'];
                    $has_invalid_dd = true;
                  }
                }
              }
              $double_check = false;
              if ($has_valid_dd == true && $has_invalid_dd == true && $ga->quantity_adjusted > 0) {
                $double_check = true;
              }

              // SCENARIO: AMZ fault || AMZ fault & Product Not Returned || AMZ fault & Returned Unsellable
              $return_reason_array = array('DAMAGED_BY_FC','MISSED_ESTIMATED_DELIVERY','DAMAGED_BY_CARRIER','EXTRA_ITEM');
              if (in_array($return_reason, $return_reason_array)) {
                if ($quantity_unsellable == 0) {
                  $gwaas = $sr->getWrongAddressAmountsSellable($val->order_id);
                  $claim_amount = $gwaas->claim_amount;
                  $claim_name = 'AMZ fault';
                } else {
                  $gwaa = $sr->getWrongAddressAmounts($val->order_id);
                  if ($detailed_disposition == 'Item Not Returned') {
                    $claim_amount = ($gwaa->claim_amount + $original_claim_amount);
                    $claim_name = 'AMZ fault & Product Not Returned';
                  } else {
                    $claim_amount = ($gwaa->claim_amount + $original_claim_amount);
                    $claim_name = 'AMZ fault & Returned Unsellable';
                  }
                }
              }

              // SCENARIO: Damaged by AMZ or Carrier || Not Returned
              if ($double_check == false) {
                  if ($detailed_disposition == 'DAMAGED' || $detailed_disposition == 'CARRIER_DAMAGED') {
                      if ($claim_amount > $original_claim_amount) {
                        $claim_amount = $claim_amount;
                      } else {
                        $claim_amount = $original_claim_amount;
                      }
                      $claim_name .= ($claim_name == '') ? 'Damaged by AMZ or Carrier' : ' AND Damaged by AMZ or Carrier';
                  } else if ($detailed_disposition == 'Item Not Returned') {
                      if ($claim_amount > $original_claim_amount) {
                        $claim_amount = $claim_amount;
                      } else {
                        $claim_amount = $original_claim_amount;
                      }
                      $claim_name .= ($claim_name == '') ? 'Not Returned' : ' AND Not Returned';
                  }
              }

              // SCENARIO: Refund Larger Than Price
              $glrtp = $sr->getLargerRefundThanPrice($val->order_id);
              if ($glrtp->is_larger == true) {
                $claim_amount += $glrtp->claim_amount;
                $claim_name .= ($claim_name == '') ? 'Refund Larger Than Price' : ' AND Refund Larger Than Price';
              }

              // subtract amount reimbursed
              $claim_amount = round($claim_amount,2) - round($ga->total_adjusted,2);

              // set all data to be saved in order_id_claims
              if ($claim_amount > 0) {
                $data = array();
                $data['seller_id'] = $seller_id;
                $data['country_code'] = $country;
                $data['order_id'] = $val->order_id;
                $data['quantity_ordered'] = $go->quantity_ordered;
                $data['quantity_refunded'] = (int)$val->quantity_refunded;
                $data['quantity_adjusted'] = $ga->quantity_adjusted;
                $data['total_ordered'] = round($go->total_ordered,2);
                $data['total_refunded'] = round($val->total_refunded,2);
                $data['total_adjusted'] = round($ga->total_adjusted,2);
                $data['quantity_returned'] = $quantity_returned;
                $data['date_of_return'] = $date_of_return;
                $data['over_45days'] = $over_45;
                $data['claim_type'] = $ga->claim_type;
                $data['detailed_disposition'] = $detailed_disposition;
                $data['claim_name'] = $claim_name;
                $data['claim_amount'] = round($claim_amount,2);
                $data['difference'] = round($claim_amount,2);
                $data['created_at'] = Carbon::now();
                $data['updated_at'] = Carbon::now();
                $data['return_reason'] = $return_reason;
                $data['quantity_unsellable'] = $quantity_unsellable;
                // $data['fmv_sales'] = round($fmv_sales,2);
                // $data['fmv_cost'] = 0;
                // $data['fmv_quantity'] = $fmv_quantity;
                // $data['fmv'] = ($fmv_quantity == 0) ? 0 : round($fmv_sales / $fmv_quantity,2);
                // $data['fmv3_sales'] = round($fmv3_sales,2);
                // $data['fmv3_cost'] = 0;
                // $data['fmv3_quantity'] = $fmv3_quantity;
                // $data['fmv3'] = ($fmv3_quantity == 0) ? 0 : round($fmv3_sales / $fmv3_quantity,2);
                $total_refund += round($claim_amount,2);

                $ifExist = OrderIdClaim::where('order_id', '=', $val->order_id)->first();
                if (isset($ifExist)) {
                  $ifExist->seller_id = $seller_id;
                  $ifExist->country_code = $country;
                  $ifExist->quantity_ordered = $go->quantity_ordered;
                  $ifExist->quantity_refunded = (int)$val->quantity_refunded;
                  $ifExist->quantity_adjusted = $ga->quantity_adjusted;
                  $ifExist->total_ordered = round($go->total_ordered,2);
                  $ifExist->total_refunded = round($val->total_refunded,2);
                  $ifExist->total_adjusted = round($ga->total_adjusted,2);
                  $ifExist->quantity_returned = $quantity_returned;
                  $ifExist->date_of_return = $date_of_return;
                  $ifExist->over_45days = $over_45;
                  $ifExist->claim_type = $ga->claim_type;
                  $ifExist->detailed_disposition = $detailed_disposition;
                  $ifExist->claim_name = $claim_name;
                  $ifExist->claim_amount = round($claim_amount,2);
                  $ifExist->difference = round(($claim_amount - $ifExist->total_amount_reimbursed),2);
                  $ifExist->updated_at = Carbon::now();
                  $ifExist->return_reason = $return_reason;
                  $ifExist->quantity_unsellable = $quantity_unsellable;
                  // $ifExist->fmv_sales = round($fmv_sales,2);
                  // $ifExist->fmv_cost = 0;
                  // $ifExist->fmv_quantity = $fmv_quantity;
                  // $ifExist->fmv = ($fmv_quantity == 0) ? 0 : round($fmv_sales / $fmv_quantity,2);
                  // $ifExist->fmv3_sales = round($fmv3_sales,2);
                  // $ifExist->fmv3_cost = 0;
                  // $ifExist->fmv3_quantity = $fmv3_quantity;
                  // $ifExist->fmv3 = ($fmv3_quantity == 0) ? 0 : round($fmv3_sales / $fmv3_quantity,2);
                  $ifExist->save();
                } else {
                  $response[] = $data;
                }
              } else {
                $ifExist1 = OrderIdClaim::where('order_id', '=', $val->order_id)->where('total_amount_reimbursed', '=', 0)->first();
                if (isset($ifExist1)) {
                  $ifExist1->claim_name = 'TBD';
                  $ifExist1->updated_at = Carbon::now();
                  $ifExist1->save();
                }
              }
            } //end sales_channel
          }
        } //end over45_days
      }
      OrderIdClaim::insert($response);
			echo '<p style="color:green">loop oic done</p><br>';
			flush();
			ob_flush();

      if ($country == 'us' || $country == 'ca' || $country == $highest_eu_sales) {
        $iar = new InventoryAdjustmentReport;
        if (isset($tc)) {
          $dt = date_format(date_create($tc->checker_date)->modify('+1 day'), 'Y-m-d');
          $fc = $iar->get_fnsku_claims($seller_id, $country, 'NO', $dt, $mkp);
        } else {
          $dt = null;
          $fc = $iar->get_fnsku_claims($seller_id, $country, 'YES', $dt, $mkp);
        }
        $response1 = array();
  			echo '<p>loop fc</p><br>';
  			flush();
  			ob_flush();
        foreach ($fc as $val) {
  				echo '.';
  				flush();
  				ob_flush();

          $gsc = $iar->getSingleColumns($val->fnsku, $country, $mkp);
          $gno = $iar->getNbOrder($val->asin, $mkp, $country);
          // $fmv = $iar->getFMV($val->asin, $mkp, $country);
          // $fmv3 = $iar->getFMV3months($val->asin, $mkp, $country);
          $gru = $iar->getReimbursedUnits($val->fnsku, $mkp, $country);

          // $fmv_sales = $fmv->sales;
          // $fmv_cost = $fmv->cost;
          // $fmv_quantity = $fmv->quantity;
          // $fmv3_sales = $fmv3->sales;
          // $fmv3_cost = $fmv3->cost;
          // $fmv3_quantity = $fmv3->quantity;

          if ($gsc->sum < 0){
            $reimbursement_reason = '';
            $items_lost = 0;
            $items_damaged = 0;
            $is_third_scenario = false;
            $claim_name = '';
            // start // identify if there is a claim or none
            // first scenario
            $claim = false;
            $scenario1 = $gsc->m+$gsc->f;
            if ($scenario1 < 0) {
              $scenario1 = ($scenario1 + $gru->quantity_lost);
              if ($scenario1 < 0) {
                $reimbursement_reason = 'Lost_Warehouse';
                $claim = true;
                $items_lost = $scenario1*(-1);
                $claim_name = 'FnSKU Claim 1';
              }
            }
            // second scenario
            $scenario2 = $gsc->e+$gsc->six+$gsc->q;
            if (($gsc->e < 0) && ($gsc->six < 0) && ($gsc->q < 0)) {
              $scenario2 = ($scenario2 + ($gru->quantity_damaged_in + $gru->quantity_damaged_wa));
              if ($scenario2 < 0) {
                if ($claim == true) {
                  $reimbursement_reason += ' and Damaged_Warehouse and Damaged_Inbound';
                } else {
                  $reimbursement_reason = 'Damaged_Warehouse and Damaged_Inbound';
                }
                $claim = true;
                $items_damaged = $scenario2*(-1);
                $claim_name = 'FnSKU Claim 1';
              }
            }
            // third scenario
            if ($claim == false) {
              $gll = $iar->getLatestLost($val->fnsku, $mkp, $country);
              $scenario3a = $gsc->m+$gsc->f;
              if ($scenario3a < 0) {
                $scenario3a = ($scenario3a + $gll->quantity_lost);
                if ($scenario3a < 0) {
                  $reimbursement_reason = 'Lost_Warehouse';
                  $claim = true;
                  $items_lost = $scenario3a*(-1);
                  $is_third_scenario = true;
                  $claim_name = 'FnSKU Claim 2';
                }
              }

              $gld = $iar->getLatestDamaged($val->fnsku, $mkp, $country);
              $scenario3b = $gsc->e+$gsc->six+$gsc->q;
              if (($gsc->e < 0) && ($gsc->six < 0) && ($gsc->q < 0)) {
                $scenario3b = ($scenario3b + ($gru->quantity_damaged_in + $gru->quantity_damaged_wa));
                if ($scenario3b < 0) {
                  if ($claim == true) {
                    $reimbursement_reason += ' and Damaged_Warehouse and Damaged_Inbound';
                  } else {
                    $reimbursement_reason = 'Damaged_Warehouse and Damaged_Inbound';
                  }
                  $claim = true;
                  $items_damaged = $scenario3b*(-1);
                  $is_third_scenario = true;
                  $claim_name = 'FnSKU Claim 2';
                }
              }
            }
            // end // identify if there is a claim or none

            if ($claim == true) {
              $units = $items_lost + $items_damaged;
              if ($gno->quantity != 0) {
                $total_owed = round((round($gno->revenue, 2)/$gno->quantity)*($units),2);
              } else {
                $total_owed = 0;
              }

              if ($gru->reimbursed_units == null) {
                $reimbursed_units  = 0;
              } else {
                $reimbursed_units  = $gru->reimbursed_units;
              }

              if ($total_owed > 0) {
                $data1 = array();
                $data1['seller_id'] = $seller_id;
                $data1['country_code'] = $country;
                $data1['fnsku'] = $val->fnsku;
                $data1['three'] = $gsc->three;
                $data1['four'] = $gsc->four;
                $data1['five'] = $gsc->five;
                $data1['six'] = $gsc->six;
                $data1['d'] = $gsc->d;
                $data1['e'] = $gsc->e;
                $data1['f'] = $gsc->f;
                $data1['m'] = $gsc->m;
                $data1['n'] = $gsc->n;
                $data1['o'] = $gsc->o;
                $data1['p'] = $gsc->p;
                $data1['q'] = $gsc->q;
                $data1['summation'] = $gsc->sum;
                $data1['reimbursed_units'] = $reimbursed_units;
                $data1['reimbursed_reasons'] = $reimbursement_reason;
                $data1['items_lost'] = $items_lost;
                $data1['items_damaged'] = $items_damaged;
                $data1['is_third_scenario'] = $is_third_scenario;
                $data1['units'] = $units;
                $data1['average_value'] = $gno->average;
                $data1['claim_name'] = $claim_name;
                $data1['total_owed'] = $total_owed;
                $data1['created_at'] = Carbon::now();
                $data1['updated_at'] = Carbon::now();
                // $data1['fmv_sales'] = round($fmv_sales,2);
                // $data1['fmv_cost'] = round($fmv_cost,2);
                // $data1['fmv_quantity'] = $fmv_quantity;
                // $data1['fmv'] = ($fmv_quantity == 0) ? 0 : round(($fmv_sales + $fmv_cost) / $fmv_quantity,2);
                // $data1['fmv3_sales'] = round($fmv3_sales,2);
                // $data1['fmv3_cost'] = round($fmv3_cost,2);
                // $data1['fmv3_quantity'] = $fmv3_quantity;
                // $data1['fmv3'] = ($fmv3_quantity == 0) ? 0 : round(($fmv3_sales + $fmv3_cost) / $fmv3_quantity,2);
                $total_refund += ($gno->quantity != 0) ? ($gno->revenue/$gno->quantity)*($units) : 0;

                $ifExist1 = FnskuClaim::where('fnsku', '=', $val->fnsku)->first();
                if (isset($ifExist1)) {
                  $ifExist1->seller_id = $seller_id;
                  $ifExist1->country_code = $country;
                  $ifExist1->three = $gsc->three;
                  $ifExist1->four = $gsc->four;
                  $ifExist1->five = $gsc->five;
                  $ifExist1->six = $gsc->six;
                  $ifExist1->d = $gsc->d;
                  $ifExist1->e = $gsc->e;
                  $ifExist1->f = $gsc->f;
                  $ifExist1->m = $gsc->m;
                  $ifExist1->n = $gsc->n;
                  $ifExist1->o = $gsc->o;
                  $ifExist1->p = $gsc->p;
                  $ifExist1->q = $gsc->q;
                  $ifExist1->summation = $gsc->sum;
                  $ifExist1->reimbursed_units = $reimbursed_units;
                  $ifExist1->reimbursed_reasons = $reimbursement_reason;
                  $ifExist1->items_lost = $items_lost;
                  $ifExist1->items_damaged = $items_damaged;
                  $ifExist1->is_third_scenario = $is_third_scenario;
                  $ifExist1->units = $units;
                  $ifExist1->average_value = $gno->average;
                  $ifExist1->claim_name = $claim_name;
                  $ifExist1->total_owed = $total_owed;
                  $ifExist1->difference = round(($total_owed - $ifExist1->total_amount_reimbursed),2);
                  $ifExist1->updated_at = Carbon::now();
                  // $ifExist1->fmv_sales = round($fmv_sales,2);
                  // $ifExist1->fmv_cost = round($fmv_cost,2);
                  // $ifExist1->fmv_quantity = $fmv_quantity;
                  // $ifExist1->fmv = ($fmv_quantity == 0) ? 0 : round(($fmv_sales + $fmv_cost) / $fmv_quantity,2);
                  // $ifExist1->fmv3_sales = round($fmv3_sales,2);
                  // $ifExist1->fmv3_cost = round($fmv3_cost,2);
                  // $ifExist1->fmv3_quantity = $fmv3_quantity;
                  // $ifExist1->fmv3 = ($fmv3_quantity == 0) ? 0 : round(($fmv3_sales + $fmv3_cost) / $fmv3_quantity,2);
                  $ifExist1->save();
                } else {
                  $response1[] = $data1;
                }
              }
            }
          }
        }
        FnskuClaim::insert($response1);
  			echo '<p style="color:green">loop fc done</p><br>';
  			flush();
  			ob_flush();
      }

      $curr='GBP';
      if($country == 'us') $curr = "USD";
      if($country == 'ca') $curr = "CAD";
      if($country == 'fr' || $country == 'de' || $country == 'es' || $country == 'it') $curr = "EUR";
      if($country == 'uk') $curr = "GBP";

      $total_refund = currency($total_refund, $curr, strtoupper($preferred_currency), false);
      $data = array(
        'total_refund_country'=> round($total_refund,2),
        'country' => $country,
        'curr' => $preferred_currency
        );
      return $data;
    }

    private function saveFbaPreCalculation($seller_id, $total_refund, $country, $currency) {
    	$fpc = new FbaPreCalculation;
    	$fpc->seller_id = $seller_id;
    	$fpc->total_owed = $total_refund;
    	$fpc->country_code = $country;
    	$fpc->currency = $currency;
    	$fpc->status = 'active';
    	$fpc->created_at = Carbon::now();
    	$fpc->updated_at = Carbon::now();
    	$fpc->save();
    }

    private function updateFbaPreCalculation($id, $total_refund, $currency) {
    	$fpc = FbaPreCalculation::find($id);
    	$fpc->total_owed = $total_refund;
    	$fpc->currency = $currency;
    	$fpc->status = 'active';
    	$fpc->updated_at = Carbon::now();
    	$fpc->save();
    }

    private function getHighestSales($seller_id) {
      $data = (object) null;

      $iar = new InventoryAdjustmentReport;
      $gsu = $iar->getSalesUk($seller_id);
      $gsf = $iar->getSalesFr($seller_id);
      $gsi = $iar->getSalesIt($seller_id);
      $gse = $iar->getSalesEs($seller_id);
      $gsd = $iar->getSalesDe($seller_id);


      $r = array();
      $r['uk'] = $gsu->sales;
      $r['fr'] = $gsf->sales;
      $r['it'] = $gsi->sales;
      $r['es'] = $gse->sales;
      $r['de'] = $gsd->sales;

      $highest_key = '';
      $highest_value = 0;
      foreach ($r as $key => $value) {
        if ($value > $highest_value) {
          $highest_value = $value;
          $highest_key = $key;
        }
      }

      $data->result = $highest_key;
      return $data;
    }

    private function getReversedReimbursements($seller_id, $mkp, $country) {
      $amount_reversed = 0;

      $query = DB::connection('mysql2')
                  ->table('fba_refund_trans')
                  ->where('fba_refund_trans.amount_reimbursed', '>', 0)
                  ->where('fba_refund_trans.seller_id', '=', $seller_id)
                  ->orderBy('fba_refund_trans.created_at', 'asc')
                  ->limit(1)
                  ->get([
                    DB::raw('DATE(fba_refund_trans.created_at) AS created_at')
                  ]);
      $first_occurence_date = '';
      if (isset($query)) {
        foreach ($query as $val) {
          $first_occurence_date = $val->created_at;
        }
      }

      if ($mkp == 2) {
        $c_arr[] = 'GBP';
        $c_arr[] = 'EUR';
      } else {
        if ($country == 'us') {
          $c_arr[] = 'USD';
        } elseif ($country == 'ca') {
          $c_arr[] = 'CAD';
        }
      }

      $currency = '';
      if ($country = 'us') {
        $currency = 'USD';
      } elseif ($country = 'ca') {
        $currency = 'CAD';
      } elseif ($country = 'uk') {
        $currency = 'GBP';
      } elseif ($country = 'it' || $country = 'fr' || $country = 'de' || $country = 'es') {
        $currency = 'EUR';
      }

      if ($first_occurence_date != '') {
        $query1 = DB::connection('mysql2')
                    ->table('reimbursements')
                    ->where('reimbursements.reason', '=', 'Reimbursement_Reversal')
                    ->whereRaw('DATE(reimbursements.approval_date) BETWEEN "'.$first_occurence_date.'" AND CURDATE()')
                    ->where('reimbursements.seller_id', '=', $seller_id)
                    ->whereIN('reimbursements.currency_unit', $c_arr)
                    ->get([
                      'reimbursements.original_reimbursement_id',
                      'reimbursements.reimbursement_id'
                    ]);
        $r_id = array();
        foreach ($query1 as $val1) {
          $r = Reimbursement::where('reimbursement_id', '=', $val1->original_reimbursement_id)->first();
          if($r->case_id != '' || $r->case_id != null) {
            $r_id[] = $val1->reimbursement_id;
          }
        }

        foreach ($r_id as $key => $value) {
          $r1 = Reimbursement::where('reimbursement_id', '=', $value)->first();
          $amount_reversed += currency(($r1->amount_per_unit*$r1->quantity_reimbursed_inventory), $r1->currency_unit, $currency, false);
        }
      }

      if ($amount_reversed != 0) {
        $amount_reversed = currency($amount_reversed, $currency, strtoupper($preferred_currency), false);

        $frp = new FbaRefundTran;
        $frp->seller_id = $seller_id;
        $frp->amount_reversed = $amount_reversed;
        $frp->currency = $preferred_currency;
        $frp->country_code = $country;
        $frp->created_at = Carbon::now();
        $frp->updated_at = Carbon::now();
        $frp->save();
      }
    }

}
