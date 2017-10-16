<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use AmazonProduct;
use AmazonCore;
use AmazonFeed;
use AmazonOrderList;
use App\MWSCustomClasses\MWSFetchReportClass;
use App\MarketplaceAssign;
use App\Product;
use App\Log;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use Mail;
use App\UniversalModel;
use AmazonProductInfo;
use DB;
use App\Seller;
use Carbon\Carbon;

class UpdateProductsEstimateFeesController extends Controller
{
    //
    public function index()
    {
      try{
    	ini_set('memory_limit', '-1');
        ini_set("max_execution_time", 0);  // on
    	$total_records = 0;
    	$report_type = 'Product: GetMyFeesEstimate';
    	$univ = new UniversalModel();
		$mkp_q= new MarketplaceAssign();
		$tries = 0;

    	if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
        {
        	echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
            exit;
        }

        $this->seller_id = trim(Input::get('seller_id'));

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

    	$w = array();
    	if( Input::get('mkp') == null OR Input::get('mkp') == "" )
        {
        	echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
			exit();
        }
        $this->mkp = trim(Input::get('mkp'));

		$where  = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);
		$mkp_assign = $mkp_q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());

		if(count($mkp_assign) > 0)
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Estimate Fees for seller'.$this->seller_id.' mkp'.$this->mkp, true));
		else
			exit();

		$time_start = time();
        $isError=false;
        $message = "Update Product Estimate Fees Cron Successfully Fetch Data!";
        $response['time_start'] = date('Y-m-d H:i:s');
        $response['total_time_of_execution'] = 0;
        $response['message'] = $message;
        $response['isError'] = false;
        $response['tries'] = 0;
        $tries=0;
        foreach ($mkp_assign as $value) {
			if($value->marketplace_id == 1){
				$mkp = config('constant.amz_keys.na.marketplaces');
				$country_key = 'na';
				$urlpref = '.com';
			}
			else if($value->marketplace_id == 2){
				$mkp = config('constant.amz_keys.eu.marketplaces');
				$country_key = 'eu';
				$urlpref = '.co.uk';
			}else{
				$country_key = '';
				$urlpref = '';
				exit();
			}

			$mkp_id = $value->marketplace_id;
			$merchantId = $value->mws_seller_id;
			$MWSAuthToken = $value->mws_auth_token;

		    foreach ($mkp as $key => $mkp_data) {
		    	$tries++;
		    	$country = $key;
		    	echo "<br><b>Country : ".$country."</b><br><br>";
		    	if (!headers_sent()) {
					header('X-Accel-Buffering: no');
				}
				$amz_conf = array(
		          'stores' =>
		              array('YourAmazonStore' =>
		                  array(
		                      'merchantId'    => $merchantId, //mkp_seller_id
		                      'MWSAuthToken'  => $MWSAuthToken,   //mkp_auth_token
		                      'marketplaceId' => $mkp_data['id'],
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
				$amz = new AmazonProductInfo($configObject);

				$container = array();

				$flag = 0;
				$x = 0;

				$products = new Product;
				$seller_id = $this->seller_id;
				$products = $products->where('seller_id', $this->seller_id)
					->where('country', $country)
					->where('price', ">", 0)
					->distinct()
					->get();


				foreach($products as $product)
				{
					$list = array();
					$list['MarketplaceId'] = $mkp_data['id'];
					$list['IdType'] = "ASIN";
					$list['IdValue'] = $product->asin;
					$list['Identifier'] = 'Request'.$x;
					$country = $product->country;
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
					$list['PriceToEstimateFees.ListingPrice.CurrencyCode'] = $curr;

					if($product->sale_price > 0)
					{
					$list['PriceToEstimateFees.ListingPrice.Amount'] = $product->sale_price;
					}
					else
					{
						$list['PriceToEstimateFees.ListingPrice.Amount'] = $product->price;
					}

					$x++;
					$container[] = $list;
				}

					$iterate = 0;
					echo "Number of ASIN's from database : ".count($container)."<br>";
					$data = array();
					$update_counter = 0;
					$update_counter2 = 0;
					while($flag<count($container)){
						$offset = 0;
						$request = array();
						while($offset < 20 AND $flag<count($container)){
							$request[] = $container[$flag];
							$offset++;
							$flag++;
						}

						if(count($request) > 0 ){
							$amz->setParemeter($request);
							$data = $amz->fetchMyFeesEstimate();
							sleep(2);

							if($data != false)
							{
								foreach($data as $fee)
								{
									$update_counter2++;
									if(isset($fee['TotalFeesEstimate'])){
										$total_records++;
										$update_counter++;
										$estimate_fee = $fee['TotalFeesEstimate']->Amount;
										$asin = $fee['IdValue'];
										$price = $fee['PriceToEstimateFees']->ListingPrice->Amount;

										 $oic = DB::connection('mysql2')->table('products')
								            ->where(function($query) use ($seller_id,$country,$asin,$price,$estimate_fee){
								            $query->where('seller_id',$seller_id);
								            $query->where('country',$country);
								            $query->where('asin', $asin);
							                $query->where(function ($query) use ($price){
								                $query->where('price',$price);
								                $query->OrWhere('sale_price',$price);
								                });
								           })
								        ->update(['estimate_fees' => $estimate_fee]);
								    }
								}
							}
						}
					}
					echo "Number of ASIN's return from Amazon : ".$update_counter2."<br>";
					echo "Number of ASIN's fees updated in the database : ".$update_counter."<br>";
					//sleep(10);

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
        $log->description = 'Update Product Estimate Fees';
        $log->date_sent = date('Y-m-d H:i:s');
        $log->subject = 'Cron Notification Update Product Estimate Fees';
        $log->api_used = $report_type;
        $log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
        $log->record_fetched = $total_records;
        $log->message = $message;
        $log->save();

        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Estimate Fees for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Estimate Fees for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
  }
}
