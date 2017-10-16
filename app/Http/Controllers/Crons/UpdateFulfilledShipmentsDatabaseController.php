<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MWSCustomClasses\MWSFetchReportClass;
use App\MarketplaceAssign;
use App\FulfilledShipment;
use App\UniversalModel;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use App\Log;
use App\Seller;
use Carbon\Carbon;
use Mail;
class UpdateFulfilledShipmentsDatabaseController extends Controller
{
    //
    private $seller_id;
    private $mkp='';

    public function index(){
    try {
    	ini_set('memory_limit', '-1');
        ini_set("max_execution_time", 0);  // on
    	$total_records = 0;
    	$fulfilled = new FulfilledShipment();
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

		//response for mail
		$time_start = time();
		$isError = false;
		$response['time_start'] = date('Y-m-d H:i:s');
		$response['total_time_of_execution'] = 0;
		$response['message'] = "Fulfilled Shipments Cron Successfully Fetch Data!";
		$response['isError'] = false;
		$response['tries'] = 0;
		$tries=0;
		$message = "Fulfilled Shipments Cron Successfully Fetch Data!";

    	$report_type = '_GET_AMAZON_FULFILLED_SHIPMENTS_DATA_';
    	//$response = array();

    	$isEmpty = false;
    	$mkp_assign = array();
    	$q= new MarketplaceAssign();
    	$where = array('seller_id'=>$this->seller_id);
    	$w = array('seller_id'=> $this->seller_id);
    	if( Input::get('mkp') != null OR Input::get('mkp') != "" )
        {
        	$this->mkp = trim(Input::get('mkp'));
        	$where  = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);

        	if($this->mkp == 2) $w = array('seller_id'=> $this->seller_id, 'like' => ['sales_channel','uk']);
        	else $w = array('seller_id'=> $this->seller_id, 'like' => ['sales_channel','com']);
        }

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Fulfilled Shipments for seller'.$this->seller_id.' mkp'.$this->mkp, true));

        $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());

		$seller_details = array();

		$ff_data = 0;
		$ff_data_count = array();
		if(count($mkp_assign)>0) $ff_data_count = $univ->getRecords('fulfilled_shipments',array('*'),$w,array(),true);
		$start_date = '-1 month';
		$end_date = null;
		$m_ctr = -1;
		if(count($ff_data_count)>0  AND !$isEmpty) $isEmpty = false;
		else{
			$isEmpty = true;
			//$start_date = '-6 months';
			$m_ctr = -6;
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

		while($m_ctr<0){
			$delay_request = 0;
			if($m_ctr == -1){
				$start_date = $m_ctr." month";
				$end_date = null;
			}else{
				$start_date = $m_ctr." months";
				$end_date = ($m_ctr+1)." months";
				$delay_request = 30*60;
			}
			$m_ctr++;
			foreach ($mkp_assign as $value) {
				if($value->marketplace_id == 1) $mkp = config('constant.amz_keys.na.marketplaces');
				if($value->marketplace_id == 2) $mkp = config('constant.amz_keys.eu.marketplaces');
				$merchantId = $value->mws_seller_id;
				$MWSAuthToken = $value->mws_auth_token;
          $columnChecked=false;
			    foreach ($mkp as $key => $mkp_data) {
			    	$tries++;
			    	$country = "";
			    	$country = $key;
			    	$init;
			    	$init = array();
			    	$init = array(
						'merchantId'    => $merchantId,
			            'MWSAuthToken'  => $MWSAuthToken,		//mkp_auth_token
			            'country'		=> $country,			//mkp_country
			            'marketPlace'	=> $mkp_data['id'],		//seller marketplace id
			    		'start_date'	=> $start_date,
			    		'end_date'		=> $end_date
			    		);
			    	$amz = new MWSFetchReportClass();
			    	$amz->initialize($init);
			    	
			    	$result = $amz->fetchData($report_type);
			    	dd($result);
			    	if($country == 'uk') $sc = "Amazon.co.uk";
			    	else if($country == 'us') $sc = "Amazon.com";
			    	else $sc = "Amazon.".$country;
			    	$ss = FulfilledShipment::where('seller_id', $this->seller_id)->where('sales_channel', $sc)->take(1)->get();
			    	if(count($ss) > 0) $isEmpty = false;
			    	else $isEmpty = true;

            if(($columnChecked==false)&&(isset($result['data'][0]))){
              $amz->checkForNewColumn('fulfilled_shipments',$result['data'][0]);
              $columnChecked=true;
            }
			    	foreach ($result['data'] as $value) {
			    		$item = array();
			    		$item['sku'] = $value['sku'];
			    		$item['amazon_order_id'] = $value['amazon_order_id'];
			    		$item['merchant_order_id'] = $value['merchant_order_id'];
			    		$item['seller_id'] = $this->seller_id;

		    			$total_records++;

		    			$pd = explode('T', $value['purchase_date']);
						if(count($pd) == 2){
							$pdf = $pd[0];
							$pd = explode('+', $pd[1]);
							$value['purchase_date'] = $pdf." ".$pd[0];
						}else{
							$value['purchase_date'] = null;
						}

		    			$pd = explode('T', $value['payments_date']);
		    			if(count($pd) == 2){
							$pdf = $pd[0];
							$pd = explode('+', $pd[1]);
							$value['payments_date'] = $pdf." ".$pd[0];
						}else{
							$value['payments_date'] = null;
						}

		    			$pd = explode('T', $value['shipment_date']);
		    			if(count($pd) == 2){
							$pdf = $pd[0];
							$pd = explode('+', $pd[1]);
							$value['shipment_date'] = $pdf." ".$pd[0];
						}else{
							$value['shipment_date'] = null;
						}

		    			$pd = explode('T', $value['reporting_date']);
		    			if(count($pd) == 2){
							$pdf = $pd[0];
							$pd = explode('+', $pd[1]);
							$value['reporting_date'] = $pdf." ".$pd[0];
						}else{
							$value['reporting_date'] = null;
						}

		    			$pd = explode('T', $value['estimated_arrival_date']);
		    			if(count($pd) == 2){
							$pdf = $pd[0];
							$pd = explode('+', $pd[1]);
							$value['estimated_arrival_date'] = $pdf." ".$pd[0];
						}else{
							$value['estimated_arrival_date'] = null;
						}
						if($value['sales_channel'] == "" OR $value['sales_channel']==null OR $value['sales_channel']==" "){
							$value['sales_channel'] = 'Non-Amazon';
						}
						if($value['item_tax'] =="" OR $value['item_tax'] ==" " OR $value['item_tax'] ==null)
							$value['item_tax'] = 0;

						if($value['shipping_price'] =="" OR $value['shipping_price'] ==" " OR $value['shipping_price'] ==null)
							$value['shipping_price'] = 0;

						if($value['shipping_tax'] =="" OR $value['shipping_tax'] ==" " OR $value['shipping_tax'] ==null)
							$value['shipping_tax'] = 0;

						if($value['gift_wrap_price'] =="" OR $value['gift_wrap_price'] ==" " OR $value['gift_wrap_price'] ==null)
							$value['gift_wrap_price'] = 0;

						if($value['gift_wrap_tax'] =="" OR $value['gift_wrap_tax'] ==" " OR $value['gift_wrap_tax'] ==null)
							$value['gift_wrap_tax'] = 0;

						if($value['item_price'] =="" OR $value['item_price'] ==" " OR $value['item_price'] ==null)
							$value['item_price'] = 0;

						if($value['item_promotion_discount'] =="" OR $value['item_promotion_discount'] ==" " OR $value['item_promotion_discount'] ==null)
							$value['item_promotion_discount'] = 0;

						if($value['ship_promotion_discount'] =="" OR $value['ship_promotion_discount'] ==" " OR $value['ship_promotion_discount'] ==null)
							$value['ship_promotion_discount'] = 0;


		    			$value['product_name'] = ($value['product_name']);

		    			$value['seller_id'] = $this->seller_id;
		    			$value['created_at'] = date('Y-m-d H:i:s');

			    		if(!$isEmpty){
				    		if(!$fulfilled->isExist($item)){
				    			$save = $fulfilled->insertData($value);
				    		}else{
				    			$value['updated_at'] = date('Y-m-d H:i:s');
				    			FulfilledShipment::where('sku', $item['sku'])
				    				->where('amazon_order_id', $item['amazon_order_id'])
				    				->where('merchant_order_id', $item['merchant_order_id'])
				    				->where('seller_id', $item['seller_id'])
				    				->update($value);
				    		}
				    	}else{
				    		$save = $fulfilled->insertData($value);
				    	}

			    	}
		    	}
	    	}
	    	sleep($delay_request);
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
		$log->description = 'Fulfilled Shipments';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for Fulfilled Shipments';
		$log->api_used = $report_type;
		$log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
		$log->record_fetched = $total_records;
		$log->message = $message;
		$log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Fulfilled Shipments for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Fulfilled Shipments for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
    }
}
