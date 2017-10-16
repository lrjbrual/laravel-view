<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use AmazonProductInfo;
use AmazonMWSConfig;
use App\MarketplaceAssign;
use App\Product;
use App\Log;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use App\UniversalModel;
use Mail;
use App\Seller;

class UpdateProductsPriceDatabaseController extends Controller
{
    private $seller_id;
    private $mkp='';

    public function index(){
      try{
    	ini_set('memory_limit', '-1');
        ini_set("max_execution_time", 0);  // on
    	$total_records = 0;
    	$report_type = 'Product: GetMyPriceForSKU';
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
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Prices for seller'.$this->seller_id.' mkp'.$this->mkp, true));
		else
			exit();

		$time_start = time();
        $isError=false;
        $message = "Update Product Prices Cron Successfully Fetch Data!";
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
				//getting sku from db by seller
				$products = array();
				$flag = 0;
				$products = new Product;
				$products = $products->where('seller_id', $this->seller_id)
					->where('country', $country)
					->distinct()
					->get(['asin']);
				$product = array();
				foreach ($products as $value) {
					$product[] = $value->asin;
				}
				echo "Number of ASIN's : ".count($product)."<br>";
				while($flag<count($product)){
					$offset = 0;
					$asins = array();
					while($offset < 20 AND $flag<count($product)){
						$asins[] = $product[$flag];
						$offset++;
						$flag++;
					}
					$prices = array();
					if(count($asins) > 0 ){
						$amz->setASINs($asins);
						$amz->fetchMyPrice();
						sleep(2);
					}
				}
				$prices = $amz->getProductData();
				if($prices!=false){
					foreach ($prices as $key => $value) {
						$total_records++;
						$asin = $value['Identifiers']['MarketplaceASIN']['ASIN'];
						echo "<b>ASIN : </b>".$asin."<br>";
						echo "<b>Prices : </b>";

						if(isset($value['Offers'])){
							$sale_price = !isset($value['Offers'][0]['BuyingPrice']['ListingPrice']['Amount']) ? 0 : ($value['Offers'][0]['BuyingPrice']['ListingPrice']['Amount']);
							$price = !isset($value['Offers'][0]['RegularPrice']['Amount']) ? '0' : ($value['Offers'][0]['RegularPrice']['Amount']);

							echo $price."<br>";
							echo "<b>Sales Price : </b>".$sale_price."<br>";
							if(trim($price) != ""){
								$p = new Product;
								$p = $p->where('seller_id', $this->seller_id)
									->where('country', $country)
									->where('asin', $asin)
									->update(['price'=> $price, 'sale_price' => $sale_price]);
							}
						}else{
							echo "Price Empty!<br>";
						}
					}
					echo "Total number of ASIN's from Amazon : ".$total_records."<br>";
				}
				echo "Number of ASIN's from database : ".count($product)."<br><br>";
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
        $log->description = 'Update Products Price';
        $log->date_sent = date('Y-m-d H:i:s');
        $log->subject = 'Cron Notification Update Product Prices';
        $log->api_used = $report_type;
        $log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
        $log->record_fetched = $total_records;
        $log->message = $message;
        $log->save();

        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Prices for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Update Product Prices for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
  }
}
