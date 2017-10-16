<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FulfilledShipment;
use App\MarketplaceAssign;
use App\Product;
use Illuminate\Support\Facades\Input;
use App\MWSCustomClasses\MWSFetchReportClass;
use App\Mail\CronNotification;
use App\ProductMatch;
use App\Log;
use Mail;

class UpdateProductImageController extends Controller
{
    //
    private $seller_id;
    public function index()
	{
	   try 
	   {
	    	$time_start = time();
			$isError = false;
			$response['time_start'] = date('Y-m-d H:i:s');
			$response['total_time_of_execution'] = 0;
			$response['message'] = "Update Product Image Cron Successfully Fetch Data!";
			$response['isError'] = false;
			$response['tries'] = 0;
			$tries=0;
			$message = "Update Product Image Cron Successfully Fetch Data!";

			if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
	        {
	        	echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
	            exit;
	        }
	        else
	        {
	            $this->seller_id = trim(Input::get('seller_id'));
	        }

			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Image for seller'.$this->seller_id, true));

			$countries = ['uk','es','ca','de','it','fr','us'];
			
			$productMatch = ProductMatch::where('seller_id',$this->seller_id)
										->get();
			$productMatchArray = array();
			foreach($countries as $country)
			{
				$productMatchArray[$country] = array();
			}

			if(isset($productMatch))
			{
				foreach($productMatch as $p)
				{
					$productMatchArray[$p->country][] = $p->asin;
				}
			}

	        $p = new Product;
	        $products = $p->getProductBySellerDistinctAsin($this->seller_id);

	        $productsArray = array();
	        foreach($countries as $country)
			{
				$productsArray[$country] = array();
			}

	        if(isset($products))
	        {
	        	foreach($products as $product)
	        	{
	        		if(!in_array($product->asin, $productMatchArray[$product->country]))
	        		$productsArray[$product->country][] = $product->asin;
	        	}
	        }	        

	        $mkps = array();
	    	$q= new MarketplaceAssign();
	    	$where = array('seller_id'=>$this->seller_id);
	    	$w = array('seller_id'=> $this->seller_id);

	        $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());

	        foreach($mkp_assign as $mkp)
	        {
	        	$mkps[$mkp->marketplace_id] = $mkp;
	        }

	        if(count($mkp_assign)<=0)
	        {
				$response['time_start'] = date('Y-m-d H:i:s');
				$response['time_end'] = date('Y-m-d H:i:s');
				$response['isError'] = true;
				$response['message'] = "No Marketplace assigned!";
				$message = "No Marketplace assigned!";
				$response['total_time_of_execution'] = 0;
				$response['tries'] = 0;
				$isError=true;
				echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
				$time_end = time();
		        $response['time_start'] = date('Y-m-d H:i:s', $time_start);
		        $response['time_end'] = date('Y-m-d H:i:s', $time_end);
		        $response['total_time_of_execution'] = ($time_end - $time_start)/60;
		        $response['tries'] = 1;
		        $response['total_records'] = (isset($total_records) ? $total_records : 0);
		        $response['isError'] = $isError;
		        $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
		        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Image for Seller '.$this->seller_id.' (error)', false, $response));
			}
			else
			{
				$check = 0;
				$total_records = 0;
				$start_date = null;
		        $end_date = null;

		        $hasMkp = 0;
		        foreach($countries as $country)
				{
					echo '<br>'.$country;
					if(count($productsArray[$country]) > 0)
					{
						$offsetCatch = count($productsArray[$country]);
						echo '# of products: '.$offsetCatch.'<br>';
						switch($country)
						{
							case 'us':
							case 'ca':
								if(isset($mkps[1]))
								{
									$merchantId = $mkps[1]->mws_seller_id;
									$MWSAuthToken = $mkps[1]->mws_auth_token;
									$marketPlace = config('constant.amz_keys.na.marketplaces.'.$country.'.id');
									$hasMkp = 1;
								}
								break;
							case 'de':
							case 'es':
							case 'fr':
							case 'it':
							case 'uk':
								if(isset($mkps[2]))
								{
									$merchantId = $mkps[2]->mws_seller_id;
									$MWSAuthToken = $mkps[2]->mws_auth_token;
									$marketPlace = config('constant.amz_keys.eu.marketplaces.'.$country.'.id');
									$hasMkp = 1;
								}
								break;
						}

						if($hasMkp == 0)
						{
							continue;
						}

						$init = array(
							'merchantId'    => $merchantId,
				            'MWSAuthToken'  => $MWSAuthToken,		//mkp_auth_token			//mkp_country
				            'marketPlace'	=> $marketPlace,	//seller marketplace id
				            'country'       => $country,
				            'start_date'	=> $start_date,
						    'end_date'		=> $end_date
				    		);

						$amz = new MWSFetchReportClass();
		    			$amz->initialize($init);
						$array = array();
						$x = 0;
						$y = 0;
						foreach($productsArray[$country] as $pa)
						{
							$x++;
							$y++;
							$array[] = $pa;

							if($x == 5)
							{
								$result = $amz->fetchDataProduct($array);
								foreach($result as $key => $val)
								{
									$pm = new ProductMatch;
									$pm->seller_id = $this->seller_id;
									$pm->asin = $key;
									$pm->country = $country;
									$pm->url = $val;
									$pm->save();
									$total_records++;
									//echo '<br>saving this asin = '.$key;
								}
								$array = array();
								$x = 0;
							}

							if(($offsetCatch - $y) == 0 && $x != 5)
							{
								$result = $amz->fetchDataProduct($array);
								foreach($result as $key => $val)
								{
									$pm = new ProductMatch;
									$pm->seller_id = $this->seller_id;
									$pm->asin = $key;
									$pm->country = $country;
									$pm->url = $val;
									$pm->save();
									$total_records++;
									echo '<br>saving this asin offset = '.$key;
								}
							}
								echo '<br>'.$y;
								
						}
					}
				$check++;
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
		$log->description = 'Product Image';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for Product Image';
		$log->api_used = 'GetMatchingProductForId ';
		$log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
		$log->record_fetched = $total_records;
		$log->message = $message;
		$log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Image for seller'.$this->seller_id, false, $response));
		}
		 catch (\Exception $e) 
		 {
	      $time_end = time();
	      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
	      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
	      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
	      $response['tries'] = 1;
	      $response['total_records'] = (isset($total_records) ? $total_records : 0);
	      $response['isError'] = $isError;
	      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
	      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Image for seller'.$this->seller_id, false, $response));
	    }
	}
}
