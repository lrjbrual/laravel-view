<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;

use App\MarketplaceAssign;
use App\InventoryAdjustmentReport;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use App\Product;
use App\Log;
use App\UniversalModel;
use Carbon\Carbon;
use App\Seller;

use Mail;
class UpdateInventoryAdjustmentsDatabaseController extends Controller
{
    //
    private $seller_id;
    private $mkp='';

    public function index(){
      try {
    	ini_set('memory_limit', '-1');
        ini_set("max_execution_time", 0);  // on
    	$total_records = 0;
    	$fulfilled = new InventoryAdjustmentReport();
    	$univ = new UniversalModel();
    	$product = new Product();
    	$isEmailed = false;

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
		$response['message'] = "Inventory Adjustments Cron Successfully Fetch Data!";
		$response['isError'] = false;
		$response['tries'] = 0;
		$tries=0;
		$message = "Inventory Adjustments Cron Successfully Fetch Data!";

    	$report_type = '_GET_FBA_FULFILLMENT_INVENTORY_ADJUSTMENTS_DATA_';
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

        	if($this->mkp == 2) $w = array('seller_id'=> $this->seller_id, 'like' => ['country','uk']);
        	else $w = array('seller_id'=> $this->seller_id, 'like' => ['country','us']);
        }
		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Inventory Adjustments for seller'.$this->seller_id.' mkp'.$this->mkp, true));

        $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());


		$seller_details = array();


		$ff_data = array();
		if(count($mkp_assign)>0) $ff_data_count = $univ->getRecords('inventory_adjustment_reports',array('*'),$w,array(),true);
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

		foreach ($mkp_assign as $value) {
			if($value->marketplace_id == 1) $mkp = config('constant.amz_keys.na.marketplaces');
			if($value->marketplace_id == 2) $mkp = config('constant.amz_keys.eu.marketplaces');

	    	$country = "";
			if($value->marketplace_id == 1) $country = 'us';
			if($value->marketplace_id == 2) $country = 'uk';

			$merchantId = $value->mws_seller_id;
			$MWSAuthToken = $value->mws_auth_token;

		    $tries++;
	    	$init;
	    	$init = array();
	    	$init = array(
				'merchantId'    => $merchantId,
	            'MWSAuthToken'  => $MWSAuthToken,		//mkp_auth_token
	            'country'		=> $country,			//mkp_country
	            'marketPlace'	=> null,		//seller marketplace id
	    		'start_date'	=> $start_date,
	    		'end_date'		=> null,
	    		'name'			=> 'Inventory Adjustments'
	    		);
	    	$amz = new MWSFetchReportClass();
	    	$amz->initialize($init);
	    	$result = $amz->fetchData($report_type);

	    	$ss = InventoryAdjustmentReport::where('seller_id', $this->seller_id)->where('country', $country)->take(1)->get();
	    	if(count($ss) > 0) $isEmpty = false;
	    	else $isEmpty = true;

        $columnChecked=false;
        if(($columnChecked==false)&&(isset($result['data'][0]))){
          $amz->checkForNewColumn('inventory_adjustment_reports',$result['data'][0]);
          $columnChecked=true;
        }
	    	foreach ($result['data'] as $value) {
	    		$item = array();
	    		$item['sku'] = $value['sku'];
	    		$item['transaction_item_id'] = $value['transaction_item_id'];
	    		$item['fnsku'] = $value['fnsku'];
	    		$item['seller_id'] = $this->seller_id;
    			$total_records++;

    			$pd = explode('T', $value['adjusted_date']);
				if(count($pd) == 2){
					$pdf = $pd[0];
					$pd = explode('+', $pd[1]);
					$value['adjusted_date'] = $pdf." ".$pd[0];
				}else{
					$value['adjusted_date'] = null;
				}

				if($value['quantity'] == '' || $value['quantity'] == ' ' || $value['quantity'] == null)
					$value['quantity'] = 0;

    			$asin = $product->setConnection('mysql2')->where('sku',$value['sku'])
    					->where('seller_id', $this->seller_id)
    					->first();
    			if($asin != "" OR $asin != null){
    				$value['asin'] = $asin->asin;
    			}

    			$value['product_name'] = ($value['product_name']);
    			$value['seller_id'] = $this->seller_id;
    			$value['created_at'] = date('Y-m-d H:i:s');
    			$value['country'] = $country;

	    		if(!$isEmpty){
		    		if(!$fulfilled->isExist($item)){
		    			$save = $fulfilled->insertData($value);

		    			$fc = array();
		    			$fc['fulfillment_center_id'] = $value['fulfillment_center_id'];
		    			$un = new UniversalModel();
		    			if(!$un->isExist('fulfillment_country',$fc)){
		    				$un->insertData('fulfillment_country',$fc);
		    				if(!$isEmailed){
		    					$isEmailed = true;
		    					Mail::send('emails.autocampaign', array('message_body' => "New Fulfillment Center ID is Saved"), function($message)
								{
								    $message->to(env('MAIL_CRON_EMAIL','crons@trendle.io'))
								    		->subject('New Fulfillment Center ID')
								    		->from('crm@trendle.io' , 'CRM Trendle.io');
								});
		    				}
		    			}
		    		}else{
		    			$value['updated_at'] = date('Y-m-d H:i:s');
		    			InventoryAdjustmentReport::where('sku', $item['sku'])
		    				->where('transaction_item_id', $item['transaction_item_id'])
		    				->where('fnsku', $item['fnsku'])
		    				->where('seller_id', $item['seller_id'])
		    				->update($value);
		    		}
		    	}else{
		    		$save = $fulfilled->insertData($value);

	    			$fc = array();
	    			$fc['fulfillment_center_id'] = $value['fulfillment_center_id'];
	    			$un = new UniversalModel();
	    			if(!$un->isExist('fulfillment_country',$fc)){
	    				$un->insertData('fulfillment_country',$fc);
	    				if(!$isEmailed){
	    					$isEmailed = true;
	    					Mail::send('emails.autocampaign', array('message_body' => "New Fulfillment Center ID is Saved"), function($message)
							{
							    $message->to(env('MAIL_CRON_EMAIL','crons@trendle.io'))
							    		->subject('New Fulfillment Center ID')
							    		->from('crm@trendle.io' , 'CRM Trendle.io');
							});
	    				}
	    			}
		    	}

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
		$log->description = 'Inventory Adjustments';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for Inventory Adjustments';
		$log->api_used = $report_type;
		$log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
		$log->record_fetched = $total_records;
		$log->message = $message;
		$log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Inventory Adjustments for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Inventory Adjustments for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
    }
}
