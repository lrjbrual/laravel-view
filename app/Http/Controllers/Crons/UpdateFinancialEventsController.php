<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;
use App\MarketplaceAssign;
use App\SettlementReport;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use App\Product;
use App\Log;
use App\UniversalModel;
use AmazonFinancialEventList;
use AmazonMWSConfig;
use Carbon\Carbon;
use App\Seller;

use Mail;

class UpdateFinancialEventsController extends Controller
{
    //
    private $seller_id;
    private $mkp='';
    private $check_empty = false;
    private $main_posted_date;

    public function index(){
    try {
    	ini_set('memory_limit', '-1');
		ini_set("max_execution_time", 0);  // on
		ob_start();
		header('X-Accel-Buffering: no');
    	$total_records = 0;
    	$fulfilled = new UniversalModel();
    	$product = new Product();

    	if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
        {
        	echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
            exit;
        }
        else
        {
            $this->seller_id = trim(Input::get('seller_id'));
        }

        //checker for invalid payment -Altsi
        $seller = Seller::find($this->seller_id);

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
        //


		//response for mail
		$time_start = time();
		$isError = false;
		$response['time_start'] = date('Y-m-d H:i:s');
		$response['total_time_of_execution'] = 0;
		$response['message'] = "Financial Events Cron Successfully Fetch Data!";
		$response['isError'] = false;
		$response['tries'] = 0;
		$tries=0;
		$message = "Financial Events Cron Successfully Fetch Data!";

    	$report_type = 'No Report Type.';
    	$response = array();

    	$isEmpty = false;
    	$mkp_assign = array();
    	$q= new MarketplaceAssign();
    	$where = array('seller_id'=>$this->seller_id);
    	$w = array('seller_id'=> $this->seller_id);
    	if( Input::get('mkp') != null OR Input::get('mkp') != "" )
        {
        	$this->mkp = trim(Input::get('mkp'));
        	$where  = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);

        	if($this->mkp == 2) $w = array('seller_id'=> $this->seller_id, 'like' => ['marketplace_name','uk']);
        	else $w = array('seller_id'=> $this->seller_id, 'like' => ['marketplace_name','com']);
        }

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Financial Events for seller'.$this->seller_id.' mkp'.$this->mkp, true));

        $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());


		$seller_details = array();


		$ff_data_count = array();
		if(count($mkp_assign)>0) $ff_data_count = $fulfilled->getRecords('financial_events_reports',array('*'),$w,array(),true);
		$flag = 1;

	    $dtoday = Carbon::today()->addMinutes(3);

		if(count($mkp_assign)<=0){
			$response['time_start'] = date('Y-m-d H:i:s');
			$response['time_end'] = date('Y-m-d H:i:s');
			$response['isError'] = true;
			$response['message'] = "No Marketplace assigned!";
			$message = "No Marketplace assigned!";
			$response['total_time_of_execution'] = 0;
			$response['tries'] = 0;
			$isError=true;
			echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
		}
		$this->check_empty = $isEmpty;

	        if(count($ff_data_count)>0){
			$isEmpty = false;
			$flag = 1;
		$dsubmonths = Carbon::today()->addMinutes(3)->subDays(7);
		$dsub = Carbon::today()->addMinutes(3)->subDays(7);
		}else{
			$isEmpty = true;
			$flag = 24;
		$dsubmonths = Carbon::today()->addMinutes(3)->subMonths(24);
		$dsub = Carbon::today()->addMinutes(3)->subMonths(24);
		}

		foreach ($mkp_assign as $value) {

			$c_key='';
			if($value->marketplace_id == 1){
				$c_key = 'na';
			}
			if($value->marketplace_id == 2){
				$c_key = 'eu';
			}
			$merchantId = $value->mws_seller_id;
			$MWSAuthToken = $value->mws_auth_token;

		    $tries++;
	    	$country = "All ".$c_key;
	    	$init = array(
				'merchantId'    => $merchantId,
	            'MWSAuthToken'  => $MWSAuthToken,
	    		);
	    	$amz = $this->setAmazonConfig($c_key,$init);
	    	//loop for each month
	    	$iii = 1;
	    	//echo $dsub;
	    	while($dsub!=$dtoday){
	    	//for($iii=$flag; $iii > 0; $iii--){
       			gc_collect_cycles();
				ini_set("max_execution_time", 0);
	    		$start_date = "+".($iii-1)." days" . $dsubmonths;
	    		$end_date = "+".$iii." days" . $dsubmonths;
	    		if($iii==1){
	    			$start_date = $dsubmonths;
	    			$end_date = "+".$iii." day" . $dsubmonths;
	    		}
	    		if($iii == 2) $start_date = "+".($iii-1)." day" . $dsubmonths;
	    		$this->main_posted_date = $dsub;
	    		echo " start date: ".$start_date." end date: ".$end_date."<br>";
	    		ob_flush();
		     	flush();
		     	$iii++;
		     	$end_date = strtotime($end_date);
		     	$start_date = strtotime($start_date);

		    	$response = $this->getFinancialEvents($start_date, $end_date, $amz);
		    	$shipments = $response['Shipment'];
		    	$refunds = $response['Refund'];
		    	$adjustments = $response['Adjustment'];
		    	$guarantee_claims = $response['GuaranteeClaim'];
		    	$charge_backs = $response['Chargeback'];
		    	$pay_with_amazons = $response['PayWithAmazon'];
		    	$service_provider_credits = $response['ServiceProviderCredit'];

		    	$Retrocharge = $response['Retrocharge'];
		    	$RentalTransaction = $response['RentalTransaction'];
		    	$PerformanceBondRefund = $response['PerformanceBondRefund'];
		    	$ServiceFee = $response['ServiceFee'];
		    	$DebtRecovery = $response['DebtRecovery'];
		    	$LoanServicing = $response['LoanServicing'];
		    	$SAFETReimbursement = $response['SAFETReimbursement'];

				$this->_insert_Retrocharge($fulfilled,$Retrocharge,$this->seller_id);
				$this->_insert_RentalTransaction($fulfilled,$RentalTransaction,$this->seller_id);
				$this->_insert_PerformanceBondRefund($fulfilled,$PerformanceBondRefund,$this->seller_id);
        $this->_insert_ServiceFee($fulfilled,$ServiceFee,$this->seller_id);
				$this->_insert_DebtRecovery($fulfilled,$DebtRecovery,$this->seller_id);
				$this->_insert_LoanServicing($fulfilled,$LoanServicing,$this->seller_id);
				$this->_insert_SAFETReimbursement($fulfilled,$SAFETReimbursement,$this->seller_id);

          		gc_collect_cycles();
		    	// GuaranteeClaim
		    	echo "<br>GuaranteeClaim<br>";
		    	if($guarantee_claims!=false){
			    	foreach ($guarantee_claims as $claims) {
			    		$arr = explode('T', $claims['PostedDate']);
		    			$arr2  =explode('Z', $arr[1]);
		    			$posted_date = $arr[0]." ".$arr2[0];
		    			$currency = "";

		    			$raw = array();
		    			$raw['posted_date'] = $arr[0]." ".$arr2[0];
		    			$raw['order_id'] = $claims['AmazonOrderId'];
		    			$raw['marketplace_name'] = $claims['MarketplaceName'];
		    			$raw['seller_id'] = $this->seller_id;
		    			$raw['type'] = "Guarantee Claim";

		    			if(isset($claims['ShipmentItemAdjustmentList'])){
			    			foreach ($claims['ShipmentItemAdjustmentList'] as $value) {
			    				$raw2 = array();
			    				$raw2 = $raw;
			    				$raw2['sku'] = (!isset($value['SellerSKU'])) ? "" : $value['SellerSKU'];
			    				$raw2['order_item_code'] = (!isset($value['OrderItemId'])) ? "" : $value['OrderItemId'];
			    				$raw2['quantity'] = (!isset($value['QuantityShipped'])) ? "" : $value['QuantityShipped'];
			    				$raw2['merchant_adjustment_item_id'] = (!isset($value['OrderAdjustmentItemId'])) ? "" : $value['OrderAdjustmentItemId'];

			    				foreach ($value['ItemChargeAdjustmentList'] as $val) {
			    					$raw3 = array();
			    					$raw3 = $raw2;
			    					$raw3['price_type'] = $val['ChargeType'];
			    					$raw3['price_amount'] = $val['Amount'];
			    					$raw3['currency'] = $val['CurrencyCode'];
			    					if($isEmpty){
			    						$raw3['created_at'] = date('Y-m-d H:i:s');
			    						$fulfilled->insertData('financial_events_report_raws', $raw3);
			    					}else{
				    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
											$raw3['created_at'] = date('Y-m-d H:i:s');
				    						$fulfilled->insertData('financial_events_report_raws', $raw3);
				    					}
				    				}
			    				}

			    				if(isset($value['ItemFeeAdjustmentList'])){
			    					foreach ($value['ItemFeeAdjustmentList'] as $val) {
				    					$raw3 = array();
				    					$raw3 = $raw2;
				    					$raw3['item_related_fee_type'] = $val['FeeType'];
				    					$raw3['item_related_fee_amount'] = $val['Amount'];
				    					$raw3['currency'] = $val['CurrencyCode'];
				    					if($isEmpty){
				    						$raw3['created_at'] = date('Y-m-d H:i:s');
				    						$fulfilled->insertData('financial_events_report_raws', $raw3);
				    					}else{
					    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
												$raw3['created_at'] = date('Y-m-d H:i:s');
					    						$fulfilled->insertData('financial_events_report_raws', $raw3);
					    					}
					    				}
				    				}
			    				}
					    	}
					    }
				    }
				}
		    	// Chargeback
          		gc_collect_cycles();
		    	echo "Chargeback<br>";
		    	if($charge_backs!=false){
			    	foreach ($charge_backs as $charge_back) {
			    		$arr = explode('T', $charge_back['PostedDate']);
		    			$arr2  =explode('Z', $arr[1]);
		    			$posted_date = $arr[0]." ".$arr2[0];
		    			$currency = "";

		    			$raw = array();
		    			$raw['posted_date'] = $arr[0]." ".$arr2[0];
		    			$raw['order_id'] = $charge_back['AmazonOrderId'];
		    			$raw['marketplace_name'] = $charge_back['MarketplaceName'];
		    			$raw['seller_id'] = $this->seller_id;
		    			$raw['type'] = "Charge Back";

		    			if(isset($charge_back['ShipmentItemAdjustmentList'])){
			    			foreach ($charge_back['ShipmentItemAdjustmentList'] as $value) {
			    				$raw2 = array();
			    				$raw2 = $raw;
			    				$raw2['sku'] = (!isset($value['SellerSKU'])) ? "" : $value['SellerSKU'];
			    				$raw2['order_item_code'] = (!isset($value['OrderItemId'])) ? "" : $value['OrderItemId'];
			    				$raw2['quantity'] = (!isset($value['QuantityShipped'])) ? "" : $value['QuantityShipped'];
			    				$raw2['merchant_adjustment_item_id'] = (!isset($value['OrderAdjustmentItemId'])) ? "" : $value['OrderAdjustmentItemId'];

			    				foreach ($value['ItemChargeAdjustmentList'] as $val) {
			    					$raw3 = array();
			    					$raw3 = $raw2;
			    					$raw3['price_type'] = $val['ChargeType'];
			    					$raw3['price_amount'] = $val['Amount'];
			    					$raw3['currency'] = $val['CurrencyCode'];
			    					if($isEmpty){
			    						$raw3['created_at'] = date('Y-m-d H:i:s');
			    						$fulfilled->insertData('financial_events_report_raws', $raw3);
			    					}else{
				    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
				    						$raw3['created_at'] = date('Y-m-d H:i:s');
				    						$fulfilled->insertData('financial_events_report_raws', $raw3);
				    					}
				    				}
			    				}

			    				if(isset($value['ItemFeeAdjustmentList'])){
			    					foreach ($value['ItemFeeAdjustmentList'] as $val) {
				    					$raw3 = array();
				    					$raw3 = $raw2;
				    					$raw3['item_related_fee_type'] = $val['FeeType'];
				    					$raw3['item_related_fee_amount'] = $val['Amount'];
				    					$raw3['currency'] = $val['CurrencyCode'];
				    					if($isEmpty){
				    						$raw3['created_at'] = date('Y-m-d H:i:s');
				    						$fulfilled->insertData('financial_events_report_raws', $raw3);
				    					}else{
					    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
					    						$raw3['created_at'] = date('Y-m-d H:i:s');
					    						$fulfilled->insertData('financial_events_report_raws', $raw3);
					    					}
					    				}
				    				}
			    				}
					    	}
					    }
			    	}
			    }
          		gc_collect_cycles();
		    	// PayWithAmazon
		    	echo "PayWithAmazon<br>";
		    	if($pay_with_amazons!=false){
			    	foreach ($pay_with_amazons as $pay_with_amazon) {
			    		$raw =array();
			    		$arr = explode('T', $pay_with_amazon['TransactionPostedDate']);
		    			$arr2  =explode('Z', $arr[1]);
		    			$posted_date = $arr[0]." ".$arr2[0];
			    		$raw['order_id'] = $pay_with_amazon['SellerOrderId'];
			    		$raw['posted_date'] = $posted_date;
			    		$raw['transaction_type'] = $pay_with_amazon['BusinessObjectType'];
			    		$raw['marketplace_name'] = $pay_with_amazon['SalesChannel'];
			    		$raw['price_type'] = $pay_with_amazon['PaymentAmountType'];
			    		$raw['price_amount'] = $pay_with_amazon['AmountDescription'];
			    		$raw['fulfillment_id'] = $pay_with_amazon['FulfillmentChannel'];
			    		$raw['seller_store_name'] = $pay_with_amazon['StoreName'];
			    		$raw['type'] = "PayWithAmazon";
			    		$order_fee_amount = 0;
			    		$order_fee_type = "";
			    		$currency = "";
			    		if(isset($pay_with_amazon['Charge'])){
			    			$order_fee_type = $pay_with_amazon['Charge']['ChargeType'];
			    			$order_fee_amount = $pay_with_amazon['Charge']['Amount'];
			    			$currency = $pay_with_amazon['Charge']['CurrencyCode'];
			    		}
			    		$raw['order_fee_amount'] = $order_fee_amount;
			    		$raw['order_fee_type'] = $order_fee_type;
			    		if(isset($pay_with_amazon['FeeList'])){
			    			foreach ($pay_with_amazon['FeeList'] as  $value) {
			    				$raw2 = array();
			    				$raw2 = $raw;
			    				$currency = (!isset($value['CurrencyCode'])) ? "" : $value['CurrencyCode'];
			    				$raw2['currency'] = $currency;
			    				$raw2['item_related_fee_type'] = (!isset($value['FeeType'])) ? "" : $value['FeeType'];
			    				$raw2['item_related_fee_amount'] = (!isset($value['Amount'])) ? "" : $value['Amount'];
			    				if($isEmpty){
			    					$raw2['created_at'] = date('Y-m-d H:i:s');
		    						$fulfilled->insertData('financial_events_report_raws', $raw2);
			    				}else{
				    				if(!$fulfilled->isExist('financial_events_report_raws', $raw2)){
				    					$raw2['created_at'] = date('Y-m-d H:i:s');
			    						$fulfilled->insertData('financial_events_report_raws', $raw2);
			    					}
			    				}
			    			}
			    		}

			    	}
			    }
          		gc_collect_cycles();
		    	// ServiceProviderCredit
		    	echo "ServiceProviderCredit<br>";
		    	if($service_provider_credits!=false){
			    	foreach ($service_provider_credits as $service_provider_credit) {
			    		$data['order_id'] = $service_provider_credit['SellerOrderId'];
			    		$data['marketplace_name'] = $service_provider_credit['MarketplaceId'];
			    		$data['amazon_seller_id'] = $service_provider_credit['SellerId'];
			    		$data['seller_store_name'] = $service_provider_credit['SellerStoreName'];
			    		$data['provider_id'] = $service_provider_credit['ProviderId'];
			    		$data['provider_store_name'] = $service_provider_credit['ProviderStoreName'];
			    		$data['type'] = 'ServiceProviderCredit';
			    		$data['posted_date'] = $this->main_posted_date;
			    		$data['transaction_type'] = $service_provider_credit['ProviderTransactionType'];
			    		if($isEmpty){
			    			$data['created_at'] = date('Y-m-d H:i:s');
    						$fulfilled->insertData('financial_events_report_raws', $data);
			    		}else{
				    		if(!$fulfilled->isExist('financial_events_report_raws', $data)){
				    			$data['created_at'] = date('Y-m-d H:i:s');
	    						$fulfilled->insertData('financial_events_report_raws', $data);
	    					}
	    				}
			    	}
			    }

          		gc_collect_cycles();
		    	//adjustments
          		if($adjustments!=false){
			    	foreach ($adjustments as $value) {
			    		$adjustment_type = (!isset($value['AdjustmentType'])) ? "" : $value['AdjustmentType'];
			    		$total_amount = 0;
			    		$currency = (!isset($value['CurrencyCode'])) ? "" : $value['CurrencyCode'];
			    		$type = "Adjustment";
			    		$sku = "";
			    		$fnsku = "";
			    		$asin = "";
			    		$quantity = 0;

			    		if(isset($value['AdjustmentItemList'])){
				    		foreach ($value['AdjustmentItemList'] as $val) {
				    			$sku = (!isset($val['SellerSKU'])) ? "" : $val['SellerSKU'];
					    		$fnsku = (!isset($val['FnSKU'])) ? "" : $val['FnSKU'];
					    		$asin = (!isset($val['ASIN'])) ? "" : $val['ASIN'];
					    		$quantity = (!isset($val['Quantity'])) ? 0 : $val['Quantity'];
					    		$total_amount = (!isset($val['TotalAmount']['Amount'])) ? 0 : $val['TotalAmount']['Amount'];

					    		if($total_amount == 0){
					    			$total_amount = (!isset($val['PerUnitAmount']['Amount'])) ? "" : $val['PerUnitAmount']['Amount'];
					    			$total_amount = $total_amount * $quantity;
					    		}

					    		if($asin == '' || $asin == ' ' || $asin == null){
					    			$as="";
					    			$asin = $product->setConnection('mysql2')->where('sku',$sku)
					    					->where('seller_id', $this->seller_id)
					    					->first();

					    			if($asin != "" OR $asin != null){
					    				$asin = $asin->asin;
					    			}else{
					    				$asin = "";
					    			}
					    		}
					    		$data = [
						    		'price_type'=> $adjustment_type,
						    		'price_amount' => $total_amount,
						    		'sku'=>$sku,
						    		'asin'=>$asin,
						    		'type'=>$type,
						    		'quantity'=>$quantity,
						    		'currency'=>$currency,
						    		'seller_id'=>$this->seller_id
						    	];
			    				$data['posted_date'] = $this->main_posted_date;
						    	if($isEmpty){
						    		$data['created_at'] = date('Y-m-d H:i:s');
						    		$fulfilled->insertData('financial_events_reports',$data);
						    		$total_records++;

						    		$data['created_at'] = date('Y-m-d H:i:s');
						    		$fulfilled->insertData('financial_events_report_raws',$data);

						    	}else{
							    	if(!$fulfilled->isExist('financial_events_reports',$data)){
							    		$data['created_at'] = date('Y-m-d H:i:s');
							    		$fulfilled->insertData('financial_events_reports',$data);
							    		$total_records++;
							    	}

							    	if(!$fulfilled->isExist('financial_events_report_raws',$data)){
							    		$data['created_at'] = date('Y-m-d H:i:s');
							    		$fulfilled->insertData('financial_events_report_raws',$data);
							    		//$total_records++;
							    	}
							    }
				    		}
			    		}
			    	}
       			}
          		gc_collect_cycles();
          		if($shipments!=false){
			    	foreach ($shipments as $shipment) {
			    		$arr = explode('T', $shipment['PostedDate']);
		    			$arr2  =explode('Z', $arr[1]);
		    			$posted_date = $arr[0]." ".$arr2[0];
		    			$raw = array();
		    			$raw['posted_date'] = $arr[0]." ".$arr2[0];
		    			$raw['order_id'] = $shipment['AmazonOrderId'];
		    			$raw['marketplace_name'] = $shipment['MarketplaceName'];
		    			$raw['seller_id'] = $this->seller_id;
		    			$raw['type'] = "Order";

		    			foreach ($shipment['ShipmentItemList'] as $v) {
		    				// price_type
			    			$GiftWrap = 0;
							$GiftWrapTax = 0;
							$ShippingTax = 0;
							// item_related_fee_type
			    			$Commission = 0;
							$FBAPerOrderFulfillmentFee = 0;
							$FBAPerUnitFulfillmentFee = 0;
							$FBAWeightBasedFee = 0;
							$FixedClosingFee = 0;
							$GetPaidFasterFee = 0;
							$GiftWrapChargeback = 0;
							$GiftWrapCommission = 0;
							$SalesTaxCollectionFee = 0;
							$ShippingChargeback = 0;
							$ShippingHB = 0;
							$VariableClosingFee = 0;
		    				$raw2 = array();
		    				$raw2 = $raw;
		    				$raw2['sku'] = $v['SellerSKU'];
		    				$raw2['order_item_code'] = $v['OrderItemId'];
		    				$raw2['quantity'] = $v['QuantityShipped'];
		    				if(isset($v['ItemChargeList'])){
                  // $v['ItemChargeList'] = array_filter($v['ItemChargeList']);
			    				foreach ($v['ItemChargeList'] as $val) {
			    					$raw3 = array();
			    					$raw3 = $raw2;
			    					$raw3['price_type'] = $val['ChargeType'];
			    					$raw3['price_amount'] = $val['Amount'];
			    					$raw3['currency'] = $val['CurrencyCode'];

			    					if ($val['ChargeType'] == 'GiftWrap') {
				    					$GiftWrap += $val['Amount'];
				    				} elseif ($val['ChargeType'] == 'GiftWrapTax') {
			    						$GiftWrapTax += $val['Amount'];
				    				} elseif ($val['ChargeType'] == 'ShippingTax') {
			    						$ShippingTax += $val['Amount'];
				    				}
			    					if($isEmpty){
			    						$raw3['created_at'] = date('Y-m-d H:i:s');
			    						$fulfilled->insertData('financial_events_report_raws', $raw3);
			    					}else{
				    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
				    						$raw3['created_at'] = date('Y-m-d H:i:s');
				    						$fulfilled->insertData('financial_events_report_raws', $raw3);
				    					}
				    				}
			    				}
		    				}
		    				if(isset($v['ItemFeeList'])){
                  // $v['ItemFeeList'] = array_filter($v['ItemFeeList']);
		    					foreach ($v['ItemFeeList'] as $val) {
			    					$raw3 = array();
			    					$raw3 = $raw2;
			    					$raw3['item_related_fee_type'] = $val['FeeType'];
			    					$raw3['item_related_fee_amount'] = $val['Amount'];

			    					if ($val['FeeType'] == 'Commission') {
				    					$Commission += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'FBAPerOrderFulfillmentFee') {
			    						$FBAPerOrderFulfillmentFee += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'FBAPerUnitFulfillmentFee') {
			    						$FBAPerUnitFulfillmentFee += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'FBAWeightBasedFee') {
			    						$FBAWeightBasedFee += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'FixedClosingFee') {
			    						$FixedClosingFee += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'GetPaidFasterFee') {
			    						$GetPaidFasterFee += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'GiftWrapChargeback') {
			    						$GiftWrapChargeback += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'GiftWrapCommission') {
			    						$GiftWrapCommission += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'SalesTaxCollectionFee') {
			    						$SalesTaxCollectionFee += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'ShippingChargeback') {
			    						$ShippingChargeback += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'ShippingHB') {
			    						$ShippingHB += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'VariableClosingFee') {
			    						$VariableClosingFee += $val['Amount'];
				    				}
			    					$raw3['currency'] = $val['CurrencyCode'];
			    					if($isEmpty){
			    						$raw3['created_at'] = date('Y-m-d H:i:s');
			    						$fulfilled->insertData('financial_events_report_raws', $raw3);
			    					}else{
				    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
				    						$raw3['created_at'] = date('Y-m-d H:i:s');
				    						$fulfilled->insertData('financial_events_report_raws', $raw3);
				    					}
				    				}
			    				}
		    				}

		    				if(isset($v['PromotionList'])){
		    					foreach ($v['PromotionList'] as $val) {
			    					$raw3 = array();
			    					$raw3 = $raw2;
			    					$raw3['promotion_type'] = $val['PromotionType'];
			    					$raw3['promotion_id'] = $val['PromotionId'];
			    					$raw3['promotional_rebates'] = $val['Amount'];
			    					$raw3['currency'] = $val['CurrencyCode'];
			    					if($raw3['promotional_rebates'] != 0){
				    					if($isEmpty){
				    						$raw3['created_at'] = date('Y-m-d H:i:s');
				    						$fulfilled->insertData('financial_events_report_raws', $raw3);
				    					}else{
					    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
					    						$raw3['created_at'] = date('Y-m-d H:i:s');
					    						$fulfilled->insertData('financial_events_report_raws', $raw3);
					    					}
					    				}
				    				}
			    				}
		    				}


				    		$where = [
				    			'order_id'=>$shipment['AmazonOrderId'],
				    			'marketplace_name'=>$shipment['MarketplaceName'],
				    			'seller_id' => $this->seller_id,
				    			'type' => 'Order',
				    			'sku' => $v['SellerSKU'],
				    			'order_item_code' => $v['OrderItemId'],
				    			'posted_date' => $posted_date,
				    		];
				    			$promotional_rebates = 0;
				    			$promotion_id = "";
				    			$promotion_type = "";
				    			$quantity = (!isset($v['QuantityShipped'])) ? 0 : $v['QuantityShipped'];
				    			if(isset($v['PromotionList'])){
				    				$promotion_id = $v['PromotionList'][0]['PromotionId'];
				    				$promotion_type = $v['PromotionList'][0]['PromotionType'];
				    				// for($i=0;$i<$quantity;$i++){
					    				foreach ($v['PromotionList'] as $value) {
					    					$promotional_rebates += (!isset($value['Amount'])) ? 0 : $value['Amount'];
					    				}
					    			// }
				    			}

				    			$item_related_fee = 0;
				    			for($i=0;$i<$quantity;$i++){
					    			if(isset($v['ItemFeeList'])){
                      // if(isset($shipment['ShipmentItemList'][0]['ItemFeeList'])){
                      //   $shipment['ShipmentItemList'][0]['ItemFeeList'] = array_filter($shipment['ShipmentItemList'][0]['ItemFeeList']);
                        foreach ($shipment['ShipmentItemList'][0]['ItemFeeList'] as $value) {
                          $item_related_fee += (!isset($value['Amount'])) ? 0 : $value['Amount'];
                        }
                      // }
					    			}
					    		}
				    			$order_fee = 0;
				    			$order_type = "";
				    			if(isset($shipment['OrderFeeList'])){
				    				$order_type = $shipment['OrderFeeList']['FeeType'];
				    				foreach ($shipment['OrderFeeList'] as $value) {
				    					$order_fee += (!isset($value['Amount'])) ? 0 : $value['Amount'];
				    				}
				    			}
				    			$direct_payment_amount = 0;
				    			$direct_payment_type = "";
				    			if(isset($shipment['DirectPaymentList'])){
				    				$order_type = $shipment['DirectPaymentList']['DirectPaymentType'];
				    				foreach ($shipment['DirectPaymentList'] as $value) {
				    					$direct_payment_amount += (!isset($value['Amount'])) ? 0 : $value['Amount'];
				    				}
				    			}

				    			$as="";
				    			$asin = $product->setConnection('mysql2')->where('sku',$v['SellerSKU'])
				    					->where('seller_id', $this->seller_id)
				    					->first();
				    			if($asin != "" OR $asin != null){
				    				$as = $asin->asin;
				    			}
				    			$data = [
					    			'order_id'=>$shipment['AmazonOrderId'],
					    			'marketplace_name'=>$shipment['MarketplaceName'],
					    			'seller_id' => $this->seller_id,
					    			'type' => 'Order',
					    			'sku' => $v['SellerSKU'],
					    			'asin' => $as,
					    			'order_item_code' => $v['OrderItemId'],
					    			'posted_date' => $posted_date,
					    			'quantity' => $quantity,
					    			'price_type' => (!isset($v['ItemChargeList'])) ? "" : $v['ItemChargeList'][0]['ChargeType'],
					    			'price_amount' => (!isset($v['ItemChargeList'])) ? 0 : $v['ItemChargeList'][0]['Amount'],
					    			'currency' => (!isset($v['ItemChargeList'])) ? "" : $v['ItemChargeList'][0]['CurrencyCode'],
					    			'tax_amount' => (!isset($v['ItemChargeList'])) ? 0 :  $v['ItemChargeList'][1]['Amount'],
					    			'shipment_fee_amount' => (!isset($v['ItemChargeList'])) ? 0 :  $v['ItemChargeList'][4]['Amount'],
					    			'other_amount' => (!isset($v['ItemChargeList'])) ? 0 : $v['ItemChargeList'][2]['Amount'] +
					    							  $v['ItemChargeList'][3]['Amount'] +
					    							  $v['ItemChargeList'][5]['Amount'],
					    			'promotional_rebates' => $promotional_rebates,
					    			'promotion_type' => $promotion_type,
					    			'promotion_id' => $promotion_id,
					    			'order_fee_amount' => $order_fee,
					    			'order_fee_type' => $order_type,
					    			'direct_payment_amount' => $direct_payment_amount,
					    			'direct_payment_type' => $direct_payment_type,
					    			'item_related_fee_amount' => $item_related_fee,
					    			'created_at' => date('Y-m-d H:i:s'),
					    			'pt_gift_wrap' => $GiftWrap,
					    			'pt_gift_wrap_tax' => $GiftWrapTax,
					    			'pt_shipping_tax' => $ShippingTax,
					    			'irft_commission' => $Commission,
					    			'irft_fba_per_order_fulfillmen_fee' => $FBAPerOrderFulfillmentFee,
									'irft_fba_per_unit_fulfillmen_fee' => $FBAPerUnitFulfillmentFee,
									'irft_fba_weight_based_fee' => $FBAWeightBasedFee,
									'irft_fixed_closing_fee' => $FixedClosingFee,
									'irft_get_paid_faster_fee' => $GetPaidFasterFee,
									'irft_gift_wrap_chargeback' => $GiftWrapChargeback,
									'irft_gift_wrap_commission' => $GiftWrapCommission,
									'irft_sales_tax_collection_fee' => $SalesTaxCollectionFee,
									'irft_shipping_charge_back' => $ShippingChargeback,
									'irft_shipping_hb' => $ShippingHB,
									'irft_variable_closing_fee' => $VariableClosingFee
					    		];
					    	if($isEmpty){
					    		$fulfilled->insertData('financial_events_reports',$data);
					    		$total_records++;
					    	}else{
					    		if(!$fulfilled->isExist('financial_events_reports',$where)){
					    			$fulfilled->insertData('financial_events_reports',$data);
					    			$total_records++;
					    		}
					    	}
				    	}
			    	}
			    }
          		gc_collect_cycles();
          		if($refunds!=false){
			    	foreach ($refunds as $refund) {
			    		$arr = explode('T', $refund['PostedDate']);
		    			$arr2  =explode('Z', $arr[1]);
		    			$posted_date = $arr[0]." ".$arr2[0];
		    			$currency = "";

		    			$raw = array();
		    			$raw['posted_date'] = $arr[0]." ".$arr2[0];
		    			$raw['order_id'] = $refund['AmazonOrderId'];
		    			$raw['marketplace_name'] = $refund['MarketplaceName'];
		    			$raw['seller_id'] = $this->seller_id;
		    			$raw['type'] = "Refund";

		    			foreach ($refund['ShipmentItemAdjustmentList'] as $value) {
			    			// price_type
							$ExportCharge = 0;
							$GenericDeduction = 0;
							$GiftWrap = 0;
							$GiftWrapTax = 0;
							$Goodwill = 0;
							$RestockingFee = 0;
							$ReturnShipping = 0;
							$ShippingTax = 0;
							// item_related_fee_type
			    			$Commission = 0;
							$GiftWrapChargeback = 0;
							$RefundCommission = 0;
							$SalesTaxCollectionFee = 0;
							$ShippingChargeback = 0;
							$ShippingHB = 0;
		    				$raw2 = array();
		    				$raw2 = $raw;
		    				$raw2['sku'] = (!isset($value['SellerSKU'])) ? 0 : $value['SellerSKU'];
		    				$raw2['order_item_code'] = (!isset($value['OrderItemId'])) ? 0 : $value['OrderItemId'];
		    				$raw2['quantity'] =  (!isset($value['QuantityShipped'])) ? 0 : $value['QuantityShipped'];
		    				$raw2['merchant_adjustment_item_id'] = (!isset($value['OrderAdjustmentItemId'])) ? 0 : $value['OrderAdjustmentItemId'];

		    				foreach ($value['ItemChargeAdjustmentList'] as $val) {
		    					$raw3 = array();
		    					$raw3 = $raw2;
		    					$raw3['price_type'] = $val['ChargeType'];
		    					$raw3['price_amount'] = $val['Amount'];
		    					$raw3['currency'] = $val['CurrencyCode'];

		    					if ($val['ChargeType'] == 'ExportCharge') {
			    					$ExportCharge += $val['Amount'];
		    					} elseif ($val['ChargeType'] == 'GenericDeduction') {
			    					$GenericDeduction += $val['Amount'];
		    					} elseif ($val['ChargeType'] == 'GiftWrap') {
			    					$GiftWrap += $val['Amount'];
			    				} elseif ($val['ChargeType'] == 'GiftWrapTax') {
		    						$GiftWrapTax += $val['Amount'];
			    				} elseif ($val['ChargeType'] == 'Goodwill') {
		    						$Goodwill += $val['Amount'];
			    				} elseif ($val['ChargeType'] == 'RestockingFee') {
		    						$RestockingFee += $val['Amount'];
			    				} elseif ($val['ChargeType'] == 'ReturnShipping') {
		    						$ReturnShipping += $val['Amount'];
			    				} elseif ($val['ChargeType'] == 'ShippingTax') {
		    						$ShippingTax += $val['Amount'];
			    				}
                  				if($isEmpty){
		    						$raw3['created_at'] = date('Y-m-d H:i:s');
			    					$fulfilled->insertData('financial_events_report_raws', $raw3);
		    					}else{
			    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
			    						$raw3['created_at'] = date('Y-m-d H:i:s');
			    						$fulfilled->insertData('financial_events_report_raws', $raw3);
			    					}
			    				}
		    				}

		    				if(isset($value['ItemFeeAdjustmentList'])){
		    					foreach ($value['ItemFeeAdjustmentList'] as $val) {
			    					$raw3 = array();
			    					$raw3 = $raw2;
			    					$raw3['item_related_fee_type'] = $val['FeeType'];
			    					$raw3['item_related_fee_amount'] = $val['Amount'];
			    					$raw3['currency'] = $val['CurrencyCode'];

			    					if ($val['FeeType'] == 'Commission') {
				    					$Commission += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'GiftWrapChargeback') {
			    						$GiftWrapChargeback += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'RefundCommission') {
			    						$RefundCommission += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'SalesTaxCollectionFee') {
			    						$SalesTaxCollectionFee += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'ShippingChargeback') {
			    						$ShippingChargeback += $val['Amount'];
				    				} elseif ($val['FeeType'] == 'ShippingHB') {
			    						$ShippingHB += $val['Amount'];
				    				}
			    					if($isEmpty){
			    						$raw3['created_at'] = date('Y-m-d H:i:s');
			    						$fulfilled->insertData('financial_events_report_raws', $raw3);
			    					}else{
				    					if(!$fulfilled->isExist('financial_events_report_raws', $raw3)){
				    						$raw3['created_at'] = date('Y-m-d H:i:s');
				    						$fulfilled->insertData('financial_events_report_raws', $raw3);
				    					}
				    				}
			    				}
		    				}

				    		$where = [
				    			'order_id'=>$refund['AmazonOrderId'],
				    			'marketplace_name'=>$refund['MarketplaceName'],
				    			'seller_id' => $this->seller_id,
				    			'type' => 'Refund',
				    			'sku' => (!isset($value['SellerSKU'])) ? "" : $value['SellerSKU'],
				    			'merchant_adjustment_item_id' => (!isset($value['OrderAdjustmentItemId'])) ? "" : $value['OrderAdjustmentItemId'],
				    			'quantity' => (!isset($value['QuantityShipped'])) ? "" : $value['QuantityShipped'],
				    			'posted_date' => $posted_date,
				    		];
				    			$item_related_fee_amount=0;
				    			if(isset($value['ItemFeeAdjustmentList'])){
					    			foreach ($value['ItemFeeAdjustmentList'] as $val) {
					    				$item_related_fee_amount += $val['Amount'];
					    			}
					    		}
				    			$tax_amount = 0;
				    			$price_amount = 0;
				    			$price_type = "";
				    			$shipment_fee_amount = 0;
				    			if(isset($value['ItemChargeAdjustmentList'])){
				    				foreach ($value['ItemChargeAdjustmentList'] as $v) {
				    					if($v['ChargeType'] == "Tax"){
				    						$tax_amount = $v['Amount'];
				    						$currency = $v['CurrencyCode'];
				    					}
				    					if($v['ChargeType'] == "Principal"){
				    						$price_amount = $v['Amount'];
				    						$price_type = $v['ChargeType'];
				    						$currency = $v['CurrencyCode'];
				    					}
				    					if($v['ChargeType'] == "ShippingCharge") {
				    						$shipment_fee_amount = $v['Amount'];
				    						$currency = $v['CurrencyCode'];
				    					}
				    				}
				    			}

				    			$as="";
                  $skuvar = (!isset($value['SellerSKU'])) ? "" : $value['SellerSKU'];
				    			$asin = $product->setConnection('mysql2')->where('sku',$skuvar)
				    					->where('seller_id', $this->seller_id)
				    					->first();
				    			if($asin != "" OR $asin != null){
				    				$as = $asin->asin;
				    			}
				    			$data = [
					    			'order_id'=>$refund['AmazonOrderId'],
					    			'marketplace_name'=>$refund['MarketplaceName'],
					    			'seller_id' => $this->seller_id,
					    			'type' => 'Refund',
					    			'sku' => (!isset($value['SellerSKU'])) ? "" : $value['SellerSKU'],
					    			'asin' => $as,
					    			'merchant_adjustment_item_id' => (!isset($value['OrderAdjustmentItemId'])) ? "" : $value['OrderAdjustmentItemId'],
					    			'quantity' => (!isset($value['QuantityShipped'])) ? "" : $value['QuantityShipped'],
					    			'posted_date' => $posted_date,
					    			'tax_amount' => $tax_amount,
					    			'price_type' => $price_type,
					    			'price_amount' => $price_amount,
					    			'shipment_fee_amount' => $shipment_fee_amount,
					    			'item_related_fee_amount'=>$item_related_fee_amount,
					    			'created_at'=>date('Y-m-d H:i:s'),
					    			'pt_export_charge' => $ExportCharge,
					    			'pt_generic_deduction' => $GenericDeduction,
					    			'pt_gift_wrap' => $GiftWrap,
					    			'pt_gift_wrap_tax' => $GiftWrapTax,
					    			'pt_goodwill' => $Goodwill,
					    			'pt_restocking_fee' => $RestockingFee,
					    			'pt_return_shipping' => $ReturnShipping,
					    			'pt_shipping_tax' => $ShippingTax,
					    			'irft_commission' => $Commission,
									'irft_gift_wrap_chargeback' => $GiftWrapChargeback,
									'irft_refund_commission' => $RefundCommission,
									'irft_sales_tax_collection_fee' => $SalesTaxCollectionFee,
									'irft_shipping_charge_back' => $ShippingChargeback,
									'irft_shipping_hb' => $ShippingHB
					    		];
                 			if($isEmpty){
  					    		$fulfilled->insertData('financial_events_reports',$data);
  					    		$total_records++;
  					    	}else{
  					    		if(!$fulfilled->isExist('financial_events_reports',$where)){
  						    		$fulfilled->insertData('financial_events_reports',$data);
  						    		$total_records++;
  					    		}
  					    	}
  				    	}
  			    	}
  			    }
		      	$dsub->addDay();
  	    	}
      	}

		$time_end = time();
    	$response['total_records'] = $total_records;
		$response['time_end'] = date('Y-m-d H:i:s');
		$response['isError'] = $isError;
		$response['time_start'] = date('Y-m-d H:i:s', $time_start);
		$response['total_time_of_execution'] = ($time_end - $time_start)/60;
		$response['tries'] = $tries;
		$response['message'] = $message;

		$log = new Log;
		$log->seller_id = $this->seller_id;
		$log->description = 'FinancialEventReport';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for FinancialEventReport';
		$log->api_used = $report_type;
		$log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
		$log->record_fetched = $total_records;
		$log->message = $message;
		$log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('FinancialEventReport for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('FinancialEventReport for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
    }
    public function getFinancialEvents($start_date, $end_date, $config){
		$amz_report = new AmazonFinancialEventList($config);
		$amz_report->setUseToken(true);
		$amz_report->setMaxResultsPerPage(100);
		$amz_report->setTimeLimits($start_date, $end_date);
		$amz_report->fetchEventList();
		$report = array();
		$report['Shipment'] = $amz_report->getShipmentEvents();
		$report['Refund'] = $amz_report->getRefundEvents();
		$report['Adjustment'] = $amz_report->getAdjustmentEvents();
		$report['GuaranteeClaim'] = $amz_report->getGuaranteeClaimEvents();
		$report['Chargeback'] = $amz_report->getChargebackEvents();
		$report['PayWithAmazon'] = $amz_report->getPayWithAmazonEvents();
		$report['ServiceProviderCredit'] = $amz_report->getServiceProviderCreditEvents();
		$report['Retrocharge'] = $amz_report->getRetrochargeEvents();
		$report['RentalTransaction'] = $amz_report->getRentalTransactionEvents();
		$report['PerformanceBondRefund'] = $amz_report->getPerformanceBondRefundEvents();
		$report['ServiceFee'] = $amz_report->getServiceFeeEvents();
		$report['DebtRecovery'] = $amz_report->getDebtRecoveryEvents();
		$report['LoanServicing'] = $amz_report->getLoanServicingEvents();
		$report['SAFETReimbursement'] = $amz_report->getSAFETReimbursementEvents();

		return $report;
	}
	private function setAmazonConfig($country_key, $creds){
		if($country_key == 'eu') $urlpref = '.co.uk';
		else $urlpref = '.com';

		$amz_conf = array(
          'stores' =>
              array('YourAmazonStore' =>
                  array(
                      'merchantId'    => $creds['merchantId'], //mkp_seller_id
                      'MWSAuthToken'  => $creds['MWSAuthToken'],   //mkp_auth_token
                      'marketplaceId' => null,
                      'keyId'         => config('constant.amz_keys.'.$country_key.'.access_key'),
                      'secretKey'     => config('constant.amz_keys.'.$country_key.'.secret_key'),
                      'serviceUrl'    => '',
                  )
              ),
          'AMAZON_SERVICE_URL'        => 'https://mws.amazonservices'.$urlpref, // eu store
          'logpath'                   => __DIR__ . './logs/amazon_mws.log',
          'logfunction'               => '',
          'muteLog'                   => false
        );
        $configObject = new \AmazonMWSConfig($amz_conf);
        return $configObject;
	}


  private function _insert_Retrocharge(UniversalModel $fulfilled, $Retrocharge, $sellerid){
    $return_id = array();
    if(!is_array($Retrocharge)){
      return false;
    }

    if(isset($Retrocharge[0])){
      $dummyarr=$Retrocharge[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_retrocharges');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_retrocharges',$checkv);
    }

    foreach($Retrocharge as $a){
      $PostedDate = (!empty($a['PostedDate']) ? $this->_format_date_to_yyyymmddhhiiss($a['PostedDate']) : '');

      $data = array(
        'retrochargeeventtype'=>(isset($a['RetrochargeEventType']) ? $a['RetrochargeEventType'] : ''),
        'amazonorderid'=>(isset($a['AmazonOrderId']) ? $a['AmazonOrderId'] : ''),
        'posteddate'=>$PostedDate,
        'basetax_amount'=>(isset($a['BaseTax']['Amount']) ? $a['BaseTax']['Amount'] : ''),
        'basetax_currencycode'=>(isset($a['BaseTax']['CurrencyCode']) ? $a['BaseTax']['CurrencyCode'] : ''),
        'shippingtax_amount'=>(isset($a['ShippingTax']['Amount']) ? $a['ShippingTax']['Amount'] : ''),
        'shippingtax_currencycode'=>(isset($a['ShippingTax']['CurrencyCode']) ? $a['ShippingTax']['CurrencyCode'] : ''),
        'marketplacename'=>(isset($a['MarketplaceName']) ? $a['MarketplaceName'] : ''),
      );

      if(count(array_filter($data))>0){
        $data['seller_id'] = $sellerid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_retrocharges',$data);
        	array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_retrocharges',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_retrocharges',$data);
	          array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }


  private function _insert_RentalTransaction(UniversalModel $fulfilled, $PerformanceBondRefund, $sellerid){
    $return_id = array();
    if(!is_array($PerformanceBondRefund)){
      return false;
    }

    if(isset($PerformanceBondRefund[0])){
      $dummyarr=$PerformanceBondRefund[0];
      unset($dummyarr['RentalChargeList']);
      unset($dummyarr['RentalFeeList']);
      $checkv=$this->formatcolumn($dummyarr,'financial_event_rental_transactions');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_rental_transactions',$checkv);
    }


    foreach($PerformanceBondRefund as $a){
      $PostedDate = (!empty($a['PostedDate']) ? $this->_format_date_to_yyyymmddhhiiss($a['PostedDate']) : '');

      $data = array(
        'amazonorderid'=>(isset($a['AmazonOrderId']) ? $a['AmazonOrderId'] : ''),
        'rentaleventtype'=>(isset($a['RentalEventType']) ? $a['RentalEventType'] : ''),
        'extensionlength'=>(isset($a['ExtensionLength']) ? $a['ExtensionLength'] : ''),
        'posteddate'=>$PostedDate,
        'marketplacename'=>(isset($a['MarketplaceName']) ? $a['MarketplaceName'] : ''),
        'rentalinitialvalue_currencycode'=>(isset($a['RentalInitialValue']['CurrencyCode']) ? $a['RentalInitialValue']['CurrencyCode'] : ''),
        'rentalinitialvalue_amount'=>(isset($a['RentalInitialValue']['Amount']) ? $a['RentalInitialValue']['Amount'] : ''),
        'rentalreimbursement_currencycode'=>(isset($a['RentalReimbursement']['CurrencyCode']) ? $a['RentalReimbursement']['CurrencyCode'] : ''),
        'rentalreimbursement_amount'=>(isset($a['RentalReimbursement']['Amount']) ? $a['RentalReimbursement']['Amount'] : ''),
      );

      if(count(array_filter($data))>0){
        $data['seller_id'] = $sellerid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_rental_transactions',$data);
          if(isset($a['RentalChargeList'])){
            $this->_insert_RentalTransactionRentalChargeList($fulfilled,$id,$a['RentalChargeList']);
          }
          if(isset($a['RentalFeeList'])){
            $this->_insert_RentalTransactionRentalFeeList($fulfilled,$id,$a['RentalFeeList']);
          }
    			array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_rental_transactions',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_rental_transactions',$data);
            if(isset($a['RentalChargeList'])){
              $this->_insert_RentalTransactionRentalChargeList($fulfilled,$id,$a['RentalChargeList']);
            }
            if(isset($a['RentalFeeList'])){
              $this->_insert_RentalTransactionRentalFeeList($fulfilled,$id,$a['RentalFeeList']);
            }
            array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }

  private function _insert_RentalTransactionRentalChargeList(UniversalModel $fulfilled, $fkid,$RentalChargeList){
    $return_id = array();
    if(!is_array($RentalChargeList)){
      return false;
    }

    if(isset($RentalChargeList[0])){
      $dummyarr=$RentalChargeList[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_rental_transaction_rental_charge_lists');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_rental_transaction_rental_charge_lists',$checkv);
    }


    foreach($RentalChargeList as $b){
      $data = array(
        'chargetype'=>(isset($b['ChargeType']) ? $b['ChargeType'] : ''),
        'currencycode'=>(isset($b['CurrencyCode']) ? $b['CurrencyCode'] : ''),
        'amount'=>(isset($b['Amount']) ? $b['Amount'] : ''),
      );

      if(count(array_filter($data))>0){
        $data['financial_event_rental_transactions_id'] = $fkid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_rental_transaction_rental_charge_lists',$data);
          array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_rental_transaction_rental_charge_lists',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_rental_transaction_rental_charge_lists',$data);
	          array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }

  private function _insert_RentalTransactionRentalFeeList(UniversalModel $fulfilled, $fkid,$RentalFeeList){
    $return_id = array();
    if(!is_array($RentalFeeList)){
      return false;
    }

    if(isset($RentalFeeList[0])){
      $dummyarr=$RentalFeeList[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_rental_transaction_rental_fee_lists');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_rental_transaction_rental_fee_lists',$checkv);
    }

    foreach($RentalFeeList as $c){

      $data = array(
        'feetype'=>(isset($c['FeeType']) ? $c['FeeType'] : ''),
        'currencycode'=>(isset($c['CurrencyCode']) ? $c['CurrencyCode'] : ''),
        'amount'=>(isset($c['Amount']) ? $c['Amount'] : ''),
      );

      if(count(array_filter($data))>0){
        $data['financial_event_rental_transactions_id'] = $fkid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_rental_transaction_rental_fee_lists',$data);
	        array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_rental_transaction_rental_fee_lists',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_rental_transaction_rental_fee_lists',$data);
	          array_push($return_id,$id);
	        }
	    }
      }

    }
    return $return_id;
  }


  private function _insert_PerformanceBondRefund(UniversalModel $fulfilled, $PerformanceBondRefund, $sellerid){
    $return_id = array();
    if(!is_array($PerformanceBondRefund)){
      return false;
    }

    if(isset($PerformanceBondRefund[0])){
      $dummyarr=$PerformanceBondRefund[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_performance_bond_refunds');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_performance_bond_refunds',$checkv);
    }

    foreach($PerformanceBondRefund as $a){
      $data = array(
        // 'seller_id'=>$sellerid,
        'marketplacecountrycode'=>(isset($a['MarketplaceCountryCode']) ? $a['MarketplaceCountryCode'] : ''),
        'currencycode'=>(isset($a['CurrencyCode']) ? $a['CurrencyCode'] : ''),
        'amount'=>(isset($a['Amount']) ? $a['Amount'] : ''),
        'productgrouplist'=>(isset($a['ProductGroupList']) ? $a['ProductGroupList'] : ''),
        'posted_date'=>$this->main_posted_date,
      );
      if(count(array_filter($data))>0){
        $data['seller_id'] = $sellerid;
        // $cond=array('');
        if($this->check_empty){
        	 $id = $fulfilled->insertData_return_id('financial_event_performance_bond_refunds',$data);
           array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_performance_bond_refunds',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_performance_bond_refunds',$data);
	          array_push($return_id,$id);
	        }
	      }
      }
    }
    return $return_id;
  }



  private function _insert_ServiceFee(UniversalModel $fulfilled, $ServiceFee, $sellerid){
    $return_id = array();
    if(!is_array($ServiceFee)){
      return false;
    }

    if(isset($ServiceFee[0])){
      $dummyarr=$ServiceFee[0];
      // $dummyarr['newcol']='sampleval';
      unset($dummyarr['FeeList']);
      $checkv=$this->formatcolumn($dummyarr,'financial_event_service_fees');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_service_fees',$checkv);
    }


    foreach($ServiceFee as $a){
      $data = array(
        // 'seller_id'=>$sellerid,
        'amazonorderid'=>(isset($a['AmazonOrderId']) ? $a['AmazonOrderId'] : ''),
        'feereason'=>(isset($a['FeeReason']) ? $a['FeeReason'] : ''),
        'sellersku'=>(isset($a['SellerSKU']) ? $a['SellerSKU'] : ''),
        'fnsku'=>(isset($a['FnSKU']) ? $a['FnSKU'] : ''),
        'feedescription'=>(isset($a['FeeDescription']) ? $a['FeeDescription'] : ''),
        'asin'=>(isset($a['ASIN']) ? $a['ASIN'] : ''),
        'posted_date'=>$this->main_posted_date,
      );
      if(count(array_filter($data))>0){
        $data['seller_id'] = $sellerid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_service_fees',$data);
          if(isset($a['FeeList'])){
            $this->_insert_ServiceFeeFeeList($fulfilled,$id,$a['FeeList']);
          }
          array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_service_fees',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_service_fees',$data);
            if(isset($a['FeeList'])){
  	          $this->_insert_ServiceFeeFeeList($fulfilled,$id,$a['FeeList']);
            }
	          array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }

  private function _insert_ServiceFeeFeeList(UniversalModel $fulfilled, $fkid, $ServiceFeeFeeList){
    $return_id = array();
    if(!is_array($ServiceFeeFeeList)){
      return false;
    }

    if(isset($ServiceFeeFeeList[0])){
      $dummyarr=$ServiceFeeFeeList[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_service_fee_fee_lists');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_service_fee_fee_lists',$checkv);

    }
    foreach($ServiceFeeFeeList as $b){

      $data = array(
        // 'financial_event_service_fees_id'=>$fkid,
        'feetype'=>(isset($b['FeeType']) ? $b['FeeType'] : ''),
        'currencycode'=>(isset($b['CurrencyCode']) ? $b['CurrencyCode'] : ''),
        'amount'=>(isset($b['Amount']) ? $b['Amount'] : ''),
      );

      if(count(array_filter($data))>0){
        $data['financial_event_service_fees_id'] = $fkid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_service_fee_fee_lists',$data);
          array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_service_fee_fee_lists',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_service_fee_fee_lists',$data);
	          array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }




  private function _insert_DebtRecovery(UniversalModel $fulfilled, $DebtRecovery, $sellerid){
    $return_id = array();
    if(!is_array($DebtRecovery)){
      return false;
    }

    if(isset($DebtRecovery[0])){
      $dummyarr=$DebtRecovery[0];
      unset($dummyarr['DebtRecoveryItemList']);
      unset($dummyarr['ChargeInstrumentList']);
      $checkv=$this->formatcolumn($dummyarr,'financial_event_debt_recoveries');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_debt_recoveries',$checkv);
    }

    foreach($DebtRecovery as $a){
      $data = array(
        // 'seller_id'=>$sellerid,
        'debtrecoverytype'=>(isset($a['DebtRecoveryType']) ? $a['DebtRecoveryType'] : ''),
        'currencycode'=>(isset($a['RecoveryAmount']['CurrencyCode']) ? $a['RecoveryAmount']['CurrencyCode'] : ''),
        'amount'=>(isset($a['RecoveryAmount']['Amount']) ? $a['RecoveryAmount']['Amount'] : ''),
        'overpaymentcredit_currencycode'=>(isset($a['OverPaymentCredit']['CurrencyCode']) ? $a['OverPaymentCredit']['CurrencyCode'] : ''),
        'overpaymentcredit_amount'=>(isset($a['OverPaymentCredit']['Amount']) ? $a['OverPaymentCredit']['Amount'] : ''),
        'posted_date'=>$this->main_posted_date,
      );

      if(count(array_filter($data))>0){
        $data['seller_id'] = $sellerid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_debt_recoveries',$data);
          if(isset($a['DebtRecoveryItemList'])){
            $this->_insert_DebtRecoveryItemList($fulfilled,$id,$a['DebtRecoveryItemList']);
          }
          if(isset($a['ChargeInstrumentList'])){
            $this->_insert_DebtRecoveryChargeInstrumentList($fulfilled,$id,$a['ChargeInstrumentList']);
          }
          array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_debt_recoveries',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_debt_recoveries',$data);
            if(isset($a['DebtRecoveryItemList'])){
              $this->_insert_DebtRecoveryItemList($fulfilled,$id,$a['DebtRecoveryItemList']);
            }
            if(isset($a['ChargeInstrumentList'])){
              $this->_insert_DebtRecoveryChargeInstrumentList($fulfilled,$id,$a['ChargeInstrumentList']);
            }
	          array_push($return_id,$id);
	        }
	    }
      }
    }

    return $return_id;
  }

  private function _insert_DebtRecoveryItemList(UniversalModel $fulfilled, $fkid,$DebtRecoveryItemList){
    $return_id = array();
    if(!is_array($DebtRecoveryItemList)){
      return false;
    }

    if(isset($DebtRecoveryItemList[0])){
      $dummyarr=$DebtRecoveryItemList[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_debt_recovery_item_lists');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_debt_recovery_item_lists',$checkv);
    }

    foreach($DebtRecoveryItemList as $b){
      $GroupBeginDate = (!empty($b['GroupBeginDate']) ? $this->_format_date_to_yyyymmddhhiiss($b['GroupBeginDate']) : '');
      $GroupEndDate = (!empty($b['GroupEndDate']) ? $this->_format_date_to_yyyymmddhhiiss($b['GroupEndDate']) : '');

      $data = array(
        // 'financial_event_debt_recovery_id'=>$fkid,
        'recoverycurrencycode'=>(isset($b['RecoveryAmount']['CurrencyCode']) ? $b['RecoveryAmount']['CurrencyCode'] : ''),
        'recoveryamount'=>(isset($b['RecoveryAmount']['Amount']) ? $b['RecoveryAmount']['Amount'] : ''),
        'originalcurrencycode'=>(isset($b['OriginalAmount']['CurrencyCode']) ? $b['OriginalAmount']['CurrencyCode'] : ''),
        'originalamount'=>(isset($b['OriginalAmount']['Amount']) ? $b['OriginalAmount']['Amount'] : ''),
        'groupbegindate'=>$GroupBeginDate,
        'groupenddate'=>$GroupEndDate,
      );

      if(count(array_filter($data))>0){
        $data['financial_event_debt_recovery_id'] = $fkid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_debt_recovery_item_lists',$data);
          array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_debt_recovery_item_lists',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_debt_recovery_item_lists',$data);
	          array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }

  private function _insert_DebtRecoveryChargeInstrumentList(UniversalModel $fulfilled, $fkid,$ChargeInstrumentList){
    $return_id = array();
    if(!is_array($ChargeInstrumentList)){
      return false;
    }

    if(isset($ChargeInstrumentList[0])){
      $dummyarr=$ChargeInstrumentList[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_debt_recovery_charge_instrument_lists');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_debt_recovery_charge_instrument_lists',$checkv);
    }

    foreach($ChargeInstrumentList as $c){
      $data = array(
        // 'financial_event_debt_recovery_id'=>$fkid,
        'description'=>(isset($c['Description']) ? $c['Description'] : ''),
        'tail'=>(isset($c['Tail']) ? $c['Tail'] : ''),
        'currencycode'=>(isset($c['Amount']) ? $c['Amount'] : ''),
        'amount'=>(isset($c['CurrencyCode']) ? $c['CurrencyCode'] : ''),
      );

      if(count(array_filter($data))>0){
        $data['financial_event_debt_recovery_id'] = $fkid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_debt_recovery_charge_instrument_lists',$data);
          array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_debt_recovery_charge_instrument_lists',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_debt_recovery_charge_instrument_lists',$data);
	          array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }


  private function _insert_LoanServicing(UniversalModel $fulfilled, $LoanServicing, $sellerid){
    $return_id = array();
    if(!is_array($LoanServicing)){
      return false;
    }

    if(isset($LoanServicing[0])){
      $dummyarr=$LoanServicing[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_loan_servicings');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_loan_servicings',$checkv);
    }

    foreach($LoanServicing as $a){
      $data = array(
        // 'seller_id'=>$sellerid,
        'currency' =>(isset($a['CurrencyCode']) ? $a['CurrencyCode'] : ''),
        'amount' =>(isset($a['Amount']) ? $a['Amount'] : ''),
        'sourcebusinesseventtype' =>(isset($a['SourceBusinessEventType']) ? $a['SourceBusinessEventType'] : ''),
        'posted_date'=>$this->main_posted_date,
      );
      if(count(array_filter($data))>0){
        $data['seller_id'] = $sellerid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_loan_servicings',$data);
          array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_loan_servicings',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_loan_servicings',$data);
	          array_push($return_id,$id);
	        }
	      }
      }
    }
    return $return_id;
  }

  private function _insert_SAFETReimbursement(UniversalModel $fulfilled, $SAFETReimbursement, $sellerid){
    $return_id = array();
    if(!is_array($SAFETReimbursement)){
      return false;
    }

    if(isset($SAFETReimbursement[0])){
      $dummyarr=$SAFETReimbursement[0];
      unset($SAFETReimbursement['SAFETReimbursementItemList']);
      $checkv=$this->formatcolumn($dummyarr,'financial_event_s_a_f_e_t_reimbursements');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_s_a_f_e_t_reimbursements',$checkv);
    }

    foreach($SAFETReimbursement as $a){
      $data = array(
        // 'seller_id'=>$sellerid,
        'posteddate' =>(isset($a['PostedDate']) ? $a['PostedDate'] : ''),
        'safetclaimid' =>(isset($a['SAFETClaimId']) ? $a['SAFETClaimId'] : ''),
        'reimbursedamount_amount' =>(isset($a['ReimbursedAmount']['Amount']) ? $a['ReimbursedAmount']['Amount'] : ''),
        'reimbursedamount_currencycode' =>(isset($a['ReimbursedAmount']['CurrencyCode']) ? $a['ReimbursedAmount']['CurrencyCode'] : ''),
      );
      if(count(array_filter($data))>0){
        $data['seller_id'] = $sellerid;
        // $cond=array('');
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_s_a_f_e_t_reimbursements',$data);
          if(isset($a['SAFETReimbursementItemList'])){
            $this->_insert_SAFETReimbursementItemList($fulfilled,$id,$a['SAFETReimbursementItemList']);
          }
          array_push($return_id,$id);
        }else{
        	if(!$fulfilled->isExist('financial_event_s_a_f_e_t_reimbursements',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_s_a_f_e_t_reimbursements',$data);
            if(isset($a['SAFETReimbursementItemList'])){
              $this->_insert_SAFETReimbursementItemList($fulfilled,$id,$a['SAFETReimbursementItemList']);
            }
            array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }

  private function _insert_SAFETReimbursementItemList(UniversalModel $fulfilled, $fkid,$SAFETReimbursementItemList){
    $return_id = array();
    if(!is_array($SAFETReimbursementItemList)){
      return false;
    }

    if(isset($SAFETReimbursementItemList[0])){
      $dummyarr=$SAFETReimbursementItemList[0];
      $checkv=$this->formatcolumn($dummyarr,'financial_event_s_a_f_e_t_reimbursement_item_lists');
      $amz = new MWSFetchReportClass();
      $amz->checkForNewColumn('financial_event_s_a_f_e_t_reimbursement_item_lists',$checkv);
    }

    foreach($SAFETReimbursementItemList as $b){
      $data = array(
        'chargetype'=>(isset($b['ChargeType']) ? $b['ChargeType'] : ''),
        'currencycode'=>(isset($b['CurrencyCode']) ? $b['CurrencyCode'] : ''),
        'amount'=>(isset($b['Amount']) ? $b['Amount'] : ''),
      );

      if(count(array_filter($data))>0){
        $data['financial_event_s_a_f_e_t_reimbursements_id'] = $fkid;
        if($this->check_empty){
        	$id = $fulfilled->insertData_return_id('financial_event_s_a_f_e_t_reimbursement_item_lists',$data);
          array_push($return_id,$id);
        }else{
	        if(!$fulfilled->isExist('financial_event_s_a_f_e_t_reimbursement_item_lists',$data)){
	          $id = $fulfilled->insertData_return_id('financial_event_s_a_f_e_t_reimbursement_item_lists',$data);
	          array_push($return_id,$id);
	        }
	    }
      }
    }
    return $return_id;
  }

  private function _format_date_to_yyyymmddhhiiss($date){
    $newd = date('Y-m-d H:i:s',strtotime($date));
    //returns 1970-01-01 00:00:00 if invalid date
    return $newd;
  }

  public function format_multidimensionarray($arr){
    $r=array();
    foreach($arr as $k => $v){
        $newcol=strtolower($k);
        if (is_array($v) || is_object($v)){
          $subarr=$this->format_multidimensionarray($v);
          foreach($subarr as $subarrk => $subarrv){
            $r[$newcol.'_'.$subarrk]=$subarrv;
          }
        }else{
          $r[$newcol]=$v;
        }
    }
    return $r;
  }

  public function formatcolumn($arr,$table=''){
    switch($table){
        case 'financial_event_retrocharges':
          unset($arr['RetrochargeEventType']);
          unset($arr['AmazonOrderId']);
          unset($arr['PostedDate']);
          unset($arr['BaseTax']['Amount']);
          unset($arr['BaseTax']['CurrencyCode']);
          unset($arr['ShippingTax']['Amount']);
          unset($arr['ShippingTax']['CurrencyCode']);
          unset($arr['MarketplaceName']);
        break;

        case 'financial_event_rental_transactions':
          unset($arr['AmazonOrderId']);
          unset($arr['RentalEventType']);
          unset($arr['ExtensionLength']);
          unset($arr['PostedDate']);
          unset($arr['MarketplaceName']);
          unset($arr['RentalInitialValue']['CurrencyCode']);
          unset($arr['RentalInitialValue']['Amount']);
          unset($arr['RentalReimbursement']['CurrencyCode']);
          unset($arr['RentalReimbursement']['Amount']);
        break;

        case 'financial_event_rental_transaction_rental_charge_lists':
          unset($arr['ChargeType']);
          unset($arr['CurrencyCode']);
          unset($arr['Amount']);
        break;

        case 'financial_event_rental_transaction_rental_fee_lists':
          unset($arr['FeeType']);
          unset($arr['CurrencyCode']);
          unset($arr['Amount']);
        break;

        case 'financial_event_performance_bond_refunds':
          unset($arr['MarketplaceCountryCode']);
          unset($arr['CurrencyCode']);
          unset($arr['Amount']);
          unset($arr['ProductGroupList']);
        break;

        case 'financial_event_service_fees':
          unset($arr['AmazonOrderId']);
          unset($arr['FeeReason']);
          unset($arr['SellerSKU']);
          unset($arr['FnSKU']);
          unset($arr['FeeDescription']);
          unset($arr['ASIN']);
        break;

        case 'financial_event_service_fee_fee_lists':
          unset($arr['FeeType']);
          unset($arr['CurrencyCode']);
          unset($arr['Amount']);
        break;

        case 'financial_event_debt_recoveries':
          unset($arr['DebtRecoveryType']);
          unset($arr['RecoveryAmount']['CurrencyCode']);
          unset($arr['RecoveryAmount']['Amount']);
          unset($arr['OverPaymentCredit']['CurrencyCode']);
          unset($arr['OverPaymentCredit']['Amount']);
        break;

        case 'financial_event_debt_recovery_item_lists':
          unset($arr['GroupBeginDate']);
          unset($arr['GroupEndDate']);
          unset($arr['RecoveryAmount']['CurrencyCode']);
          unset($arr['RecoveryAmount']['Amount']);
          unset($arr['OriginalAmount']['CurrencyCode']);
          unset($arr['OriginalAmount']['Amount']);
        break;

        case 'financial_event_debt_recovery_charge_instrument_lists':
          unset($arr['Description']);
          unset($arr['Tail']);
          unset($arr['Amount']);
          unset($arr['CurrencyCode']);
        break;

        case 'financial_event_loan_servicings':
          unset($arr['CurrencyCode']);
          unset($arr['Amount']);
          unset($arr['SourceBusinessEventType']);
        break;

        case 'financial_event_s_a_f_e_t_reimbursements':
          unset($arr['PostedDate']);
          unset($arr['SAFETClaimId']);
          unset($arr['ReimbursedAmount']['Amount']);
          unset($arr['ReimbursedAmount']['CurrencyCode']);
        break;

        case 'financial_event_s_a_f_e_t_reimbursement_item_lists':
          unset($arr['ChargeType']);
          unset($arr['CurrencyCode']);
          unset($arr['Amount']);
        break;
    }

    $arr = $this->format_multidimensionarray($arr);
    return $arr;
  }
}
