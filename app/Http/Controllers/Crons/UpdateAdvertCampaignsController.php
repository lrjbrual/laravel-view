<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Trendle\AdsRecommendationController;
use Carbon\Carbon;
use App\MWSCustomClasses\MWSCurlAdvertisingClass;
use App\AmazonSellerDetail;
use App\Seller;
use App\AdsCampaign;
use App\AdsCampaignAdGroup;
use App\AdsCampaignKeyword;
use App\AdsCampaignProduct;
use App\AdsCampaignReportId;
use App\AdsCampaignReportByKeyword;
use App\CampaignAdvertising;
use App\UniversalModel;
use App\CampaignAdsRecommendation;
use App\CampaignAdsRecommendationCondition;
use Illuminate\Support\Facades\Input;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Log;
use App\Mail\CronNotification;
use Mail;
use Product;
use File;


class UpdateAdvertCampaignsController extends Controller
{
	private $seller_id;
	private $mkp;
	private $mkp_code;

	private function is_country_token_expired($country){
	    $amz = new MWSCurlAdvertisingClass;
		$profile = AmazonSellerDetail::where('seller_id', $this->seller_id)
       		->where('mkp_id', $this->mkp)->where('amz_country_code', strtoupper($country))
       		->get()->first();
       	if(isset($profile)){
	       	$current = date_create(Carbon::now());
	   		$expiry_date = date_create($profile->amz_expires_in);
	   		$refresh_token = $profile->amz_refresh_token;
	   		if($current >= $expiry_date){
	   			echo "Access token is expired. Requesting new access token...... ";
	   			$tokens = $amz->refresh_tokens($refresh_token);
				$data = ['amz_access_token'=>$tokens->access_token,
				      'amz_expires_in'=>Carbon::now()->addHour(),
				      'updated_at'=>Carbon::now(),
				      'amz_refresh_token'=>$tokens->refresh_token];
				$update = AmazonSellerDetail::where('seller_id', $this->seller_id)->update($data);
	   			echo "<b>DONE!!</b><br>";
	   			$profile = AmazonSellerDetail::where('seller_id', $this->seller_id)->where('mkp_id', $this->mkp)->where('amz_country_code', $country)->get()->first();
	   		}
	   		return $profile;
	   	}else{
	   		return false;
	   	}
	}

    public function index(){
    	try{
	    	ini_set('memory_limit', '-1');
	        ini_set("max_execution_time", 0);  // on
	    	$univ = new UniversalModel();
	    	$amz = new MWSCurlAdvertisingClass;
	    	$total_records = 0;
	    	$time_start = time();

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
	       	$seller_profile = AmazonSellerDetail::where('seller_id', $this->seller_id)
	       		->where('mkp_id', $this->mkp)
	       		->get();

	       	if(count($seller_profile) == 0){
	       		echo "<p style='color:red;'><b>Seller Must Login Via amazon in the Marketplace Page or Advertising Page!</b></p>";
	            exit;
	       	}


	        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Advertisings New API for seller'.$this->seller_id.' mkp'.$this->mkp.' Server '.env('APP_ENV', ''), true));

	       	$isExpired = true;
	       	$countries = array();
	       	foreach ($seller_profile as $profile) {
	       		$country = strtolower($profile->amz_country_code);
	       		$countries[] = $country;
	       		//check if access token is expired
	       		$profile = $this->is_country_token_expired($country);
	       		$refresh_token = $profile->amz_refresh_token;
	       		$access_token = $profile->amz_access_token;
	       		$profile_id = $profile->amz_profile_id;

	       		//get campaign list
	       		$condition = ['startIndex' => 0];
	       		$campaigns = $amz->get_campaigns($access_token, $profile_id, $this->mkp_code, $condition);
		   		echo "Extracting ".count($campaigns)." Campaigns on ".$country.".<br>";
		   		$isEmpty = new AdsCampaign;
		   		$isEmpty = $isEmpty->where('seller_id', $this->seller_id)->where('country', $country)->take(1)->get();
		   		if(count($isEmpty) > 0){
		   			$isEmpty = false;
		   		}else{
		   			$isEmpty = true;
		   		}
		   		if(count($campaigns) > 0){
		       		foreach ($campaigns as $campaign) {
		       			$total_records++;
		       			if($isEmpty){
			   				$this->save_campaign($campaign, $country);
			       		}else{
			   				$this->update_campaign($campaign, $country);
			       		}
			       		unset($campaign);
		       		}
		       	}
	       		$campaigns = array();
	       		echo "memory usage : ".memory_get_peak_usage()." bytes<br>";

	       		// check again if access token is expired
	       		$profile = $this->is_country_token_expired($country);
	       		$refresh_token = $profile->amz_refresh_token;
	       		$access_token = $profile->amz_access_token;
	       		$profile_id = $profile->amz_profile_id;

	       		//get campaigns adgroup list
	       		$condition = ['startIndex' => 0];
	       		$adgroups = $amz->get_adGroups($access_token, $profile_id, $this->mkp_code, $condition);
		   		echo "Extracting ".count($adgroups)." Campaign adGroups on ".$country.".<br>";
		   		$isEmpty = new AdsCampaignAdGroup;
		   		$isEmpty = $isEmpty->where('seller_id', $this->seller_id)->where('country', $country)->take(1)->get();
		   		if(count($isEmpty) > 0){
		   			$isEmpty = false;
		   		}else{
		   			$isEmpty = true;
		   		}
		   		if( count($adgroups) > 0 ){
		       		foreach ($adgroups as $adgroup) {
		       			$total_records++;
		       			if( $isEmpty ){
			       			$this->save_adgroup($adgroup, $country);
			       		}else{
		   					$this->update_adgroup($adgroup, $country);
			       		}
			       		unset($adgroup);
		       		}
		       	}
		       	echo "memory usage : ".memory_get_peak_usage()." bytes<br>";
	       		$adgroups = array();

	       		// check again if access token is expired
	       		$profile = $this->is_country_token_expired($country);
	       		$refresh_token = $profile->amz_refresh_token;
	       		$access_token = $profile->amz_access_token;
	       		$profile_id = $profile->amz_profile_id;



		       	//get campaign ProductAd list
	       		$flag = 0;
	       		$repeat = true;
	       		$isEmpty = new AdsCampaignProduct;
		   		$isEmpty = $isEmpty->where('seller_id', $this->seller_id)->where('country', $country)->take(1)->get();
	       		while($repeat){
		       		$condition = ['startIndex' => $flag];
		       		$prods = $amz->get_productAds($access_token, $profile_id, $this->mkp_code, $condition);
		       		echo "Extracting ".count($prods)." Campaign Product Ads on ".$country.".<br>";
		       		if(count($prods) >= 5000) $flag += 5000;
		       		else $repeat = false;
		       		echo "memory usage : ".memory_get_peak_usage()." bytes<br>";
		       		if( count($prods) > 0 ){
			       		foreach ($prods as $prod) {
		       				$total_records++;
			       			if( !(count($isEmpty) > 0) ){
				       			$this->save_campaign_product($prod, $country);
				       		}else{
				   				$this->update_campaign_product($prod, $country);
				       		}
				       		unset($prod);
			       		}
			       	}
		       	}
		       	echo "memory usage : ".memory_get_peak_usage()." bytes<br>";
				$prods = array();

	       		// check again if access token is expired
	       		$profile = $this->is_country_token_expired($country);
	       		$refresh_token = $profile->amz_refresh_token;
	       		$access_token = $profile->amz_access_token;
	       		$profile_id = $profile->amz_profile_id;

	       		//get campaign adgroups keyword list
	       		$flag = 0;
	       		$repeat = true;
	       		$isEmpty = new AdsCampaignKeyword;
		   		$isEmpty = $isEmpty->where('seller_id', $this->seller_id)->where('country', $country)->take(1)->get();
	       		while($repeat){
		       		$condition = ['startIndex' => $flag];
		       		$keywords = $amz->get_keywords($access_token, $profile_id, $this->mkp_code, $condition);
		       		echo "Extracting ".count($keywords)." Campaign AdGroup Keywords on ".$country.".<br>";
		       		if(count($keywords) >= 5000) $flag += 5000;
		       		else $repeat = false;
		       		if( count($keywords) > 0 ){
			       		foreach ($keywords as $keyword) {
		       				$total_records++;
			       			if( !(count($isEmpty) > 0) ){
				       			$this->save_campaign_keyword($keyword, $country);
				       		}else{
				   				$this->update_campaign_keyword($keyword, $country);
				       		}
				       		unset($keyword);
			       		}
			       	}
		       	}
		       	echo "memory usage : ".memory_get_peak_usage()." bytes<br>";
		       	$keywords = array();

	       		// check again if access token is expired
	       		$profile = $this->is_country_token_expired($country);
	       		$refresh_token = $profile->amz_refresh_token;
	       		$access_token = $profile->amz_access_token;
	       		$profile_id = $profile->amz_profile_id;

		       	//get campaign adgroups keyword negative list
	       		$flag = 0;
	       		$repeat = true;
	       		while($repeat){
		       		$condition = ['startIndex' => $flag];
		       		$keywords = $amz->get_adgroup_negativeKeywords($access_token, $profile_id, $this->mkp_code, $condition);
		       		echo "Extracting ".count($keywords)." Campaign AdGroup Negative Keywords on ".$country.".<br>";
		       		if(count($keywords) >= 5000) $flag += 5000;
		       		else $repeat = false;
		       		if( count($keywords) > 0 ){
			       		foreach ($keywords as $keyword) {
		       				$total_records++;
			       			if( !(count($isEmpty) > 0) ){
				       			$this->save_campaign_keyword($keyword, $country);
				       		}else{
				   				$this->update_campaign_keyword($keyword, $country);
				       		}
				       		unset($keyword);
			       		}
			       	}
		       	}
		       	echo "memory usage : ".memory_get_peak_usage()." bytes<br>";
		       	$keywords = array();

	       		// check again if access token is expired
	       		$profile = $this->is_country_token_expired($country);
	       		$refresh_token = $profile->amz_refresh_token;
	       		$access_token = $profile->amz_access_token;
	       		$profile_id = $profile->amz_profile_id;

		       	//get campaign negative keyword list
	       		$flag = 0;
	       		$repeat = true;
	       		while($repeat){
		       		$condition = ['startIndex' => $flag];
		       		$keywords = $amz->get_campaign_negativeKeywords($access_token, $profile_id, $this->mkp_code, $condition);
		       		echo "Extracting ".count($keywords)." Campaign AdGroup Negative Keywords on ".$country.".<br>";
		       		if(count($keywords) >= 5000) $flag += 5000;
		       		else $repeat = false;
		       		if( count($keywords) > 0 ){
			       		foreach ($keywords as $keyword) {
		       				$total_records++;
			       			if( !(count($isEmpty) > 0) ){
				       			$this->save_campaign_keyword($keyword, $country);
				       		}else{
				   				$this->update_campaign_keyword($keyword, $country);
				       		}
				       		unset($keyword);
			       		}
			       	}
		       	}
		       	echo "memory usage : ".memory_get_peak_usage()." bytes<br>";
		       	$keywords = array();

		       	// getting the transaction clicks and impressions and etc.
		       	// check if first run or next run
		       	$trans = AdsCampaignReportId::where('seller_id', $this->seller_id)
		       		->where('country', $country)
		       		->take(10)->get();

		       	$start_date = Carbon::today()->addDay();
		       	if(count($trans) > 0){
		       	//if(true){
		       		//next run
		       		$stop_date = Carbon::today()->subDays(29);
	                $isEmpty = false;
		       	}else{
		       		//first run
		       		$stop_date = Carbon::today()->subDays(58);
	                $isEmpty = true;
		       	}
		       	// requesting report ids
		       	echo "Requesting Report ID's.. ";
		       	$report_id = array();
		       	while($start_date>$stop_date) {

		       		// check again if access token is expired
		       		$profile = $this->is_country_token_expired($country);
		       		$refresh_token = $profile->amz_refresh_token;
		       		$access_token = $profile->amz_access_token;
		       		$profile_id = $profile->amz_profile_id;

		       		$reportDate = (string)$stop_date;
		       		$reportDate_index = $reportDate;
		       		$reportDate = str_replace('-', '', $reportDate);
		       		$reportDate = explode(' ', $reportDate);
		       		$reportDate = $reportDate[0];
		       		//cost divide by clicks is equals average_cpc
		       		$cond = ['campaignType'=>'sponsoredProducts',
		       				'segment' => 'query',
		       				'reportDate' => (string)$reportDate,
		       				//'metrics' => 'impressions,clicks,cost'
		       				'metrics' => 'impressions,clicks,cost,attributedConversions1dSameSKU,attributedConversions1dSameSKU,attributedConversions1d,attributedSales1dSameSKU,attributedSales1d,attributedConversions7dSameSKU,attributedConversions7d,attributedSales7dSameSKU,attributedSales7d,attributedConversions30dSameSKU,attributedConversions30d,attributedSales30dSameSKU,attributedSales30d'
		       				];
		       		$rep = $amz->get_report($access_token, $profile_id, $this->mkp_code, 'keywords', $cond);

		       		if(isset($rep->reportId))
		       			$report_id[$reportDate_index] = $rep->reportId;
		       		$stop_date->addDay();
		       		//sleep(10);
	            }
	            sleep(10);
	            echo "DONE!!.. <br>";
	            echo "Parsing report IDs...<br>";
	            foreach ($report_id as $key => $id) {

		       		// check again if access token is expired
		       		$profile = $this->is_country_token_expired($country);
		       		$refresh_token = $profile->amz_refresh_token;
		       		$access_token = $profile->amz_access_token;
		       		$profile_id = $profile->amz_profile_id;

	            	echo "Date : ".$key." Report ID : ".$id."...";
	            	$status = "IN_PROGRESS";
	            	$report = array();
	            	$flg = 0;
	            	while ($status == "IN_PROGRESS") {
	            		$report = $amz->get_report_by_reportid($access_token, $profile_id, $this->mkp_code, $id);
	            		if( !isset($report->status) ){ $flg++; sleep(20); }
	            		else if($report->status == 'IN_PROGRESS') sleep(20);
	            		else if($report->status == 'FAILURE') $status = "FAILURE";
	            		else $status = "SUCCESS";

	            		if($flg == 5) $status = "FAILURE";
	            	}
	            	if($status == 'FAILURE') continue;

	            	$ads_id = new AdsCampaignReportId;
	            	$ads_id->seller_id = $this->seller_id;
	            	$ads_id->country = $country;
	            	$ads_id->mkp_id = $this->mkp;
	            	$ads_id->report_id = $id;
	            	$ads_id->report_url = $report->location;
	            	$ads_id->posted_date = $key;
	            	$ads_id->is_new = true;
	            	$ads_id->save();
	            	echo "<br>Report Location: ".$report->location;
	            	// $report = $amz->get_report_data_by_reporturl($access_token,$profile_id, $report->location);
	            	echo "<br><br>";
	            }

	            echo "memory usage : ".memory_get_peak_usage()." bytes<br>";

	        	echo "<b>DONE!!!</b><br><br>";
	        	ob_flush();
	        	flush();
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
	        $log->description = 'Campaign Advertisings New API';
	        $log->date_sent = date('Y-m-d H:i:s');
	        $log->subject = 'Cron Notification for Campaign Advertisings New API';
	        $log->api_used = 'NONE';
	        $log->start_time = $response['time_start'];
	        $log->end_sent = date('Y-m-d H:i:s');
	        $log->record_fetched = $total_records;
	        $log->message = "SUCCESS!!";
	        $log->save();

	        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Advertisings New API for seller'.$this->seller_id.' mkp'.$this->mkp.' Server '.env('APP_ENV', ''), false, $response));

	        // run if first run
	        $isEmpty = CampaignAdvertising::where('seller_id', (string)$this->seller_id)
	        	->whereIn('country',$countries)->take(1)->get();
       		if( !(count($isEmpty) > 0) ){
       			exec("curl '" . config('app.url') . "/ExtractAdvertCampaigns?seller_id=" . $this->seller_id . "&mkp=".$this->mkp."' > /dev/null 2>&1 & echo $!",$output);
       		}

        } catch (\Exception $e) {
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
	        $log->description = 'Campaign Advertisings New API';
	        $log->date_sent = date('Y-m-d H:i:s');
	        $log->subject = 'Cron Notification for Campaign Advertisings New API';
	        $log->api_used = 'NONE';
	        $log->start_time = $response['time_start'];
	        $log->end_sent = date('Y-m-d H:i:s');
	        $log->record_fetched = (isset($total_records) ? $total_records : 0);
	        $log->message = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
	        $log->save();

	        echo "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();

			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Advertisings New API for seller'.$this->seller_id.' mkp'.$this->mkp.' Server '.env('APP_ENV', '').' (error)', false, $response));
        }
    }

    public function extract_reports(){
    	try{
	    	ini_set('memory_limit', '-1');
	        ini_set("max_execution_time", -1);  // on
	        set_time_limit ( -1 );
	    	$univ = new UniversalModel();
	    	$amz = new MWSCurlAdvertisingClass;
	    	$total_data = 0;
	    	$time_start = time();
	    	echo "Start Time: ".date('Y-m-d H:i:s')."<br>";

	    	if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
	        {
	        	echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
	            exit;
	        }

	        $this->seller_id = trim(Input::get('seller_id'));

	    	$w = array();
	    	if( Input::get('mkp') == null OR Input::get('mkp') == "" )
	        {
	        	echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
				exit();
	        }
	        $this->mkp = trim(Input::get('mkp'));
	        if($this->mkp == 1) $this->mkp_code = 'na';
	        else $this->mkp_code = 'eu';
	       	$seller_profile = AmazonSellerDetail::where('seller_id', $this->seller_id)
	       		->where('mkp_id', $this->mkp)
	       		->get();

	       	if(count($seller_profile) == 0){
	       		echo "<p style='color:red;'><b>Seller Must Login Via amazon in the Marketplace Page or Advertising Page!</b></p>";
	            exit;
	       	}

	        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Extract Campaign Advertisings New API Reports for seller'.$this->seller_id.' mkp'.$this->mkp.' Server '.env('APP_ENV', ''), true));

	       	$report_urls = AdsCampaignReportId::where('seller_id', $this->seller_id)
	       		->where('is_new', 1)->where('mkp_id', $this->mkp)->orderBy('created_at')->get();
	       	foreach ($report_urls as $url) {
	       		$profile = AmazonSellerDetail::where('seller_id', (string)$this->seller_id)
	       		->where('mkp_id', $this->mkp)->where('amz_country_code', strtoupper($url->country))
	       		->get()->first();

	       		$isEmpty = $q = CampaignAdvertising::where('seller_id', (string)$this->seller_id)
	       			->where('posted_date', $url->posted_date)->where('country', $url->country)->take(1)->get();
	       		if(count($isEmpty) > 0){
	       			$isEmpty = false;
	       		}else{
	       			$isEmpty = true;
	       		}

	       		$current = date_create(Carbon::now());
	       		$expiry_date = date_create($profile->amz_expires_in);
	       		$refresh_token = $profile->amz_refresh_token;
	       		$access_token = $profile->amz_access_token;
	       		$profile_id = $profile->amz_profile_id;
	       		if($current >= $expiry_date){
	       			echo "Access token is expired. Requesting new access token...... ";
	       			$tokens = $amz->refresh_tokens($refresh_token);
					$data = ['amz_access_token'=>$tokens->access_token,
					      'amz_expires_in'=>Carbon::now()->addHour(),
					      'updated_at'=>Carbon::now(),
					      'amz_refresh_token'=>$tokens->refresh_token];
					$update = new AmazonSellerDetail;
					$update = $update->where('seller_id', $this->seller_id)->update($data);
	       			$refresh_token = $tokens->refresh_token;
	       			$access_token = $tokens->access_token;
	       			echo "<b>DONE!!</b><br>";
	       		}

	       		echo "Extracting data from Country : ".$url->country." Date: ".$url->posted_date."<br>Report URL : ".$url->report_url;
	       		ob_flush();
	        	flush();
	       		$report = $amz->get_report_data_by_reporturl($access_token,$profile_id, $url->report_url);
	       		echo "<b>Done!!!!</b><br>";
	       		echo "Saving ".count($report)." of data to database .......";
	       		ob_flush();
	        	flush();
	        	if(count($report) > 0){
	        		$keywordid_flag = '';
		       		$campaign = array();
		       		$headers = 'seller_id, campaign_name, ad_group_name, type, keyword, currency, country, customer_search_term, match_type, impressions, clicks, ctr, total_spend, average_cpc, acos, posted_date, created_at, updated_at, keyword_id, ads_campaign_report_by_keywords_id, campaignid, adgroupid, attributedconversions1dsamesku, attributedconversions1d, attributedsales1dsamesku, attributedsales1d, attributedconversions7dsamesku, attributedconversions7d, attributedsales7dsamesku, attributedsales7d, attributedconversions30dsamesku, attributedconversions30d, attributedsales30dsamesku, attributedsales30d, bid';
		       		$file_name = "app/ads_ajax_files/temp_".time()."_".$this->seller_id."_".$url->country.".csv";
		       		$table_name = "temp_".time()."_".$this->seller_id."_".$url->country;
		       		$data_arr = array();
		       		//$file = fopen($file_name,"wb");
		       		//fputcsv($file, explode(', ', $headers));
		       		foreach ($report as $d) {
		       			$currency = config('constant.currency_list.'.strtolower($url->country));
		       			$keywordid = $d['keywordId'];
		       			$data = [
		       				'seller_id' => $this->seller_id,
		       				'country' => $url->country,
		       				'keyword_id' => $d['keywordId'],
		       				'query' => $d['query'],
		       				'impressions'=>$d['impressions'],
		       				'clicks'=>$d['clicks'],
		       				'cost'=>$d['cost'],
		       				'attributedconversions1dsamesku'=>$d['attributedConversions1dSameSKU'],
		       				'attributedconversions1d'=>$d['attributedConversions1d'],
		       				'attributedsales1dsamesku'=>$d['attributedSales1dSameSKU'],
		       				'attributedsales1d'=>$d['attributedSales1d'],
		       				'attributedconversions7dsamesku'=>$d['attributedConversions7dSameSKU'],
		       				'attributedconversions7d'=>$d['attributedConversions7d'],
		       				'attributedsales7dsamesku'=>$d['attributedSales7dSameSKU'],
		       				'attributedsales7d'=>$d['attributedSales7d'],
		       				'attributedconversions30dsamesku'=>$d['attributedConversions30dSameSKU'],
		       				'attributedconversions30d'=>$d['attributedConversions30d'],
		       				'attributedsales30dsamesku'=>$d['attributedSales30dSameSKU'],
		       				'attributedsales30d'=>$d['attributedSales30d'],
		       				'posted_date'=>$url->posted_date
		       			];
		       			if($keywordid_flag != $keywordid){
		       				$keywordid_flag = $keywordid;
			       			$campaign = DB::connection('mysql2')->select( DB::raw("SELECT b.keywordtext, b.matchtype, b.state, b.bid, c.defaultbid as defaultbid, c.name AS adgroupname, c.state, d.name AS campaignname, d.campaigntype, d.targetingtype, d.state, b.adgroupid, b.campaignid FROM ads_campaign_keywords AS b JOIN ads_campaign_ad_groups AS c ON b.adgroupid=c.adgroupid JOIN ads_campaigns AS d ON d.campaignid=b.campaignid WHERE b.keywordid = :keywordid AND b.country = :country"), array(
								'keywordid' => (string)$keywordid,
								'country' => $url->country
								)
			       			);
			       		}

		       			$data2 = $data;
		       			unset($data2['cost']);
		       			unset($data2['query']);
		       			$data2['customer_search_term'] = $data['query'];
		       			$data2['total_spend'] = $data['cost'];
		       			$data2['ctr'] = ($data['impressions'] == 0) ? 0 : round(($data['clicks']/$data['impressions'])*100,2);
		       			$data2['acos'] = ($data['attributedsales30d'] == 0) ? 0 : round(($data['cost']/$data['attributedsales30d'])*100,2);
		       			$data2['average_cpc'] = ($data['clicks'] == 0) ? 0 : round(($data['cost']/$data['clicks']),2);
		       			$total_data++;
			       			$data2['campaignid'] = (!isset($campaign[0]->campaignid)) ? "" : $campaign[0]->campaignid;
			       			$data2['adgroupid'] = (!isset($campaign[0]->adgroupid)) ? "" : $campaign[0]->adgroupid;
			       			$data2['campaign_name'] = (!isset($campaign[0]->campaignname)) ? "" : $campaign[0]->campaignname;
			       			$data2['ad_group_name'] = (!isset($campaign[0]->adgroupname)) ? "" : $campaign[0]->adgroupname;
			       			$data2['type'] = (!isset($campaign[0]->targetingtype)) ? "" : strtoupper($campaign[0]->targetingtype);
			       			$data2['keyword'] = (!isset($campaign[0]->keywordtext)) ? "" : $campaign[0]->keywordtext;
			       			$data2['currency'] = $currency;
			       			$data2['match_type'] = (!isset($campaign[0]->matchtype)) ? "" : $campaign[0]->matchtype;
			       			if($data2['type'] == 'MANUAL')
								$data2['bid'] = (!isset($campaign[0]->bid)) ? "" : $campaign[0]->bid;
							else
								$data2['bid'] = (!isset($campaign[0]->defaultbid)) ? "" : $campaign[0]->defaultbid;
			       			$data2['ads_campaign_report_by_keywords_id'] = 0;
			       			$data2['created_at'] = date('Y-m-d H:i:s');
			       			$data2['updated_at'] = date('Y-m-d H:i:s');
			       		$arr = array();
			       		$arr['seller_id'] = $this->seller_id;
			       		$arr['campaign_name'] = $data2['campaign_name'];
			       		$arr['ad_group_name'] = $data2['ad_group_name'];
			       		$arr['type'] = $data2['type'];
			       		$arr['keyword'] = $data2['keyword'];
			       		$arr['currency'] = $data2['currency'];
			       		$arr['country'] = $data2['country'];
			       		$arr['customer_search_term'] = $data2['customer_search_term'];
			       		$arr['match_type'] = $data2['match_type'];
			       		$arr['impressions'] = $data2['impressions'];
			       		$arr['clicks'] = $data2['clicks'];
			       		$arr['ctr'] = $data2['ctr'];
			       		$arr['total_spend'] = $data2['total_spend'];
			       		$arr['average_cpc'] = $data2['average_cpc'];
			       		$arr['acos'] = $data2['acos'];
			       		$arr['posted_date'] = $data2['posted_date'];
			       		$arr['created_at'] = $data2['created_at'];
			       		$arr['updated_at'] = $data2['updated_at'];
			       		$arr['keyword_id'] = $data2['keyword_id'];
			       		$arr['ads_campaign_report_by_keywords_id'] = $data2['ads_campaign_report_by_keywords_id'];
			       		$arr['campaignid'] = $data2['campaignid'];
			       		$arr['adgroupid'] = $data2['adgroupid'];
			       		$arr['attributedconversions1dsamesku'] = $data2['attributedconversions1dsamesku'];
			       		$arr['attributedconversions1d'] = $data2['attributedconversions1d'];
			       		$arr['attributedsales1dsamesku'] = $data2['attributedsales1dsamesku'];
			       		$arr['attributedsales1d'] = $data2['attributedsales1d'];
			       		$arr['attributedconversions7dsamesku'] = $data2['attributedconversions7dsamesku'];
			       		$arr['attributedconversions7d'] = $data2['attributedconversions7d'];
			       		$arr['attributedsales7dsamesku'] = $data2['attributedsales7dsamesku'];
			       		$arr['attributedsales7d'] = $data2['attributedsales7d'];
			       		$arr['attributedconversions30dsamesku'] = $data2['attributedconversions30dsamesku'];
			       		$arr['attributedconversions30d'] = $data2['attributedconversions30d'];
			       		$arr['attributedsales30dsamesku'] = $data2['attributedsales30dsamesku'];
			       		$arr['attributedsales30d'] = $data2['attributedsales30d'];
			       		$arr['bid'] = $data2['bid'];
			       		//fputcsv($file, $arr);
			       		$data_arr[] = $arr;
			       	}
			       	//fclose($file);
			       	if($isEmpty){
			       		$records = array_chunk($data_arr, 1000);
						foreach ($records as $batch) {
						    //$ret = CampaignAdvertising::insert($batch);
						    $string = "INSERT INTO campaign_advertisings (seller_id, campaign_name, ad_group_name, type, keyword, currency, country, customer_search_term, match_type, impressions, clicks, ctr, total_spend, average_cpc, acos, posted_date, created_at, updated_at, keyword_id, ads_campaign_report_by_keywords_id, campaignid, adgroupid, attributedconversions1dsamesku, attributedconversions1d, attributedsales1dsamesku, attributedsales1d, attributedconversions7dsamesku, attributedconversions7d, attributedsales7dsamesku, attributedsales7d, attributedconversions30dsamesku, attributedconversions30d, attributedsales30dsamesku, attributedsales30d, bid) values";
						    $ss = 0;
						    $s="";
						    foreach ($batch as $key => $value) {
						    	$ss++;
						    	$flg = implode('||||||', $value);
						    	if(strpos($flg, "'") === false AND strpos($flg, '"') === false){
						    		$ssss ="('".implode("', '", $value)."') ";
						    		$ssss = str_replace('\\', '//', $ssss);
						    		$s .= str_replace("\\'", "//'", $ssss);
						    	}else{
						    		$flg = str_replace("'", ' ~', $flg);
						    		$flg = str_replace('"', ' ~', $flg);
						    		$flg = explode('||||||', $flg);
						    		$ssss ="('".implode("', '", $flg)."') ";
						    		$ssss = str_replace('\\', '//', $ssss);
						    		$s .= str_replace("\\'", "//'", $ssss);
						    		// $ssss ="(\"".implode("\", \"", $value)."\") ";
						    		// $s .= str_replace('\\"', '//"', $ssss);
						    	}
						    	if($ss < (count($batch))) $s .= ", ";
						    }
						    $string .= $s;
						    $string .= " ON DUPLICATE KEY UPDATE impressions=VALUES(impressions), clicks=VALUES(clicks), ctr=VALUES(ctr), total_spend=VALUES(total_spend), average_cpc=VALUES(average_cpc), acos=VALUES(acos), attributedconversions1dsamesku=VALUES(attributedconversions1dsamesku), attributedconversions1d=VALUES(attributedconversions1d), attributedsales1dsamesku=VALUES(attributedsales1dsamesku), attributedsales1d=VALUES(attributedsales1d), attributedconversions7dsamesku=VALUES(attributedconversions7dsamesku), attributedconversions7d=VALUES(attributedconversions7d), attributedsales7dsamesku=VALUES(attributedsales7dsamesku), attributedsales7d=VALUES(attributedsales7d), attributedconversions30dsamesku=VALUES(attributedconversions30dsamesku), attributedconversions30d=VALUES(attributedconversions30d), attributedsales30dsamesku=VALUES(attributedsales30dsamesku), attributedsales30d=VALUES(attributedsales30d), bid=VALUES(bid)";
						   	//echo $string;
						   	DB::connection('mysql2')->getpdo()->exec($string);
						}

			       	}else{
		       			DB::connection('mysql2')->getpdo()->exec(
						    'CREATE TABLE '.$table_name.' LIKE campaign_advertisings'
						);

			       		$records = array_chunk($data_arr, 1000);
						foreach ($records as $batch) {
						    //$ret = DB::connection('mysql2')->table($table_name)->insert($batch);
						    $string = "INSERT INTO ".$table_name." (seller_id, campaign_name, ad_group_name, type, keyword, currency, country, customer_search_term, match_type, impressions, clicks, ctr, total_spend, average_cpc, acos, posted_date, created_at, updated_at, keyword_id, ads_campaign_report_by_keywords_id, campaignid, adgroupid, attributedconversions1dsamesku, attributedconversions1d, attributedsales1dsamesku, attributedsales1d, attributedconversions7dsamesku, attributedconversions7d, attributedsales7dsamesku, attributedsales7d, attributedconversions30dsamesku, attributedconversions30d, attributedsales30dsamesku, attributedsales30d, bid) values";
						    $ss = 0;
						    $s="";
						    foreach ($batch as $key => $value) {
						    	$ss++;
						    	$flg = implode('||||||', $value);
						    	if(strpos($flg, "'") === false AND strpos($flg, '"') === false){
						    		$ssss ="('".implode("', '", $value)."') ";
						    		$ssss = str_replace('\\', '//', $ssss);
						    		$s .= str_replace("\\'", "//'", $ssss);
						    	}else{
						    		$flg = str_replace("'", ' ~', $flg);
						    		$flg = str_replace('"', ' ~', $flg);
						    		$flg = explode('||||||', $flg);
						    		$ssss ="('".implode("', '", $flg)."') ";
						    		$ssss = str_replace('\\', '//', $ssss);
						    		$s .= str_replace("\\'", "//'", $ssss);
						    		// $ssss ="(\"".implode("\", \"", $value)."\") ";
						    		// $s .= str_replace('\\"', '//"', $ssss);
						    	}
						    	if($ss < (count($batch))) $s .= ", ";
						    }
						    $string .= $s;
						    $string .= " ON DUPLICATE KEY UPDATE impressions=VALUES(impressions), clicks=VALUES(clicks), ctr=VALUES(ctr), total_spend=VALUES(total_spend), average_cpc=VALUES(average_cpc), acos=VALUES(acos), attributedconversions1dsamesku=VALUES(attributedconversions1dsamesku), attributedconversions1d=VALUES(attributedconversions1d), attributedsales1dsamesku=VALUES(attributedsales1dsamesku), attributedsales1d=VALUES(attributedsales1d), attributedconversions7dsamesku=VALUES(attributedconversions7dsamesku), attributedconversions7d=VALUES(attributedconversions7d), attributedsales7dsamesku=VALUES(attributedsales7dsamesku), attributedsales7d=VALUES(attributedsales7d), attributedconversions30dsamesku=VALUES(attributedconversions30dsamesku), attributedconversions30d=VALUES(attributedconversions30d), attributedsales30dsamesku=VALUES(attributedsales30dsamesku), attributedsales30d=VALUES(attributedsales30d), bid=VALUES(bid)";
						   	//echo $string;
						   	DB::connection('mysql2')->getpdo()->exec($string);
						}

						// DB::connection('mysql2')->getpdo()->exec("LOAD DATA  INFILE '".url('/')."/".$file_name."'  INTO TABLE ".$table_name." FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\r\\n' IGNORE 1 LINES (seller_id, campaign_name, ad_group_name, type, keyword, currency, country, customer_search_term, match_type, impressions, clicks, ctr, total_spend, average_cpc, acos, posted_date, created_at, updated_at, keyword_id, ads_campaign_report_by_keywords_id, campaignid, adgroupid, attributedconversions1dsamesku, attributedconversions1d, attributedsales1dsamesku, attributedsales1d, attributedconversions7dsamesku, attributedconversions7d, attributedsales7dsamesku, attributedsales7d, attributedconversions30dsamesku, attributedconversions30d, attributedsales30dsamesku, attributedsales30d)");

						DB::connection('mysql2')->getpdo()->exec("INSERT INTO campaign_advertisings (seller_id, campaign_name, ad_group_name, type, keyword, currency, country, customer_search_term, match_type, impressions, clicks, ctr, total_spend, average_cpc, acos, posted_date, created_at, updated_at, keyword_id, ads_campaign_report_by_keywords_id, campaignid, adgroupid, attributedconversions1dsamesku, attributedconversions1d, attributedsales1dsamesku, attributedsales1d, attributedconversions7dsamesku, attributedconversions7d, attributedsales7dsamesku, attributedsales7d, attributedconversions30dsamesku, attributedconversions30d, attributedsales30dsamesku, attributedsales30d, bid) SELECT seller_id, campaign_name, ad_group_name, type, keyword, currency, country, customer_search_term, match_type, impressions, clicks, ctr, total_spend, average_cpc, acos, posted_date, created_at, updated_at, keyword_id, ads_campaign_report_by_keywords_id, campaignid, adgroupid, attributedconversions1dsamesku, attributedconversions1d, attributedsales1dsamesku, attributedsales1d, attributedconversions7dsamesku, attributedconversions7d, attributedsales7dsamesku, attributedsales7d, attributedconversions30dsamesku, attributedconversions30d, attributedsales30dsamesku, attributedsales30d, bid FROM ".$table_name." ON DUPLICATE KEY UPDATE impressions=VALUES(impressions), clicks=VALUES(clicks), ctr=VALUES(ctr), total_spend=VALUES(total_spend), average_cpc=VALUES(average_cpc), acos=VALUES(acos), attributedconversions1dsamesku=VALUES(attributedconversions1dsamesku), attributedconversions1d=VALUES(attributedconversions1d), attributedsales1dsamesku=VALUES(attributedsales1dsamesku), attributedsales1d=VALUES(attributedsales1d), attributedconversions7dsamesku=VALUES(attributedconversions7dsamesku), attributedconversions7d=VALUES(attributedconversions7d), attributedsales7dsamesku=VALUES(attributedsales7dsamesku), attributedsales7d=VALUES(attributedsales7d), attributedconversions30dsamesku=VALUES(attributedconversions30dsamesku), attributedconversions30d=VALUES(attributedconversions30d), attributedsales30dsamesku=VALUES(attributedsales30dsamesku), attributedsales30d=VALUES(attributedsales30d), bid=VALUES(bid)");

						DB::connection('mysql2')->getpdo()->exec("DROP TABLE ".$table_name);

					}
		       	}

	       		$ads_id = AdsCampaignReportId::find($url->id);
	        	$ads_id->updated_at = date('Y-m-d H:i:s');
	        	$ads_id->is_new = 0;
	        	$ads_id->save();
	        	echo "<b>DONE!!!</b><br><br>";
	        	ob_flush();
	        	flush();
	       	}
	       	echo "<b>Updating Max Bid Recommendations .... <br>";
	       	$mkp_country = config('constant.amz_keys.'.strtolower($this->mkp_code).'.marketplaces');
	       	foreach ($mkp_country as $key => $value) {
	       		echo "Country : ". $key."<br>";
	       		$country = $key;
	       		$this->updateAdsPerfMaxRecommendation($country);
	       		echo "<b>Updating Max Bid Recommendations .... DONE!!<br>";
	       		$this->updateAmazonSuggestedBid("","",$country,$amz);
	       		echo "<br>DONE!!<br>";
	       	}
	       	echo "End Time: ".date('Y-m-d H:i:s')."<br>";

	       	echo "<br><b>Updating Recommendations ... </b><br>";
	       	$this->updateRecommendation($this->seller_id);

	       	$time_end = time();
	        $response['total_records'] = $total_data;
	        $response['isError'] = false;
	        $response['time_end'] = date('Y-m-d H:i:s');
	        $response['time_start'] = date('Y-m-d H:i:s', $time_start);
	        $response['total_time_of_execution'] = ($time_end - $time_start)/60;
	        $response['tries'] = 1;
	        $response['message'] = "SUCCESS";

	        $log = new Log;
	        $log->seller_id = $this->seller_id;
	        $log->description = 'Extract Campaign Advertisings New API Reports';
	        $log->date_sent = date('Y-m-d H:i:s');
	        $log->subject = 'Cron Notification for Extract Campaign Advertisings New API Reports';
	        $log->api_used = "None";
	        $log->start_time = $response['time_start'];
	        $log->end_sent = date('Y-m-d H:i:s');
	        $log->record_fetched = $total_data;
	        $log->message = "SUCCESS!";
	        $log->save();

	        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Extract Campaign Advertisings New API Reports for seller'.$this->seller_id.' mkp'.$this->mkp.' Server '.env('APP_ENV', ''), false, $response));
	    }catch (\Exception $e) {
			$time_end = time();
			$response['time_start'] = date('Y-m-d H:i:s', $time_start);
			$response['time_end'] = date('Y-m-d H:i:s', $time_end);
			$response['total_time_of_execution'] = ($time_end - $time_start)/60;
			$response['tries'] = 1;
			$response['total_records'] = (isset($total_data) ? $total_data : 0);
			$response['isError'] = true;
			$response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();

			$log = new Log;
			$log->seller_id = $this->seller_id;
			$log->description = 'Extract Campaign Advertisings New API Reports';
			$log->date_sent = date('Y-m-d H:i:s');
			$log->subject = 'Cron Notification for Extract Campaign Advertisings New API Reports';
			$log->api_used = "None";
			$log->start_time = $response['time_start'];
			$log->end_sent = date('Y-m-d H:i:s');
			$log->record_fetched = (isset($total_data) ? $total_data : 0);
			$log->message = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
			$log->save();
			echo "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Extract Campaign Advertisings New API Reports for seller'.$this->seller_id.' mkp'.$this->mkp.' Server '.env('APP_ENV', '').' (error)', false, $response));
        }
    }
    //looking at the report: what would be good to do is for the "NegativeExact" and "NegativePhrase" keywords & search terms is if you could save the date when that record first appeared (Ie in posted date).

	private function save_campaign($campaign, $country){
		$camp = new AdsCampaign;
   		$camp->seller_id = $this->seller_id;
   		$camp->country = $country;
		$camp->campaignid = (!isset($campaign->campaignId)) ? "" : $campaign->campaignId;
		$camp->name = (!isset($campaign->name)) ? "" : $campaign->name;
		$camp->campaigntype = (!isset($campaign->campaignType)) ? "" : $campaign->campaignType;
		$camp->targetingtype = (!isset($campaign->targetingType)) ? "" : $campaign->targetingType;
		$camp->premiumbidadjustment = (!isset($campaign->premiumBidAdjustment)) ? "" : $campaign->premiumBidAdjustment;
		$camp->dailybudget = (!isset($campaign->dailyBudget)) ? "" : $campaign->dailyBudget;
		$date = (!isset($campaign->startDate)) ? '' : $campaign->startDate;
		if($date != ''){
			$date = DateTime::createFromFormat('Ymd', $date);
			$date = $date->format('Y-m-d');
		}
		$camp->startdate = $date;
		$date = (!isset($campaign->endDate)) ? '' : $campaign->endDate;
		if($date != ''){
			$date = DateTime::createFromFormat('Ymd', $date);
			$date = $date->format('Y-m-d');
		}
		$camp->enddate = $date;
		$camp->state = (!isset($campaign->state)) ? "" : $campaign->state;
		$camp->servingstatus = (!isset($campaign->servingStatus)) ? "" : $campaign->servingStatus;
		$date = (!isset($campaign->creationDate)) ? '' : $campaign->creationDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$camp->creationdate = $date;
		$date = (!isset($campaign->lastUpdatedDate)) ? '' : $campaign->lastUpdatedDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$camp->lastupdateddate = $date;
		$camp->save();
	}
	private function update_campaign($campaign, $country){
		$date = (!isset($campaign->startDate)) ? '' : $campaign->startDate;
		if($date != ''){
			$date = DateTime::createFromFormat('Ymd', $date);
			$date = $date->format('Y-m-d');
		}
		$sd = $date;
		$date = (!isset($campaign->endDate)) ? '' : $campaign->endDate;
		if($date != ''){
			$date = DateTime::createFromFormat('Ymd', $date);
			$date = $date->format('Y-m-d');
		}
		$ed = $date;
		$date = (!isset($campaign->creationDate)) ? '' : $campaign->creationDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$cd = $date;
		$date = (!isset($campaign->lastUpdatedDate)) ? '' : $campaign->lastUpdatedDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$lud = $date;

		$c = AdsCampaign::updateOrCreate(
			['seller_id' => $this->seller_id, 'campaignid' => (!isset($campaign->campaignId)) ? "" : $campaign->campaignId],
    		['country' => $country,
    		'name' => (!isset($campaign->name)) ? "" : $campaign->name,
    		'campaigntype' => (!isset($campaign->campaignType)) ? "" : $campaign->campaignType,
			'targetingtype' => (!isset($campaign->targetingType)) ? "" : $campaign->targetingType,
			'premiumbidadjustment' => (!isset($campaign->premiumBidAdjustment)) ? "" : $campaign->premiumBidAdjustment,
			'dailybudget' => (!isset($campaign->dailyBudget)) ? "" : $campaign->dailyBudget,
			'state' => (!isset($campaign->state)) ? "" : $campaign->state,
			'servingstatus' => (!isset($campaign->servingStatus)) ? "" : $campaign->servingStatus,
			'startdate' => $sd,
			'enddate' => $ed,
			'creationdate' => $cd,
			'lastupdateddate' => $lud]
		);
	}

	private function save_adgroup($adgroup, $country){
		$ads = new AdsCampaignAdGroup;
		$ads->seller_id = $this->seller_id;
		$ads->country = $country;
		$ads->adgroupid = (!isset($adgroup->adGroupId)) ? "" : $adgroup->adGroupId;
		$ads->name = (!isset($adgroup->name)) ? "" : $adgroup->name;
		$ads->campaignid = (!isset($adgroup->campaignId)) ? "" : $adgroup->campaignId;
		$ads->defaultbid = (!isset($adgroup->defaultBid)) ? "" : $adgroup->defaultBid;
		$ads->state = (!isset($adgroup->state)) ? "" : $adgroup->state;
		$date = (!isset($adgroup->creationDate)) ? "" : $adgroup->creationDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$ads->creationdate = $date;
		$date = (!isset($adgroup->lastUpdatedDate)) ? "" : $adgroup->lastUpdatedDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$ads->lastupdateddate = $date;
		$ads->servingstatus = (!isset($adgroup->servingStatus)) ? "" : $adgroup->servingStatus;
		$ads->save();
	}

	private function update_adgroup($adgroup, $country){
		$date = (!isset($adgroup->creationDate)) ? "" : $adgroup->creationDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$cd = $date;
		$date = (!isset($adgroup->lastUpdatedDate)) ? "" : $adgroup->lastUpdatedDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$lud = $date;
		$ads = AdsCampaignAdGroup::updateOrCreate(
			['seller_id'=>$this->seller_id, 'adgroupid'=>(!isset($adgroup->adGroupId)) ? "" : $adgroup->adGroupId],
			['country' => $country,
			'name' => (!isset($adgroup->name)) ? "" : $adgroup->name,
			'campaignid' => (!isset($adgroup->campaignId)) ? "" : $adgroup->campaignId,
			'defaultbid' => (!isset($adgroup->defaultBid)) ? "" : $adgroup->defaultBid,
			'state' => (!isset($adgroup->state)) ? "" : $adgroup->state,
			'servingstatus' => (!isset($adgroup->servingStatus)) ? "" : $adgroup->servingStatus,
			'creationdate' => $cd,
			'lastupdateddate' => $lud]
		);
	}

	private function save_campaign_keyword($keyword, $country){
		$key = new AdsCampaignKeyword;
		$key->seller_id = $this->seller_id;
		$key->country = $country;
		$key->keywordid = (!isset($keyword->keywordId)) ? "" : $keyword->keywordId;
		$key->campaignid = (!isset($keyword->campaignId)) ? "" : $keyword->campaignId;
		$key->adgroupid = (!isset($keyword->adGroupId)) ? "" : $keyword->adGroupId;
		$key->state = (!isset($keyword->state)) ? "" : $keyword->state;
		$key->keywordtext = (!isset($keyword->keywordText)) ? "" : $keyword->keywordText;
		$key->matchtype = (!isset($keyword->matchType)) ? "" : $keyword->matchType;
		$key->bid = (!isset($keyword->bid)) ? "" : $keyword->bid;
		$date = (!isset($keyword->creationDate)) ? "" : $keyword->creationDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$key->creationdate = $date;
		$date = (!isset($keyword->lastUpdatedDate)) ? "" : $keyword->lastUpdatedDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$key->lastupdateddate = $date;
		$key->servingstatus = (!isset($keyword->servingStatus)) ? "" : $keyword->servingStatus;
		$key->save();
	}

	private function update_campaign_keyword($keyword, $country){
		$date = (!isset($keyword->creationDate)) ? "" : $keyword->creationDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$cd = $date;
		$date = (!isset($keyword->lastUpdatedDate)) ? "" : $keyword->lastUpdatedDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$lud = $date;
		$key = AdsCampaignKeyword::updateOrCreate(
			['seller_id' => $this->seller_id, 'keywordid' => (!isset($keyword->keywordId)) ? "" : $keyword->keywordId],
			['country' => $country,
			'campaignid' => (!isset($keyword->campaignId)) ? "" : $keyword->campaignId,
			'adgroupid' => (!isset($keyword->adGroupId)) ? "" : $keyword->adGroupId,
			'state' => (!isset($keyword->state)) ? "" : $keyword->state,
			'keywordtext' => (!isset($keyword->keywordText)) ? "" : $keyword->keywordText,
			'matchtype' => (!isset($keyword->matchType)) ? "" : $keyword->matchType,
			'bid' => (!isset($keyword->bid)) ? "" : $keyword->bid,
			'servingstatus' => (!isset($keyword->servingStatus)) ? "" : $keyword->servingStatus,
			'creationdate' => $cd,
			'lastupdateddate' => $lud]
		);
	}

	private function save_campaign_product($prod, $country){
		$key = new AdsCampaignProduct;
		$key->seller_id = $this->seller_id;
		$key->country = $country;
		$key->adid = (!isset($prod->adId)) ? "" : $prod->adId;
		$key->campaignid = (!isset($prod->campaignId)) ? "" : $prod->campaignId;
		$key->adgroupid = (!isset($prod->adGroupId)) ? "" : $prod->adGroupId;
		$key->sku = (!isset($prod->sku)) ? "" : $prod->sku;
		$key->asin = (!isset($prod->asin)) ? "" : $prod->asin;
		$key->state = (!isset($prod->state)) ? "" : $prod->state;
		$date = (!isset($prod->creationDate)) ? "" : $prod->creationDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$key->creationdate = $date;
		$date = (!isset($prod->lastUpdatedDate)) ? "" : $prod->lastUpdatedDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$key->lastupdateddate = $date;
		$key->servingstatus = (!isset($prod->servingStatus)) ? "" : $prod->servingStatus;
		$key->save();
	}

	private function update_campaign_product($prod, $country){
		$date = (!isset($prod->creationDate)) ? "" : $prod->creationDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$cd = $date;
		$date = (!isset($prod->lastUpdatedDate)) ? "" : $prod->lastUpdatedDate;
		if($date != null AND $date != ""){
			$date = (int)($date/1000);
			$date = new DateTime("@$date");
			$date = $date->format('Y-m-d H:i:s');
		}
		$lud = $date;
		$pro = AdsCampaignProduct::updateOrCreate(
			['seller_id'=>$this->seller_id, 'adid' => (!isset($prod->adId)) ? "" : $prod->adId],
			['country' => $country,
			'campaignid' => (!isset($prod->campaignId)) ? "" : $prod->campaignId,
			'adgroupid' => (!isset($prod->adGroupId)) ? "" : $prod->adGroupId,
			'sku' => (!isset($prod->sku)) ? "" : $prod->sku,
			'asin' => (!isset($prod->asin)) ? "" : $prod->asin,
			'state' => (!isset($prod->state)) ? "" : $prod->state,
			'servingstatus' => (!isset($prod->servingStatus)) ? "" : $prod->servingStatus,
			'creationdate' => $cd,
			'lastupdateddate' => $lud]
		);
	}

	public function updateAdsPerfMaxRecommendation($country){
        ini_set("max_execution_time", 0);  // on
	    ini_set("max_execution_time", -1);  // on

	    $ads_ps = collect();
	    // DB::connection('mysql2')->table('ads_campaign_products')
	    // 	->leftJoin('ads_campaigns', 'ads_campaigns.campaignid', '=', 'ads_campaign_products.campaignid')
	    // 	->where('ads_campaign_products.seller_id', $this->seller_id)
		AdsCampaignProduct::where('seller_id', $this->seller_id)
			->where('country', $country)
			->where('state', '<>' ,'archived')
			->orderBy('asin')
			->select('asin', 'campaignid', 'adgroupid')
			->chunk(500, function($ads_campaign_products) use ($ads_ps, $country){
				$asin_flag = "";
				$p = array();
				foreach ($ads_campaign_products as $ads_p) {
					$asin = $ads_p->asin;
					if($asin_flag != $asin){
						$p = DB::connection('mysql2')->table('products')
			            ->where('products.seller_id', $this->seller_id)
			            ->where('products.asin', $asin)
			            ->where('products.country', $country)
			            ->leftJoin('product_costs', function ($leftJoin) {
			                $leftJoin->on('products.id', '=', 'product_costs.product_id')
			                ->where('product_costs.created_at', '=', DB::raw("(select max(`created_at`) from product_costs where product_costs.product_id = products.id)"));
			            })
			            ->orderBy('product_costs.unit_cost', 'desc')
			            ->get(['products.id','products.asin','products.price','products.sale_price','products.advice_margin','products.time_period','products.estimate_fees','product_costs.unit_cost'])
			            ->first();
					}
					if(count($p) <= 0) continue;

		            $from_date = Carbon::today()->addMinutes(2)->subDays($p->time_period);

		            $ads = collect();
		            CampaignAdvertising::where('seller_id', $this->seller_id)
		                ->where('country', $country)
		                ->where('campaignid', $ads_p->campaignid)
		                ->where('adgroupid', $ads_p->adgroupid)
		                ->where('posted_date', '>=', $from_date)
		                ->select('id', 'average_cpc', 'acos', 'attributedsales30d', 'total_spend')
		                ->chunk(500, function($campaign_advertisings) use ($ads){
		                	foreach($campaign_advertisings as $camp)
			                {
			                	$c = ['id'=>$camp->id, 'average_cpc'=>$camp->average_cpc, 'acos'=>$camp->acos, 'attributedsales30d'=>$camp->attributedsales30d, 'total_spend'=>$camp->total_spend];
			                    $ads->push( (object)($c) );
			                }
		                });
		            $ads_ids = array();
		            $cpc = 0;
		            $acos = 0;
		            $attributedsales30d = 0;
		            $total_spend = 0;

		            //updating all max bid to 0
		            CampaignAdvertising::where('seller_id', $this->seller_id)
		                ->where('campaignid', $ads_p->campaignid)
		                ->where('adgroupid', $ads_p->campaignid)
		                ->where('country', $country)
		                ->update(['max_bid_recommendation'=> 0]);
		            AdsCampaignKeyword::where('seller_id', $this->seller_id)
		                ->where('campaignid', $ads_p->campaignid)
		                ->where('adgroupid', $ads_p->campaignid)
		                ->where('country', $country)
		                ->update(['max_bid_recommendation'=> 0]);
		            AdsCampaignAdGroup::where('seller_id', $this->seller_id)
		                ->where('campaignid', $ads_p->campaignid)
		                ->where('adgroupid', $ads_p->campaignid)
		                ->where('country', $country)
		                ->update(['max_bid_recommendation'=> 0]);

		            if(count($ads) > 0){
		                foreach ($ads as $value) {
		                    $ads_ids[] = $value->id;
		                    $cpc += $value->average_cpc;
		                    $attributedsales30d += $value->attributedsales30d;
		                    $total_spend += $value->total_spend;
		                }
		                $acos  = ($attributedsales30d == 0 OR $total_spend == 0) ? 0 : (($total_spend/$attributedsales30d) * 100);
		                if($acos == 0 OR $cpc == 0){
		                    $max_bid = 0;
		                }else{
		                    $cpc = $cpc / count($ads_ids);
		                    $acos = $acos / count($ads_ids);
		                    $max_bid = abs($p->sale_price - $p->estimate_fees - $p->unit_cost);
		                    $max_bid = ($max_bid == 0 OR $p->sale_price == 0) ? 0 : ($max_bid / $p->sale_price);
		                    $max_bid = ($max_bid == 0 OR $cpc == 0) ? 0 : (($max_bid / $cpc) * $acos);
		                    $max_bid = ($max_bid == 0) ? 0 : round($max_bid, 2);
		                }

		                //$ads = CampaignAdvertising::whereIn('id', $ads_ids)->update(['max_bid_recommendation'=> $max_bid]);
			            CampaignAdvertising::where('seller_id', $this->seller_id)
			                ->where('campaignid', $ads_p->campaignid)
			                ->where('adgroupid', $ads_p->campaignid)
			                ->where('country', $country)
			                ->update(['max_bid_recommendation'=> $max_bid]);

			            AdsCampaignKeyword::where('seller_id', $this->seller_id)
		                    ->where('campaignid', $ads_p->campaignid)
		                    ->where('adgroupid', $ads_p->adgroupid)
		                    ->where('country', $country)
		                    ->update(['max_bid_recommendation' => $max_bid]);

		            	AdsCampaignAdGroup::where('seller_id', $this->seller_id)
		                    ->where('campaignid', $ads_p->campaignid)
		                    ->where('adgroupid', $ads_p->adgroupid)
		                    ->where('country', $country)
		                    ->update(['max_bid_recommendation' => $max_bid]);
			            
		            }
	                echo "memory usage : ".memory_get_peak_usage()." bytes<br>";
	                //echo "SKU ".$sku." max recommendation ".$max_bid."<br>";
	                ob_flush();
	                flush();
				}
			});

        echo "memory usage : ".memory_get_peak_usage()." bytes<br>";
	}

	public function updateAmazonSuggestedBid($access_token,$profile_id,$country,$amz)
    {
            $count_keywords_error = 0;
            $success_keyword = 0;
            $count_adgroups_error = 0;
            $success_adgroups = 0;

            $array = collect();
            $users = DB::connection('mysql2')->table('campaign_advertisings')
            ->where('seller_id', $this->seller_id)
            ->where('type', 'manual')
            ->where('country', $country)
            ->select('keyword_id')
            ->orderBy('keyword_id')
            ->distinct()
            ->chunk(500, function($campaign_advertisings) use ($array)
             {
                foreach($campaign_advertisings as $camp)
                {
                    $array->push($camp->keyword_id);
                }
             });

            echo 'requesting keywords from '.$country.' seller id : '.$this->seller_id.'<br>';
            echo '# of keywords '.count($array).'<br>';

            $flag = 0;
            $data_keyword = array();
            // $error_array = array();
            foreach($array as $a => $value)
            {

                 if(($flag % 20) == 0)
                 sleep(1);

                 if(($flag == 300))
                 {
                 	$profile = $this->is_country_token_expired($country);
                 	if($profile === false) break;
		       		$refresh_token = $profile->amz_refresh_token;
		       		$access_token = $profile->amz_access_token;
		       		$profile_id = $profile->amz_profile_id;
                 }

                 $keyRecommendation = $amz->get_bid_recommendation_by_keywordId($access_token,$profile_id,$this->mkp_code,$value);
                 if(!isset($keyRecommendation->suggestedBid))
                 {
                     $count_keywords_error++;
                     $data_keyword[$value] = $keyRecommendation->details;
                     // $error_array[] = $value;
                 }
                 else
                 {
                     $data_keyword[$value] = $keyRecommendation->suggestedBid;
                     $success_keyword++;
                 }
                 $flag++;
            }


            // foreach($error_array as $e => $value)
            // {
            //     $error = AdsCampaignKeyword::where('keywordid', $value)
            //                         ->where('seller_id' , $this->seller_id)
            //                         ->where('country', $country)
            //                         ->first();
            //     if(isset($error))
            //     {
            //         $error->error = 1;
            //         $error->save();
            //     }
            // }

            echo $country.' : Done Requesting keywords';
            echo '<br>';
            echo 'success : '.$success_keyword.'<br>';
            echo 'failures : '.$count_keywords_error.'<br>';

    		// $profile = $this->is_country_token_expired($country);
    		// if($profile === false) break;
      //  		$refresh_token = $profile->amz_refresh_token;
      //  		$access_token = $profile->amz_access_token;
      //  		$profile_id = $profile->amz_profile_id;

            $camp_arr = collect();
            $users = DB::connection('mysql2')->table('campaign_advertisings')
            ->where('seller_id', $this->seller_id)
            ->where('type', 'auto')
            ->where('country', $country)
            ->select('adgroupid')
            ->orderBy('adgroupid')
            ->distinct()
            ->chunk(300, function($campaign_advertisings) use ($camp_arr)
             {
                foreach($campaign_advertisings as $camp)
                {
                    $camp_arr->push($camp->adgroupid);
                }
             });

            $flag = 0;

            echo 'Requesting adgroups from '.$country.' seller id : '.$this->seller_id.'<br>';
            echo '# of adgroups '.count($camp_arr).'<br>';

            $data_adgroup = array();
            // $error_array_ad = array();
            foreach($camp_arr as $ca => $value)
            {

                 if(($flag % 20) == 0)
                 sleep(1);

                 if(($flag == 300))
                 {
                 	$profile = $this->is_country_token_expired($country);
                 	if($profile === false) break;
		       		$refresh_token = $profile->amz_refresh_token;
		       		$access_token = $profile->amz_access_token;
		       		$profile_id = $profile->amz_profile_id;
                 }

                 $keyRecommendation = $amz->get_bid_recommendation_by_adGroupId($access_token,$profile_id,$this->mkp_code,$value);
                 if(!isset($keyRecommendation->suggestedBid))
                 {
                     $count_adgroups_error++;
                     $data_adgroup[$value] = $keyRecommendation->details;
                     // $error_array_ad[] = $value;
                 }
                 else
                 {
                     $data_adgroup[$value] = $keyRecommendation->suggestedBid;
                     $success_adgroups++;
                 }
                 $flag++;
            }

            // foreach($error_array_ad as $e => $value)
            // {
            //     $error_ad = AdsCampaignAdGroup::where('adgroupid', $value)
            //                         ->where('seller_id' , $this->seller_id)
            //                         ->where('country', $country)
            //                         ->first();
            //     if(isset($error_ad))
            //     {
            //         $error_ad->error = 1;
            //         $error_ad->save();
            //     }
            // }

            echo $country.' : Done Requesting adgroups';
            echo '<br>';
            echo 'success : '.$success_adgroups.'<br>';
            echo 'failures : '.$count_adgroups_error.'<br>';

            echo 'Updating data to Adgroups<br>';

            foreach($data_adgroup as $d => $value)
            {
                if(isset($value->suggested))
                {
                    $adGroup = AdsCampaignAdGroup::where('adgroupid', $d)
	                							 ->where('seller_id', $this->seller_id)
	                							 ->where('country', $country)
	                                             ->first();

                    if(isset($adGroup))
                    {
                        $adGroup->rangeStart = $value->rangeStart;
                        $adGroup->rangeEnd = $value->rangeEnd;
                        $adGroup->suggestedBid = $value->suggested;
                        $adGroup->save();
                    }
                }
            }

            echo 'Done<br>';

            echo 'Updating data to Keywords<br>';
            foreach($data_keyword as $k => $value)
            {
                if(isset($value->suggested))
                {
                    $adKeyword = AdsCampaignKeyword::where('keywordid', $k)
                    							   ->where('seller_id', $this->seller_id)
                    							   ->where('country', $country)
                                                   ->first();

                    if(isset($adKeyword))
                    {
                        $adKeyword->rangeStart = $value->rangeStart;
                        $adKeyword->rangeEnd = $value->rangeEnd;
                        $adKeyword->suggestedBid = $value->suggested;
                        $adKeyword->save();
                    }
                }
            }
            echo 'Done<br>';


    }

    public function updateRecommendation($seller_id){
    	ini_set("max_execution_time", 0);  // on
    	$arc = new AdsRecommendationController;
    	$fields = [
    		'car.country as country',
    		'car.campaign_name as campaign_name',
    		'car.recommendation as recommendation',
    		'car.time_period as time_period',
    		'car.camp_type as camp_type',
    		'car.ad_group_name as ad_group_name',
    		'card.operation as operation',
    		'card.matrix as matrix',
    		'card.metric as metric',
    		'card.value as value',
    		'card.camp_ads_rec_id as camp_ads_rec_id'
    	];
    	$rec = DB::connection('mysql2')
    		->table(DB::raw('campaign_ads_recommendations as car'))
    		->where('seller_id', $seller_id)
    		->leftJoin(DB::raw('campaign_ads_recommendation_conditions as card'), function($leftJoin){
    			$leftJoin->on('car.id', '=', 'card.camp_ads_rec_id')
    			->where('card.is_active', 1);
    		})
    		->get($fields);
    	//reset the recommendations
    	CampaignAdvertising::where('seller_id', $seller_id)->update(['recommendation'=>'']);
    	AdsCampaignKeyword::where('seller_id', $seller_id)->update(['recommendation'=>'']);
    	AdsCampaignAdGroup::where('seller_id', $seller_id)->update(['recommendation'=>'']);

    	$req = [];
    	$camp_ads_rec_cond = [];
    	$flag_id = 0;
    	foreach ($rec as $key => $value) {
    		if($flag_id == 0){
    			$flag_id = $value->camp_ads_rec_id;
    			//set the $req
    			$req['country'] = $value->country;
    			$req['recommendation'] = $value->recommendation;
    			$req['CampType'] = $value->camp_type;
    			$req['campaignName'] = $value->campaign_name;
    			$req['adGroupName'] = $value->ad_group_name;
    			$req['timePeriod'] = $value->time_period;
    			//set the first rule
    			$camp_ads_rec_cond[] = [
    				'matrix'	=> $value->matrix,
    				'metric'	=> $value->metric,
    				'operation'	=> $value->operation,
    				'value'		=> $value->value
    			];
    		}else if($flag_id != $value->camp_ads_rec_id){
    			//$arc = new AdsRecommendationController;
    			//call implement first
    			$arc->implementRule((object)($req), $camp_ads_rec_cond, $seller_id);
    			//reset the flag_id
    			$flag_id = $value->camp_ads_rec_id;
    			//reset the $req
    			$req = array();
    			$camp_ads_rec_cond = array();
    			$req['country'] = $value->country;
    			$req['recommendation'] = $value->recommendation;
    			$req['CampType'] = $value->camp_type;
    			$req['campaignName'] = $value->campaign_name;
    			$req['adGroupName'] = $value->ad_group_name;
    			$req['timePeriod'] = $value->time_period;
    			//reset the first rule
    			$camp_ads_rec_cond[] = [
    				'matrix'	=> $value->matrix,
    				'metric'	=> $value->metric,
    				'operation'	=> $value->operation,
    				'value'		=> $value->value
    			];
    		}else{
    			//set the next rule
    			$camp_ads_rec_cond[] = [
    				'matrix'	=> $value->matrix,
    				'metric'	=> $value->metric,
    				'operation'	=> $value->operation,
    				'value'		=> $value->value
    			];
    		}
    	}

    	if(count($req) > 0 and count($camp_ads_rec_cond) > 0)
    		$arc->implementRule((object)($req), $camp_ads_rec_cond, $seller_id);
    }

}
