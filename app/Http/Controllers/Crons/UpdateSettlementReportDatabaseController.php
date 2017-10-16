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
use Carbon\Carbon;
use App\Seller;

use Mail;


class UpdateSettlementReportDatabaseController extends Controller
{
    //
    //
    private $seller_id;
    private $mkp='';

    public function index(){
      try{
    	$total_records = 0;
    	$fulfilled = new SettlementReport();
    	$product = new Product();
    	$univ = new UniversalModel();

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

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('SettlementReport', true));

		//response for mail
		$time_start = time();
		$isError = false;
		$response['time_start'] = date('Y-m-d H:i:s');
		$response['total_time_of_execution'] = 0;
		$response['message'] = "SettlementReport Cron Successfully Fetch Data!";
		$response['isError'] = false;
		$response['tries'] = 0;
		$tries=0;
		$message = "SettlementReport Cron Successfully Fetch Data!";

    	$report_type = '_GET_V2_SETTLEMENT_REPORT_DATA_FLAT_FILE_';
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
        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('SettlementReport for seller'.$this->seller_id.' mkp'.$this->mkp, true));
        $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());


		$seller_details = array();


		$ff_data = array();
		if(count($mkp_assign)>0) $ff_data_count = $univ->getRecords('settlemet_reports',array('*'),array('seller_id'=> $this->seller_id),array(),true);
		$start_date = '-1 month';
		$end_date = null;
		$m_ctr = "-1 month";
		if(count($ff_data)>0  AND !$isEmpty) $isEmpty = false;
		else{
			$isEmpty = true;
			//$start_date = '-6 months';
			$start_date = "-18 months";
		}

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
		ini_set('memory_limit', '512');
		ini_set("zlib.output_compression", 0);  // off
		ini_set("implicit_flush", 1);  // on
		ini_set("max_execution_time", 0);  // on
		foreach ($mkp_assign as $value) {
			$c_key='';
			if($value->marketplace_id == 1){
				$mkp = config('constant.amz_keys.na.marketplaces');
				$c_key = 'na';
			}
			if($value->marketplace_id == 2){
				$mkp = config('constant.amz_keys.eu.marketplaces');
				$c_key = 'eu';
			}
			$merchantId = $value->mws_seller_id;
			$MWSAuthToken = $value->mws_auth_token;

		    $tries++;
	    	$country = "All ".$c_key;
	    	$init = array(
				'merchantId'    => $merchantId,
	            'MWSAuthToken'  => $MWSAuthToken,		//mkp_auth_token
	            'country'		=> $country,			//mkp_country
	            'marketPlace'	=> '',		//seller marketplace id
	    		'start_date'	=> $start_date,
	    		'end_date'		=> null,
	    		'name'			=> 'SettlementReport'
	    		);
	    	$amz = new MWSFetchReportClass();
	    	$amz->initialize($init);
	    	$id_list = $amz->getReportIDList($report_type, $c_key);
	    	foreach ($id_list as $id) {
	    		$report_id = $id['ReportId'];
	    		echo "Report ID: ".$report_id."<br>";
	    		echo "Fetching data....";
	    		ob_flush();
				flush();

	    		$data = $amz->fetchReportByID($report_id, $c_key);
	    		echo "Done!<br>";
	    		echo "Saving Data to Database...";
	    		ob_flush();
				flush();
				$first = true;
				$currency = '';
	    		foreach ($data['data'] as $value) {
	    			if($first){
	    				$first = false;
	    				$currency = $value['currency'];
	    			}
		    		$item = array();
		    		$item['settlement_id'] = $value['settlement_id'];
		    		$item['order_id'] = $value['order_id'];
		    		$item['sku'] = $value['sku'];
		    		$item['type'] = $value['transaction_type'];
		    		$item['order_item_code'] = $value['order_item_code'];
		    		$item['price_amount'] = $value['price_amount'];
		    		$item['price_type'] = $value['price_type'];
		    		$item['item_related_fee_type'] = $value['item_related_fee_type'];
		    		$item['item_related_fee_amount'] = $value['item_related_fee_amount'];
		    		$item['adjustment_id'] = $value['adjustment_id'];
		    		$item['shipment_id'] = $value['shipment_id'];
		    		$item['promotion_id'] = $value['promotion_id'];
		    		$item['order_item_code'] = $value['order_item_code'];
		    		$item['seller_id'] = $this->seller_id;
		    		if(!$fulfilled->isExist($item)){
		    			$total_records++;
		    			$pd = explode('T', $value['posted_date']);
						if(count($pd) == 2){
							$pdf = $pd[0];
							$pd = explode('+', $pd[1]);
							$item['posted_date'] = $pdf." ".$pd[0];
						}else{
							$item['posted_date'] = null;
						}

						if($value['quantity_purchased'] == '' || $value['quantity_purchased'] == ' ' || $value['quantity_purchased'] == null)
							$value['quantity_purchased'] = 0;
		    			$item['quantity'] = (int)$value['quantity_purchased'];

		    			if($value['total_amount'] == '' || $value['total_amount'] == ' ' || $value['total_amount'] == null)
							$value['total_amount'] = 0;
		    			$item['total'] = (float)$value['total_amount'];

		    			if($value['price_amount'] == '' || $value['price_amount'] == ' ' || $value['price_amount'] == null)
							$value['price_amount'] = 0;
		    			$item['price_amount'] = (float)$value['price_amount'];

		    			if(!isset($value['promotion_amount']) || $value['promotion_amount'] == '' || $value['promotion_amount'] == ' ' || $value['promotion_amount'] == null)
							$value['promotion_amount'] = 0;
		    			$item['promotional_rebates'] = (float)$value['promotion_amount'];

		    			if($value['other_amount'] == '' || $value['other_amount'] == ' ' || $value['other_amount'] == null)
							$value['other_amount'] = 0;
		    			$item['other_amount'] = (float)$value['other_amount'];

		    			if($value['direct_payment_amount'] == '' || $value['direct_payment_amount'] == ' ' || $value['direct_payment_amount'] == null)
							$value['direct_payment_amount'] = 0;
		    			$item['direct_payment_amount'] = (float)$value['direct_payment_amount'];

		    			if($value['shipment_fee_amount'] == '' || $value['shipment_fee_amount'] == ' ' || $value['shipment_fee_amount'] == null)
							$value['shipment_fee_amount'] = 0;
		    			$item['shipment_fee_amount'] = (float)$value['shipment_fee_amount'];

		    			if($value['order_fee_amount'] == '' || $value['order_fee_amount'] == ' ' || $value['order_fee_amount'] == null)
							$value['order_fee_amount'] = 0;
		    			$item['order_fee_amount'] = (float)$value['order_fee_amount'];

		    			if($value['item_related_fee_amount'] == '' || $value['item_related_fee_amount'] == ' ' || $value['item_related_fee_amount'] == null)
							$value['item_related_fee_amount'] = 0;
		    			$item['item_related_fee_amount'] = (float)$value['item_related_fee_amount'];

		    			$item['type'] = $value['transaction_type'];
		    			$item['currency'] = $currency;
		    			$item['adjustment_id'] = $value['adjustment_id'];
		    			$item['shipment_id'] = $value['shipment_id'];

		    			$item['shipment_fee_type'] = $value['shipment_fee_type'];
		    			$item['order_fee_type'] = $value['order_fee_type'];
		    			$item['order_item_code'] = $value['order_item_code'];
		    			$item['merchant_order_item_id'] = $value['merchant_order_item_id'];
		    			$item['merchant_adjustment_item_id'] = $value['merchant_adjustment_item_id'];
		    			$item['direct_payment_type'] = $value['direct_payment_type'];

		    			$item['marketplace_name'] = $value['marketplace_name'];
		    			$item['fulfillment_id'] = $value['fulfillment_id'];
		    			$item['price_type'] = $value['price_type'];
		    			$item['promotion_id'] = $value['promotion_id'];
		    			$item['promotion_type'] = $value['promotion_type'];
		    			$item['item_related_fee_type'] = $value['item_related_fee_type'];

		    			$asin = $product->setConnection('mysql2')->where('sku',$value['sku'])
		    					->where('seller_id', $this->seller_id)
		    					->first();
		    			if($asin != "" OR $asin != null){
		    				$item['asin'] = $asin->asin;
		    			}
		    			if($item['total'] == 0){
		    				$item['total'] += $item['price_amount'];
		    				$item['total'] += $item['promotional_rebates'];
		    				$item['total'] += $item['shipment_fee_amount'];
		    				$item['total'] += $item['order_fee_amount'];
		    				$item['total'] += $item['direct_payment_amount'];
		    				$item['total'] += $item['item_related_fee_amount'];
		    				$item['total'] += $item['other_amount'];
		    			}

		    			$item['seller_id'] = $this->seller_id;
		    			$item['created_at'] = date('Y-m-d H:i:s');
		    			$save = $fulfilled->insertData($item);
		    		}
		    	}
		    	echo "Done!<br>";
		    	echo "Resting...<br><br>";
	    		ob_flush();
				flush();
		    	sleep(5);
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
		$log->description = 'SettlementReport';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for SettlementReport';
		$log->api_used = $report_type;
		$log->start_time = $response['time_start'];
		$log->end_sent = $response['time_end'];
		$log->record_fetched = $total_records;
		$log->message = $message;
		$log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('SettlementReport for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('SettlementReport for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
    }
}
