<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MWSCustomClasses\MWSFetchReportClass;
use App\MarketplaceAssign;
use App\SellerReview;
use App\FulfilledShipment;
use App\Product;
use App\Log;
use App\UniversalModel;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use App\Seller;
use \Config;
use App\Mail\ReviewsEmail;

use Mail;
class UpdateSellerReviewDatabaseController extends Controller
{

    private $seller_id;
    private $mkp;

    public function index(){
      try{
    	ini_set('memory_limit', '-1');
        ini_set("max_execution_time", 0);  // on
        $emailArray = array();
    	$total_records = 0;
    	$SellerReview = new SellerReview();
    	$FulfilledShipment = new FulfilledShipment();
    	$product = new Product();
    	$univ = new UniversalModel();
    	$run_cron = true;
    	$isError = false;

    	if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
        {

            $run_cron = false;
        }
        else
        {
            $this->seller_id = trim(Input::get('seller_id'));

            if($this->seller_id == "" || $this->seller_id == null)
                $run_cron = false;
        }

        if($run_cron == false)
        {
            echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
            exit;
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
		$response['time_start'] = date('Y-m-d H:i:s');
		$response['total_time_of_execution'] = 0;
		$response['message'] = "Seller Reviews Cron Successfully Fetch Data!";
		$response['isError'] = false;
		$response['tries'] = 0;
		$tries=0;
		$message = "Seller Reviews Cron Successfully Fetch Data!";

    	$report_type = '_GET_SELLER_FEEDBACK_DATA_';
    	$response = array();

		$isEmpty = false;
		$q= new MarketplaceAssign();
		$where = array('seller_id'=>$this->seller_id);
    	$w = array('seller_id'=> $this->seller_id);
    	if( Input::get('mkp') != null OR Input::get('mkp') != "" )
        {
        	$this->mkp = trim(Input::get('mkp'));
        	$where  = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);

        	if($this->mkp == 2)
          {
            $w = array('seller_id'=> $this->seller_id, 'like' => ['country','uk'], 'orLike' => ['country','es'], 'orLike' => ['country','it'], 'orLike' => ['country','fr'], 'orLike' => ['country','de']);
        	}else
          {
            $w = array('seller_id'=> $this->seller_id, 'like' => ['country','us'], 'orLike' => ['country','ca']);
          }
        }
    Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Seller Review for seller'.$this->seller_id.' mkp'.$this->mkp, true));
		$mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());

		$seller_details = array();

		$ff_data = array();
		if(count($mkp_assign)>0) $ff_data = $univ->getRecords('seller_reviews',array('*'),$w,array(),true);
		$start_date = '-1 month';
		$end_date = null;
		//$m_ctr = -1;
		if(count($ff_data)>0 AND !$isEmpty) $isEmpty = false;
		else{
			$isEmpty = true;
			$start_date = '-6 months';
			//$m_ctr = -6;
		}
		if(count($mkp_assign)<=0){
			$response['time_start'] = date('Y-m-d H:i:s');
			$response['time_end'] = date('Y-m-d H:i:s');
			$response['isError'] = true;
			$response['message'] = "No Marketplace assigned!";
			$response['total_time_of_execution'] = 0;
			$response['tries'] = 0;
			$isError = true;
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
		            'country'		=> $country,	//mkp_country
		            'marketPlace'	=> $mkp_data['id'],		//seller marketplace id
		    		'start_date'	=> $start_date,
		    		'end_date'		=> $end_date
		    		);
		    	$amz = new MWSFetchReportClass();
		    	$amz->initialize($init);
		    	$result = $amz->fetchData($report_type);

		    	$ss = SellerReview::where('seller_id', $this->seller_id)->where('country', $country)->take(1)->get();
		    	if(count($ss) > 0) $isEmpty = false;
		    	else $isEmpty = true;


          if(($columnChecked==false)&&(isset($result['data'][0]))){
            $checkv = $this->updateKeys($result['data'][0]);
            $amz->checkForNewColumn('seller_reviews',$checkv);
            $columnChecked=true;
          }
		    	foreach ($result['data'] as $values) {
		    		$value = $this->updateKeys($values);
		            $value['order_number'] = (isset($value['order_number']) ? $value['order_number'] : '');
		            $value['review_comment'] = (isset($value['review_comment']) ? $value['review_comment'] : '');
		            $value['reviewer_email'] = (isset($value['reviewer_email']) ? $value['reviewer_email'] : '');
		            $value['review_date'] = (isset($value['review_date']) ? $value['review_date'] : '');
		            $value['reviewer_rating'] = (isset($value['reviewer_rating']) ? $value['reviewer_rating'] : '');
		            $value['your_response'] = (isset($value['your_response']) ? $value['your_response'] : '');
		            $value['arrived_on_time'] = (isset($value['arrived_on_time']) ? $value['arrived_on_time'] : '');
		            $value['item_as_described'] = (isset($value['item_as_described']) ? $value['item_as_described'] : '');
		            $value['customer_service'] = (isset($value['customer_service']) ? $value['customer_service'] : '');
		            $value['rater_role'] = (isset($value['rater_role']) ? $value['rater_role'] : '');
		    		$item2 = array();
		    		$item2['order_number'] = $value['order_number'];
		    		$item2['review_comment'] = $value['review_comment'];
		    		$item2['seller_id'] = $this->seller_id;
		    		$item = array();
		    		$item['order_number'] = $value['order_number'];
		    		$item['review_comment'] = $value['review_comment'];
		    		$item['seller_id'] = $this->seller_id;
					/*
					For review old codes to remove
		    		if(!$SellerReview->isExist($item)){
		    			$total_records++;
		    			$item['sku']="";
		    			$item['product_name']="";
		    			$item['asin']="";
		    			$item['reviewer_name'] = "";
		    			$skuname = $FulfilledShipment->setConnection('mysql2')->where('amazon_order_id',$value['order_number'])->first();
		    			if($skuname != "" OR $skuname != null){
		    				$item['sku'] = $skuname->sku;
		    				$item['product_name'] = ($skuname->product_name);
		    			}
					End Here
					*/
	    			$item['sku']="";
	    			$item['product_name']="";
	    			$item['asin']="";
	    			$item['reviewer_name'] = "";
	    			$skuname = $FulfilledShipment->setConnection('mysql2')->where('amazon_order_id',$value['order_number'])->first();
	    			if($skuname != "" OR $skuname != null){
	    				$item['sku'] = $skuname->sku;
	    				$item['product_name'] = ($skuname->product_name);
	    			}

	    			if($item['sku']!='' OR $item['sku']!=null){
		    			$asin = $product->setConnection('mysql2')->where('sku',$item['sku'])
		    					->where('country',$country)
		    					->where('seller_id', $this->seller_id)
		    					->first();
					/* to validate with ferdinand to remove
		    			if($rater != null OR $rater != "")
		    				$item['reviewer_name'] = $rater->buyer_name;


		    			$item['date_created'] = date('Y-m-d H:i:s');
		    			$dt = explode('/', $value['review_date']);
		    			if($country == 'us')
		    				$item['review_date'] = '20'.$dt['2'].'-'.$dt[0].'-'.$dt[1];
		    			else
		    				$item['review_date'] = '20'.$dt['2'].'-'.$dt[1].'-'.$dt[0];

		    			$item['country'] = $country;
		    			$item['reviewer_rating'] = $value['reviewer_rating'];
		    			$item['your_response'] = $value['your_response'];
		    			$item['arrived_on_time'] = $value['arrived_on_time'];
		    			$item['item_as_described'] = $value['item_as_described'];
		    			$item['customer_service'] = $value['customer_service'];
		    			$item['reviewer_email'] = $value['reviewer_email'];
		    			$item['rater_role'] = $value['rater_role'];
		    			$item['created_at'] = date('Y-m-d H:i:s');
		    			$item['seller_id'] = $this->seller_id;
					end
					*/

		    			if($asin != "" OR $asin != null){
		    				$item['asin'] = $asin->asin;
		    			}
		    		}

	    			$rater = $FulfilledShipment->setConnection('mysql2')->where('buyer_email',$value['reviewer_email'])
	    					->where('seller_id', $this->seller_id)
	    					->first();
	    			if($rater != null OR $rater != "")
	    				$item['reviewer_name'] = $rater->buyer_name;
						$item['date_created'] = date('Y-m-d H:i:s');

            $pos = strpos($value['review_date'], '/');
            if($pos!=''){
              $dt = explode('/', $value['review_date']);
            }else{
              $dt = explode('.', $value['review_date']);
            }

	    			if($country == 'us')
	    				$item['review_date'] = '20'.$dt['2'].'-'.$dt[0].'-'.$dt[1];
	    			else
	    				$item['review_date'] = '20'.$dt['2'].'-'.$dt[1].'-'.$dt[0];
						$item['country'] = $country;
						$item['reviewer_rating'] = $value['reviewer_rating'];
						$item['your_response'] = $value['your_response'];
						$item['arrived_on_time'] = $value['arrived_on_time'];
						$item['item_as_described'] = $value['item_as_described'];
						$item['customer_service'] = $value['customer_service'];
						$item['reviewer_email'] = $value['reviewer_email'];
						$item['rater_role'] = $value['rater_role'];
						$item['created_at'] = date('Y-m-d H:i:s');
						$item['seller_id'] = $this->seller_id;
						$total_records++;

		    		if(!$isEmpty){
			    		if(!$SellerReview->isExist($item2)){
			    			$save = $SellerReview->insertData($item);
			    			$item['star'] = $item['reviewer_rating'];

			    			if($item['sku'] == '' || $item['sku'] == null)
			    		{
			    			$item['sku'] = $item['asin'];
			    		}

			    			$item = (object)$item;
			    			$emailArray[] = $item;
			    		}else{
			    			$item['updated_at'] = date('Y-m-d H:i:s');
			    			SellerReview::where('order_number', $item['order_number'])
			    				->where('review_comment',$item['review_comment'])
			    				->where('seller_id', $this->seller_id)
			    				->update($item);
			    		}
			    	}else{
			    		$save = $SellerReview->insertData($item);
			    		$item['star'] = $item['reviewer_rating'];

			    		if($item['sku'] == '' || $item['sku'] == null)
			    		{
			    			$item['sku'] = $item['asin'];
			    		}

			    		$item = (object)$item;
			    		$emailArray[] = $item;
			    	}
		    	}


	    	}
    	}

    	if(count($emailArray) > 0)
    	{
	    	$this->send_email_notification($seller->firstname, $emailArray);
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
		$log->description = 'Seller Review';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for Seller Review';
		$log->api_used = $report_type;
		$log->start_time = $response['time_start'];
		$log->end_sent = $response['time_end'];
		$log->record_fetched = $total_records;
		$log->message = $message;
		$log->save();

	Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Seller Review for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {

      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Seller Review for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
    }

    private function updateKeys($item = array()){
    	$new_item = array();

        $key_index = 0;

        foreach($item as $key=>$item_value)
        {
            $use_key = $key;

            switch($key_index)
            {
                case 0:
                    $use_key = "review_date";
                    break;

                case 1:
                    $use_key = "reviewer_rating";
                    break;

                case 2:
                    $use_key = "review_comment";
                    break;

                case 3:
                    $use_key = "your_response";
                    break;

                case 4:
                    $use_key = "arrived_on_time";
                    break;

                case 5:
                    $use_key = "item_as_described";
                    break;

                case 6:
                    $use_key = "customer_service";
                    break;

                case 7:
                    $use_key = "order_number";
                    break;

                case 8:
                    $use_key = "reviewer_email";
                    break;

                case 9:
                    $use_key = "rater_role";
                    break;

            }
            $key_index += 1;

            $new_item[$use_key] = ($item_value);
        }

        return $new_item;
    }

    public function send_email_notification($first,$array)
	{
        //backup default config
        $backup = Config::get('mail');
        //set new config for sparkpost
	    if(env('SPARKPOST_MAIL_DRIVER') != ""){
	        Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));
      }

      $email = 'alchiebinan21@gmail.com';
      Mail::to($email)->send(new ReviewsEmail($first, $array,'seller'));

      //restore default config
      Config::set('mail', $backup);
	}
}
