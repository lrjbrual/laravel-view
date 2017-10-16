<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use App\UniversalModel;
use App\Seller;
use App\MarketplaceAssign;
use App\FlatFileAllOrdersByDate;
use Illuminate\Support\Facades\Input;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Log;
use App\Mail\CronNotification;
use Mail;
use App\MWSCustomClasses\MWSFetchReportClass;

class UpdateFlatFileAllOrdersByDateController extends Controller
{
	private $seller_id;
	private $mkp;
	private $mkp_code;

	public function index(){
		try {
			ini_set('memory_limit', '-1');
	        ini_set("max_execution_time", 0);  // on
	    	$univ = new UniversalModel();
	    	$total_records = 0;
	    	$time_start = time();
	    	$report_type = '_GET_FLAT_FILE_ALL_ORDERS_DATA_BY_ORDER_DATE_';

	    	if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
	        {
	        	echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
	            exit;
	        }

	        $this->seller_id = trim(Input::get('seller_id'));

	        //checker for invalid payment -Ferdz
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

	    	$w = array();
	    	if( Input::get('mkp') == null OR Input::get('mkp') == "" )
	        {
	        	echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
				exit();
	        }
	        $this->mkp = trim(Input::get('mkp'));

	        if($this->mkp == 1) $this->mkp_code = 'na';
	        else $this->mkp_code = 'eu';

			$where = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);
			$q= new MarketplaceAssign();
	        $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());

			$seller_details = array();

			if(count($mkp_assign)<=0){
				echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
				exit();
			}

	        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Flat File All Orders By Date Seller ID '.$this->seller_id.' mkp '.$this->mkp.' Server '.env('APP_ENV', ''), true));

	        //response for mail
			$time_start = time();
			$isError=false;
			$message = "Flat File All Orders By Date Cron Successfully Fetch Data!";
			$response['time_start'] = date('Y-m-d H:i:s');
			$response['total_time_of_execution'] = 0;
			$response['message'] = $message;
			$response['isError'] = false;
			$response['tries'] = 0;
			$tries=0;
			// flat_file_all_orders_by_dates
			foreach ($mkp_assign as $value) {
				if($value->marketplace_id == 1) $mkp = config('constant.amz_keys.na.marketplaces');
				if($value->marketplace_id == 2) $mkp = config('constant.amz_keys.eu.marketplaces');

				$merchantId = $value->mws_seller_id;
				$MWSAuthToken = $value->mws_auth_token;
				$columnChecked=false;

				$countryarr = array();
				foreach ($mkp as $key => $value) {
					$countryarr[] = $key;
				}
				$ct = 24;
		    	$ss = FlatFileAllOrdersByDate::where('seller_id', $this->seller_id)
		    		->whereIn('country', $countryarr)->take(1)->get();

		    	if(count($ss) > 0){
		    		$ct = 1;
		    	}

			    foreach ($mkp as $key => $mkp_data) {
			    	$tries++;
			    	$country = $key;
			    	//$ct = 18;

			    	$ss = FlatFileAllOrdersByDate::where('seller_id', $this->seller_id)
			    		->whereIn('country', $countryarr)->take(1)->get();
			    	if(count($ss) > 0){
			    		$isEmpty = false;
			    		//$ct = 1;
			    	}else $isEmpty = true;

			    	if($ct == 1){
			    		$start_date = Carbon::today()->subMonth();
			    		$end_date = Carbon::today();
			    	}else{
				    	$start_date = Carbon::today()->subMonths($ct);
				    	$end_date = Carbon::today()->subMonths($ct - 1);
				    }

			    	while($ct>0){
			    		echo "Start Date : ".(string)$start_date." End Date : ".(string)$end_date."<br>";
				    	$init = array(
							'merchantId'    => $merchantId,
				            'MWSAuthToken'  => $MWSAuthToken,		//mkp_auth_token
				            'country'		=> $country,			//mkp_country
				            'marketPlace'	=> $mkp_data['id'],		//seller marketplace id
				    		'name'			=> 'Flat File All Orders By Date',
				    		'start_date'	=> (string)$start_date,
				    		'end_date'		=> (string)$end_date
				    	);
				    	//dd($init);
				    	$amz = new MWSFetchReportClass();
				    	$amz->initialize($init);
				    	$return = $amz->fetchData($report_type);
				    	$ct--;
				    	if($ct == 1){
				    		$start_date = Carbon::today()->subMonth();
				    		$end_date = Carbon::today();
				    	}else{
				    		$start_date = Carbon::today()->subMonths($ct);
				    		$end_date = Carbon::today()->subMonths($ct - 1);
				    	}

				    	echo count($return['data']);
				    	echo "<br>Saving to database...";

				    	//check columns
				    	$columns = array();
						if(($columnChecked==false)&&(isset($return['data'][0]))){
							$columns = $amz->checkForNewColumn('flat_file_all_orders_by_dates',$return['data'][0]);
							$columnChecked=true;
						}


				    	foreach ($return['data'] as $value) {
				    		foreach ($columns as $column) {
				    			if(array_key_exists($column, $value)) unset($value[$column]);
				    		}
				    		$item2 = array();
				    		$item2['sku'] = isset($value['sku']) ? ($value['sku']) : "";
				    		$item2['asin'] = isset($value['asin']) ? ($value['asin']) : "";
				    		$item2['country'] = $country;
				    		$item2['seller_id'] = $this->seller_id;
				    		$item2['amazon_order_id'] = isset($value['amazon_order_id']) ? $value['amazon_order_id'] : "";
				    		$item2['order_status'] = isset($value['order_status']) ? ($value['order_status']) : "";

							$item = array();
			    			$total_records++;
			    			$value['country'] = $country;
			    			$value['seller_id'] = $this->seller_id;

				    		$value['purchase_date'] = isset($value['purchase_date']) ? ($value['purchase_date']) : "";
				    		$pd = explode('T', $value['purchase_date']);
							if(count($pd) == 2){
								$pdf = $pd[0];
								$pd = explode('+', $pd[1]);
								$value['purchase_date'] = $pdf." ".$pd[0];
							}else{
								$value['purchase_date'] = null;
							}

				    		$value['last_updated_date'] = isset($value['last_updated_date']) ? ($value['last_updated_date']) : "";
				    		$pd = explode('T', $value['last_updated_date']);
							if(count($pd) == 2){
								$pdf = $pd[0];
								$pd = explode('+', $pd[1]);
								$value['last_updated_date'] = $pdf." ".$pd[0];
							}else{
								$value['last_updated_date'] = null;
							}



				    		$value['quantity'] = isset($value['quantity']) ? ($value['quantity']) : 0;
			    			if(trim($value['quantity']) == '' OR $value['quantity']==null)
			    				$value['quantity'] = 0;
			    			else $value['quantity'] = $value['quantity'];

				    		$value['item_price'] = isset($value['item_price']) ? ($value['item_price']) : 0;
			    			if(trim($value['item_price']) == '' OR $value['item_price']==null)
			    				$value['item_price'] = 0;
			    			else $value['item_price'] = $value['item_price'];

				    		$value['item_tax'] = isset($value['item_tax']) ? ($value['item_tax']) : 0;
			    			if(trim($value['item_tax']) == '' OR $value['item_tax']==null)
			    				$value['item_tax'] = 0;
			    			else $value['item_tax'] = $value['item_tax'];

				    		$value['shipping_price'] = isset($value['shipping_price']) ? ($value['shipping_price']) : 0;
			    			if(trim($value['shipping_price']) == '' OR $value['shipping_price']==null)
			    				$value['shipping_price'] = 0;
			    			else $value['shipping_price'] = $value['shipping_price'];

				    		$value['shipping_tax'] = isset($value['shipping_tax']) ? ($value['shipping_tax']) : 0;
			    			if(trim($value['shipping_tax']) == '' OR $value['shipping_tax']==null)
			    				$value['shipping_tax'] = 0;
			    			else $value['shipping_tax'] = $value['shipping_tax'];

				    		$value['gift_wrap_price'] = isset($value['gift_wrap_price']) ? ($value['gift_wrap_price']) : 0;
			    			if(trim($value['gift_wrap_price']) == '' OR $value['gift_wrap_price']==null)
			    				$value['gift_wrap_price'] = 0;
			    			else $value['gift_wrap_price'] = $value['gift_wrap_price'];

				    		$value['gift_wrap_tax'] = isset($value['gift_wrap_tax']) ? ($value['gift_wrap_tax']) : 0;
			    			if(trim($value['gift_wrap_tax']) == '' OR $value['gift_wrap_tax']==null)
			    				$value['gift_wrap_tax'] = 0;
			    			else $value['gift_wrap_tax'] = $value['gift_wrap_tax'];

				    		$value['item_promotion_discount'] = isset($value['item_promotion_discount']) ? ($value['item_promotion_discount']) : 0;
			    			if(trim($value['item_promotion_discount']) == '' OR $value['item_promotion_discount']==null)
			    				$value['item_promotion_discount'] = 0;
			    			else $value['item_promotion_discount'] = $value['item_promotion_discount'];

				    		$value['ship_promotion_discount'] = isset($value['ship_promotion_discount']) ? ($value['ship_promotion_discount']) : 0;
			    			if(trim($value['ship_promotion_discount']) == '' OR $value['ship_promotion_discount']==null)
			    				$value['ship_promotion_discount'] = 0;
			    			else $value['ship_promotion_discount'] = $value['ship_promotion_discount'];

				    		$value['vat_exclusive_item_price'] = isset($value['vat_exclusive_item_price']) ? ($value['vat_exclusive_item_price']) : 0;
			    			if(trim($value['vat_exclusive_item_price']) == '' OR $value['vat_exclusive_item_price']==null)
			    				$value['vat_exclusive_item_price'] = 0;
			    			else $value['vat_exclusive_item_price'] = $value['vat_exclusive_item_price'];

				    		$value['vat_exclusive_shipping_price'] = isset($value['vat_exclusive_shipping_price']) ? ($value['vat_exclusive_shipping_price']) : 0;
			    			if(trim($value['vat_exclusive_shipping_price']) == '' OR $value['vat_exclusive_shipping_price']==null)
			    				$value['vat_exclusive_shipping_price'] = 0;
			    			else $value['vat_exclusive_shipping_price'] = $value['vat_exclusive_shipping_price'];

				    		$value['vat_exclusive_giftwrap_price'] = isset($value['vat_exclusive_giftwrap_price']) ? ($value['vat_exclusive_giftwrap_price']) : 0;
			    			if(trim($value['vat_exclusive_giftwrap_price']) == '' OR $value['vat_exclusive_giftwrap_price']==null)
			    				$value['vat_exclusive_giftwrap_price'] = 0;
			    			else $value['vat_exclusive_giftwrap_price'] = $value['vat_exclusive_giftwrap_price'];

			    			if(!$isEmpty){
			    				$checkExist = FlatFileAllOrdersByDate::where('sku', isset($value['sku']) ? ($value['sku']) : "")
				    				->where('asin', isset($value['asin']) ? ($value['asin']) : "")
				    				->where('sales_channel', 'like' , '%'.$country.'%')
				    				->where('seller_id', $this->seller_id)
				    				->where('amazon_order_id', isset($value['amazon_order_id']) ? $value['amazon_order_id'] : "")
				    				->where('order_status', isset($value['order_status']) ? ($value['order_status']) : "")
				    				->get();

					    		if( !(count($checkExist) > 0) ){
						    		$value['created_at'] = date('Y-m-d H:i:s');
					    			$save = $univ->insertData($value);
					    		}else{
					    			$value['updated_at'] = date('Y-m-d H:i:s');
					    			FlatFileAllOrdersByDate::where('sku', isset($value['sku']) ? ($value['sku']) : "")
					    				->where('asin', isset($value['asin']) ? ($value['asin']) : "")
					    				->where('sales_channel', 'like' , '%'.$country.'%')
					    				->where('seller_id', $this->seller_id)
					    				->where('amazon_order_id', isset($value['amazon_order_id']) ? $value['amazon_order_id'] : "")
					    				->where('order_status', isset($value['order_status']) ? ($value['order_status']) : "")
					    				->update($value);
					    		}
					    	}else{
					    		$value['created_at'] = date('Y-m-d H:i:s');
					    		$value = $univ->insertData('flat_file_all_orders_by_dates',$value);
					    	}
				    	}
				    }
		    	}
	    	}
	    	$time_end = time();
	        $response['total_records'] = $total_records;
	        $response['isError'] = false;
	        $response['time_end'] = date('Y-m-d H:i:s');
	        $response['time_start'] = date('Y-m-d H:i:s', $time_start);
	        $response['total_time_of_execution'] = ($time_end - $time_start)/60;
	        $response['tries'] = 1;
	        $response['message'] = "SUCCESS!";

	        $log = new Log;
	        $log->seller_id = $this->seller_id;
	        $log->description = 'Flat FIle All Orders By Date';
	        $log->date_sent = date('Y-m-d H:i:s');
	        $log->subject = 'Cron Notification for Flat FIle All Orders By Date';
	        $log->api_used = $report_type;
	        $log->start_time = $response['time_start'];
	        $log->end_sent = date('Y-m-d H:i:s');
	        $log->record_fetched = $total_records;
	        $log->message = "SUCCESS!!";
	        $log->save();

	        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Flat File All Orders By Date Seller ID '.$this->seller_id.' mkp '.$this->mkp.' Server '.env('APP_ENV', ''), false, $response));


		} catch (Exception $e) {
			$time_end = time();
			$response['time_start'] = date('Y-m-d H:i:s', $time_start);
			$response['time_end'] = date('Y-m-d H:i:s', $time_end);
			$response['total_time_of_execution'] = ($time_end - $time_start)/60;
			$response['tries'] = 1;
			$response['total_records'] = (isset($total_records) ? $total_records : 0);
			$response['isError'] = true;
			$response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();

	        $log = new Log;
	        $log->seller_id = $this->seller_id;
	        $log->description = 'Flat FIle All Orders By Date';
	        $log->date_sent = date('Y-m-d H:i:s');
	        $log->subject = 'Cron Notification for Flat FIle All Orders By Date';
	        $log->api_used = $report_type;
	        $log->start_time = $response['time_start'];
	        $log->end_sent = date('Y-m-d H:i:s');
	        $log->record_fetched = (isset($total_records) ? $total_records : 0);
	        $log->message = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
	        $log->save();

	        echo "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();

			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Flat File All Orders By Date Seller ID '.$this->seller_id.' mkp '.$this->mkp.' Server '.env('APP_ENV', '').' (error)', false, $response));
		}
	}
}
