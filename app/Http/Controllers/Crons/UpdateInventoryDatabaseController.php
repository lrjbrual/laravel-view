<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;

use App\MarketplaceAssign;
use App\InventoryData;
use App\Log;
use App\UniversalModel;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use App\Seller;

use Mail;

class UpdateInventoryDatabaseController extends Controller
{
    //
    private $seller_id;
    private $mkp='';

    public function index(){
    try{
    	ini_set('memory_limit', '-1');
        ini_set("max_execution_time", 0);  // on
        ob_start();
    	$total_records = 0;
    	$fulfilled = new InventoryData();
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

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Inventory', true));

		//response for mail
		$time_start = time();
		$isError = false;
		$response['time_start'] = date('Y-m-d H:i:s');
		$response['total_time_of_execution'] = 0;
		$response['message'] = "Inventory Cron Successfully Fetch Data!";
		$response['isError'] = false;
		$response['tries'] = 0;
		$tries=0;
		$message = "Inventory Cron Successfully Fetch Data!";

    	$report_type = '_GET_MERCHANT_LISTINGS_ALL_DATA_';
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

      	if($this->mkp == 2) $w = array('seller_id'=> $this->seller_id, 'mkp'=>'eu');
      	else $w = array('seller_id'=> $this->seller_id, 'mkp'=>'na');
      }
        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Inventory for seller'.$this->seller_id.' mkp'.$this->mkp, true));

        $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());


		$seller_details = array();
		$ff_data = array();
		if(count($mkp_assign)>0) $ff_data_count = $univ->getRecords('inventory_datas',array('*'),$w,array(),true);
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

	    	$mkp_code = "";
			if($value->marketplace_id == 1) $mkp_code = 'na';
			if($value->marketplace_id == 2) $mkp_code = 'eu';

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
	    		'name'			=> 'Inventory'
	    		);
	    	$amz = new MWSFetchReportClass();
	    	$amz->initialize($init);
	    	$result = $amz->fetchData($report_type);

	    	$ss = InventoryData::where('seller_id', $this->seller_id)->where('mkp', $mkp_code)->take(1)->get();
	    	if(count($ss) > 0) $isEmpty = false;
	    	else $isEmpty = true;

        $columnChecked=false;
        if(($columnChecked==false)&&(isset($result['data'][0]))){
          $amz->checkForNewColumn('inventory_datas',$result['data'][0]);
          $columnChecked=true;
        }
	    	foreach ($result['data'] as $value) {
	    		$item = array();
	    		$item['seller_sku'] = $value['seller_sku'];
	    		$item['listing_id'] = $value['listing_id'];
	    		$item['asin1'] = $value['asin1'];
	    		$item['seller_id'] = $this->seller_id;

	    		$item2 = array();
	    		$item2['seller_sku'] = $value['seller_sku'];
	    		$item2['listing_id'] = $value['listing_id'];
	    		$item2['asin1'] = $value['asin1'];
	    		$item2['seller_id'] = $this->seller_id;

    			$total_records++;
    			if($country == 'ca' || $country == 'us'){
    				$pd = explode(' ', $value['open_date']);
    				if(count($pd) >= 2){
						$value['open_date'] = $pd[0]." ".$pd[1];
					}else{
						$value['open_date'] = null;
					}
    			}else{
    				$pd = explode(' ', $value['open_date']);
    				if(count($pd) >= 2){
    					$dt = explode('/', $pd[0]);
						$value['open_date'] = $dt[2]."-".$dt[1]."-".$dt[0]." ".$pd[1];
					}else{
						$value['open_date'] = null;
					}
    			}

				if($value['quantity'] == '' || $value['quantity'] == ' ' || $value['quantity'] == null)
					$value['quantity'] = 0;

				if($value['zshop_shipping_fee'] == '' || $value['zshop_shipping_fee'] == ' ' || $value['zshop_shipping_fee'] == null)
					$value['zshop_shipping_fee'] = 0;

				if($value['price'] == '' || $value['price'] == ' ' || $value['price'] == null)
					$value['price'] = 0;


    			$value['item_name'] = ($value['item_name']);
    			$value['item_description'] = ($value['item_description']);

    			$value['seller_id'] = $this->seller_id;
    			$value['mkp'] = $mkp_code;
    			$value['created_at'] = date('Y-m-d H:i:s');

	            $item['seller_id'] = (isset($value['seller_id']) ? $value['seller_id'] : '');
	            $item['item_name'] = (isset($value['item_name']) ? $value['item_name'] : '');
	            $item['item_description'] = (isset($value['item_description']) ? $value['item_description'] : '');
	            $item['listing_id'] = (isset($value['listing_id']) ? $value['listing_id'] : '');
	            $item['seller_sku'] = (isset($value['seller_sku']) ? $value['seller_sku'] : '');
	            $item['price'] = (isset($value['price']) ? $value['price'] : '');
	            $item['quantity'] = (isset($value['quantity']) ? $value['quantity'] : '');
	            $item['image_url'] = (isset($value['image_url']) ? $value['image_url'] : '');
	            $item['item_is_marketplace'] = (isset($value['item_is_marketplace']) ? $value['item_is_marketplace'] : '');
	            $item['product_id_type'] = (isset($value['product_id_type']) ? $value['product_id_type'] : '');
	            $item['zshop_shipping_fee'] = (isset($value['zshop_shipping_fee']) ? $value['zshop_shipping_fee'] : '');
	            $item['item_note'] = (isset($value['item_note']) ? $value['item_note'] : '');
	            $item['item_condition'] = (isset($value['item_condition']) ? $value['item_condition'] : '');
	            $item['zshop_category1'] = (isset($value['zshop_category1']) ? $value['zshop_category1'] : '');
	            $item['zshop_browse_path'] = (isset($value['zshop_browse_path']) ? $value['zshop_browse_path'] : '');
	            $item['zshop_storefront_feature'] = (isset($value['zshop_storefront_feature']) ? $value['zshop_storefront_feature'] : '');
	            $item['asin1'] = (isset($value['asin1']) ? $value['asin1'] : '');
	            $item['asin2'] = (isset($value['asin2']) ? $value['asin2'] : '');
	            $item['asin3'] = (isset($value['asin3']) ? $value['asin3'] : '');
	            $item['will_ship_internationally'] = (isset($value['will_ship_internationally']) ? $value['will_ship_internationally'] : '');
	            $item['expedited_shipping'] = (isset($value['expedited_shipping']) ? $value['expedited_shipping'] : '');
	            $item['zshop_boldface'] = (isset($value['zshop_boldface']) ? $value['zshop_boldface'] : '');
	            $item['product_id'] = (isset($value['product_id']) ? $value['product_id'] : '');
	            $item['bid_for_featured_placement'] = (isset($value['bid_for_featured_placement']) ? $value['bid_for_featured_placement'] : '');
	            $item['add_delete'] = (isset($value['add_delete']) ? $value['add_delete'] : '');
	            $item['pending_quantity'] = (isset($value['pending_quantity']) ? $value['pending_quantity'] : '');
	            $item['fulfillment_channel'] = (isset($value['fulfillment_channel']) ? $value['fulfillment_channel'] : '');
	            $item['optional_payment_type_exclusion'] = (isset($value['optional_payment_type_exclusion']) ? $value['optional_payment_type_exclusion'] : '');
	            $item['merchant_shipping_group'] = (isset($value['merchant_shipping_group']) ? $value['merchant_shipping_group'] : '');
	            $item['created_at'] = (isset($value['created_at']) ? $value['created_at'] : '');
	            $item['mkp'] = (isset($value['mkp']) ? $value['mkp'] : '');

	    		if(!$isEmpty){
		    		if(!$fulfilled->isExist($item2)){
		    			$save = $fulfilled->insertData($item);
		    		}else{
		    			$item['updated_at'] = date('Y-m-d H:i:s');
		    			InventoryData::where('seller_id', $this->seller_id)
		    				->where('seller_sku', $item['seller_sku'])
		    				->where('listing_id', $item['listing_id'])
		    				->where('asin1', $item['asin1'])
		    				->update($item);
		    		}
		    	}else{
		    		$save = $fulfilled->insertData($item);
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
		$log->description = 'Inventory Data';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for Inventory Data';
		$log->api_used = $report_type;
		$log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
		$log->record_fetched = $total_records;
		$log->message = $message;
		$log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Inventory for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Inventory for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
    }
}
