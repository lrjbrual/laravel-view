<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;

use App\MarketplaceAssign;
use App\Product;
use App\Log;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use App\Seller;
use Carbon\Carbon;

use Mail;

class UpdateProductsDatabaseController extends Controller
{
    private $seller_id;
    private $mkp='';

    public function index(){
    try {
    	ini_set('memory_limit', '-1');
        ini_set("max_execution_time", 0);  // on
        // ob_start();
    	$total_records = 0;
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

		//response for mail
		$time_start = time();
		$isError=false;
		$message = "Product Cron Successfully Fetch Data!";
		$response['time_start'] = date('Y-m-d H:i:s');
		$response['total_time_of_execution'] = 0;
		$response['message'] = $message;
		$response['isError'] = false;
		$response['tries'] = 0;
		$tries=0;

    	$report_type = '_GET_FBA_MYI_ALL_INVENTORY_DATA_';
    	$response = array();

    	$isEmpty = false;
		$q= new MarketplaceAssign();
		$where = array('seller_id'=>$this->seller_id);
    	if( Input::get('mkp') != null OR Input::get('mkp') != "" )
        {
        	$this->mkp = trim(Input::get('mkp'));
        	$where  = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);
        	$isEmpty = true;
        }

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Products for seller'.$this->seller_id.' mkp'.$this->mkp, true));
		$mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());

		$seller_details = array();

		if(count($mkp_assign)<=0){
			$response['time_start'] = date('Y-m-d H:i:s');
			$response['time_end'] = date('Y-m-d H:i:s');
			$response['isError'] = true;
			$response['message'] = "No Marketplace assigned!";
			$response['tries'] = 0;
			$message = "No Marketplace assigned!";
			echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
		}


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
		    		'start_date'	=> null,
		    		'name'			=> 'Products'
		    		);
		    	$amz = new MWSFetchReportClass();
		    	$amz->initialize($init);
		    	$return = $amz->fetchData($report_type);
		    	echo count($return['data']);
		    	echo "<br>Saving to database...";

		    	$ss = Product::where('seller_id', $this->seller_id)->where('country', $country)->take(1)->get();
		    	if(count($ss) > 0) $isEmpty = false;
		    	else $isEmpty = true;



          if(($columnChecked==false)&&(isset($return['data'][0]))){
            $amz->checkForNewColumn('products',$return['data'][0]);
            $columnChecked=true;
          }
		    	foreach ($return['data'] as $value) {
		    		$item2 = array();
		    		$item2['sku'] = ($value['sku']);
		    		$item2['asin'] = ($value['asin']);
		    		$item2['country'] = $country;
		    		$item2['seller_id'] = $this->seller_id;


		    		$item = array();
		    		$item['sku'] = ($value['sku']);
		    		$item['asin'] = ($value['asin']);
		    		$item['country'] = $country;
		    		$item['seller_id'] = $this->seller_id;
	    			$total_records++;
	    			$item['product_name'] = ($value['product_name']);
	    			if($value['price'] == '' OR $value['price']==null OR $value['price'] == " ")
	    				$item['price'] = 0;
	    			else $item['price'] = $value['price'];
	    			if($value['quantity'] == '' OR $value['quantity']==null OR $value['quantity'] == " ")
	    				$item['quantity'] = 0;
	    			else $item['quantity'] = $value['quantity'];
	    			$item['date_created'] = date('Y-m-d H:i:s');

	    			if(!$isEmpty){
			    		if(!$product->isExist($item2)){
				    		$item['created_at'] = date('Y-m-d H:i:s');
			    			$save = $product->insertData($item);
			    		}else{
			    			$item['updated_at'] = date('Y-m-d H:i:s');
			    			Product::where('sku', $item['sku'])
			    				->where('asin', $item['asin'])
			    				->where('country', $item['country'])
			    				->where('seller_id', $item['seller_id'])
			    				->update($item);
			    		}
			    	}else{
			    		$item['created_at'] = date('Y-m-d H:i:s');
			    		$save = $product->insertData($item);
			    	}
		    	}
	    	}
    	}
    	$time_end = time();
    	$response['total_records'] = $total_records;
		$response['isError'] = $isError;
		$response['time_end'] = date('Y-m-d H:i:s');
		$response['time_start'] = date('Y-m-d H:i:s', $time_start);
		$response['total_time_of_execution'] = ($time_end - $time_start)/60;
		$response['tries'] = $tries;
		$response['message'] = $message;

		$log = new Log;
		$log->seller_id = $this->seller_id;
		$log->description = 'Products';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for Products';
		$log->api_used = $report_type;
		$log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
		$log->record_fetched = $total_records;
		$log->message = $message;
		$log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Products for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Products for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
    }
}
