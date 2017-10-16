<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;
use App\MarketplaceAssign;
use App\UniversalModel;
use App\Log;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\CampaignAdvertising;
use App\TrendleChecker;
use App\Seller;

class UpdateCampaignAdController extends Controller
{
    private $seller_id;
    private $mkp='';

    public function index(){
      try {
    	ini_set('memory_limit', '1024M');
        ini_set("zlib.output_compression", 0);  // off
        ini_set("implicit_flush", 1);  // on
        ini_set("max_execution_time", 0);  // on
    	$total_records = 0;
    	$report_type = '_GET_SP_AUTO_TARGETING_REPORT_';
    	$univ = new UniversalModel();
		$mkp_q= new MarketplaceAssign();

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
    	if( Input::get('mkp') != null OR Input::get('mkp') != "" )
        {
        	$this->mkp = trim(Input::get('mkp'));
        }else{
        	echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
			exit();
        }

        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Advertisings for seller'.$this->seller_id.' mkp'.$this->mkp, true));
        //response for mail
        $time_start = time();
        $isError=false;
        $message = "Campaign Advertising Cron Successfully Fetch Data!";
        $response['time_start'] = date('Y-m-d H:i:s');
        $response['total_time_of_execution'] = 0;
        $response['message'] = $message;
        $response['isError'] = false;
        $response['tries'] = 0;
        $tries=0;

        $where  = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);
		$mkp_assign = $mkp_q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());


        $country_arr = array();
		foreach ($mkp_assign as $value) {
			if($value->marketplace_id == 1) $mkp = config('constant.amz_keys.na.marketplaces');
			if($value->marketplace_id == 2) $mkp = config('constant.amz_keys.eu.marketplaces');

            $mkp_id = $value->marketplace_id;
			$merchantId = $value->mws_seller_id;
			$MWSAuthToken = $value->mws_auth_token;

		    foreach ($mkp as $key => $mkp_data) {
                $report_ids = array();
		    	$tries++;
		    	$country = $key;
                $country_arr[$country] = array();

                $w = array('seller_id'=> $this->seller_id, 'country'=>$country);
                $ff_data_count = $univ->getRecords('campaign_advertisings',array('*'),$w,array(),true);

                $start_date = Carbon::today()->addMinutes(5)->subDays(1);
                $end_date = Carbon::today()->addMinutes(4);
                if(count($ff_data_count)>0){
                    $stop_date = Carbon::today()->addMinutes(5)->subDays(8);
                    $isEmpty = false;
                }
                else{
                    $stop_date = Carbon::today()->addMinutes(5)->subDays(61);
                    $isEmpty = true;
                }

                $day_count = 1;
                // add checker and use checker to continue the last fetching if not done
                $tc_first = TrendleChecker::where('seller_id', '=', $this->seller_id)
                            ->where('checker_name', '=', 'campaign_ad_first')
                            ->where('checker_country', '=', $country)
                            ->first();
                if (isset($tc_first)) {
                    if ((int)$tc_first->checker_status < 60 ) {
                        $isEmpty = true;
                        $day_count = (int)$tc_first->checker_status + 1;
                        $d1 = date_format(date_create($tc_first->checker_date), 'Y-m-d');
                        $d2 = date_format(date_create($tc_first->created_at), 'Y-m-d');
                        $start_date = Carbon::parse($d1)->addMinutes(5)->subDays(1);
                        $end_date = Carbon::parse($d2)->addMinutes(4);
                        $stop_date = Carbon::parse($d2)->addMinutes(5)->subDays(61);
                    } else {
                        $isEmpty = false;
                        $tc_daily = TrendleChecker::where('seller_id', '=', $this->seller_id)
                            ->where('checker_name', '=', 'campaign_ad_daily')
                            ->where('checker_country', '=', $country)
                            ->first();

                        if (isset($tc_daily)) {
                            if ((int)$tc_daily->checker_status < 7) {
                                $day_count = (int)$tc_daily->checker_status + 1;
                                $d1 = date_format(date_create($tc_daily->checker_date), 'Y-m-d');
                                $d2 = date_format(date_create($tc_daily->updated_at), 'Y-m-d');
                                $start_date = Carbon::parse($d1)->addMinutes(5)->subDays(1);
                                $end_date = Carbon::parse($d2)->addMinutes(4);
                                $stop_date = Carbon::parse($d2)->addMinutes(5)->subDays(8);
                            }
                        }
                    }
                }
                $tc_daily = TrendleChecker::where('seller_id', '=', $this->seller_id)
                    ->where('checker_name', '=', 'campaign_ad_daily')
                    ->where('checker_country', '=', $country)
                    ->first();

                if (isset($tc_daily)) {
                    $isEmpty = false;
                    if ((int)$tc_daily->checker_status < 7) {
                        $day_count = (int)$tc_daily->checker_status + 1;
                        $d1 = date_format(date_create($tc_daily->checker_date), 'Y-m-d');
                        $d2 = date_format(date_create($tc_daily->updated_at), 'Y-m-d');
                        $start_date = Carbon::parse($d1)->addMinutes(5)->subDays(1);
                        $end_date = Carbon::parse($d2)->addMinutes(4);
                        $stop_date = Carbon::parse($d2)->addMinutes(5)->subDays(8);
                    }
                }
                $country_arr[$country]['isEmpty'] = $isEmpty;
                $country_arr[$country]['day_count'] = $day_count;

                $var = 0;

                while((string)$start_date!=(string)$stop_date) {

                    $init = array(
                        'merchantId'    => $merchantId,
                        'MWSAuthToken'  => $MWSAuthToken,       //mkp_auth_token
                        'country'       => $country,            //mkp_country
                        'marketPlace'   => $mkp_data['id'],     //seller marketplace id
                        'start_date'    => (string)$start_date,
                        'end_date'      => (string)$end_date,
                        'name'          => 'Campaign Advertising API'
                    );
                    $amz = new MWSFetchReportClass();
                    $amz->initialize($init);
                    $report_ids[(string)$start_date] = $amz->request_RequestID($report_type);
                    $start_date->subDay();

                }

                $country_arr[$country]['report_ids'] = $report_ids;
                $country_arr[$country]['init'] = $init;
            }
        }
//cutted by me

        foreach ($country_arr as $keys2 => $value) {
            $country = $keys2;
            $isEmpty = $country_arr[$keys2]['isEmpty'];
            $day_count = $country_arr[$keys2]['day_count'];
            $columnChecked=false;
            foreach ($country_arr[$country]['report_ids'] as $key => $value) {
                //foreach ($report_ids as $key => $value) {
                    $amz = new MWSFetchReportClass();
                    $amz->initialize($country_arr[$country]['init']);
                    echo "Request ID : ".$value."<br>";
                    $start_date = $key;

                    $return = $amz->fetchData($report_type, $value);
                    echo '<br>Country: '.$country.' | Posted Date: '.(string)$start_date.' | Day Counter: '.$day_count.'<br>';
                    echo 'Saving '.count($return['data']).' rows to database...<br>';
                    if(($columnChecked==false)&&(isset($return['data'][0]))){
                      $amz->checkForNewColumn('campaign_advertisings',$return['data'][0]);
                      $columnChecked=true;
                    }
                    foreach ($return['data'] as $value ) {

                        $data = $this->convert_keys_to_english($value);
                        $df = $data['first_day_of_impression'];
                        $dt = $data['last_day_of_impression'];
                        if($country == 'us' OR $country == 'ca'){
                            $data['first_day_of_impression'] = date('Y-m-d', strtotime($df));
                            $data['last_day_of_impression'] = date('Y-m-d', strtotime($dt));
                        }else{
                            $data['first_day_of_impression'] = date('Y-m-d', strtotime(str_replace('/', '-', $df)));
                            $data['last_day_of_impression'] = date('Y-m-d', strtotime(str_replace('/', '-', $dt)));
                        }
                        $data['impressions'] = (!isset($data['impressions'])) ? 0 : $data['impressions'];
                        $data['clicks'] = (!isset($data['clicks'])) ? 0 : $data['clicks'];
                        $data['total_spend'] = (!isset($data['total_spend'])) ? "0" : $data['total_spend'];
                        $data['orders_placed_within_1_week_of_a_click'] = (!isset($data['orders_placed_within_1_week_of_a_click'])) ? "0" : $data['orders_placed_within_1_week_of_a_click'];
                        $data['product_sales_within_1_week_of_a_click'] = (!isset($data['product_sales_within_1_week_of_a_click'])) ? "0" : $data['product_sales_within_1_week_of_a_click'];
                        $data['same_sku_units_ordered_within_1_week_of_click'] = (!isset($data['same_sku_units_ordered_within_1_week_of_click'])) ? "0" : $data['same_sku_units_ordered_within_1_week_of_click'];
                        $data['other_sku_units_ordered_within_1_week_of_click'] = (!isset($data['other_sku_units_ordered_within_1_week_of_click'])) ? "0" : $data['other_sku_units_ordered_within_1_week_of_click'];
                        $data['same_sku_units_product_sales_within_1_week_of_click'] = (!isset($data['same_sku_units_product_sales_within_1_week_of_click'])) ? "0" : $data['same_sku_units_product_sales_within_1_week_of_click'];
                        $data['other_sku_units_product_sales_within_1_week_of_click'] = (!isset($data['other_sku_units_product_sales_within_1_week_of_click'])) ? "0" : $data['other_sku_units_product_sales_within_1_week_of_click'];
                        $data['conversion_rate_within_1_week_of_a_click'] = (!isset($data['conversion_rate_within_1_week_of_a_click'])) ? "0" : $data['conversion_rate_within_1_week_of_a_click'];
                        $item = array();

                        if ($data['keyword'] == '*') {
                            $type = 'Automatic';
                        } else {
                            $type = 'Manual';
                        }

                        if ($isEmpty == true) { // first fetch
                            if ($day_count == 1) {

                                $item['seller_id'] = $this->seller_id;
                                $item['country'] = $country;
                                $item['campaign_name'] = $data['campaign_name'];
                                $item['ad_group_name'] = $data['ad_group_name'];
                                $item['customer_search_term'] = $data['customer_search_term'];
                                $item['keyword'] = $data['keyword'];
                                $item['match_type'] = $data['match_type'];
                                $item['first_day_of_impression'] = $data['first_day_of_impression'];
                                $item['last_day_of_impression'] = $data['last_day_of_impression'];
                                $item['currency'] = $data['currency'];
                                $item['posted_date'] = $start_date;


                                if(!$univ->isExist('campaign_advertisings',$item)){
                                    $total_records++;

                                    $item['type'] = $type;
                                    $item['impressions'] = $data['impressions'];
                                    $item['clicks'] = $data['clicks'];
                                    $item['ctr'] = $data['ctr'];
                                    $item['total_spend'] = $data['total_spend'];
                                    $item['average_cpc'] = $data['average_cpc'];
                                    $item['acos'] = $data['acos'];
                                    $item['orders_placed_within_1_week_of_a_click'] = $data['orders_placed_within_1_week_of_a_click'];
                                    $item['product_sales_within_1_week_of_a_click'] = $data['product_sales_within_1_week_of_a_click'];
                                    $item['conversion_rate_within_1_week_of_a_click'] = $data['conversion_rate_within_1_week_of_a_click'];
                                    $item['same_sku_units_ordered_within_1_week_of_click'] = $data['same_sku_units_ordered_within_1_week_of_click'];
                                    $item['other_sku_units_ordered_within_1_week_of_click'] = $data['other_sku_units_ordered_within_1_week_of_click'];
                                    $item['same_sku_units_product_sales_within_1_week_of_click'] = $data['same_sku_units_product_sales_within_1_week_of_click'];
                                    $item['other_sku_units_product_sales_within_1_week_of_click'] = $data['other_sku_units_product_sales_within_1_week_of_click'];
                                    $item['created_at'] = date('Y-m-d H:i:s');
                                    $save = $univ->insertData('campaign_advertisings',$item);
                                }

                            } else {

                                $camp_adv = DB::connection('mysql2')
                                            ->table('campaign_advertisings')
                                            ->where('seller_id','=',$this->seller_id)
                                            ->where('country','=',$country)
                                            ->where('campaign_name','=',$data['campaign_name'])
                                            ->where('ad_group_name','=',$data['ad_group_name'])
                                            ->where('customer_search_term','=',$data['customer_search_term'])
                                            ->where('keyword','=',$data['keyword'])
                                            ->where('match_type','=',$data['match_type'])
                                            ->get([
                                                'impressions',
                                                'clicks',
                                                'total_spend',
                                                'orders_placed_within_1_week_of_a_click',
                                                'product_sales_within_1_week_of_a_click',
                                                'same_sku_units_ordered_within_1_week_of_click',
                                                'other_sku_units_ordered_within_1_week_of_click',
                                                'same_sku_units_product_sales_within_1_week_of_click',
                                                'other_sku_units_product_sales_within_1_week_of_click'
                                            ]);

                                if (isset($camp_adv)) {
                                    $c_a = 0;
                                    $c_b = 0;
                                    $c_c = 0;
                                    $c_d = 0;
                                    $c_e = 0;
                                    $c_f = 0;
                                    $c_g = 0;
                                    $c_h = 0;
                                    $c_i = 0;
                                    foreach ($camp_adv as $val) {
                                        $c_a += $val->impressions;
                                        $c_b += $val->clicks;
                                        $c_c += $val->total_spend;
                                        $c_d += $val->orders_placed_within_1_week_of_a_click;
                                        $c_e += $val->product_sales_within_1_week_of_a_click;
                                        $c_f += $val->same_sku_units_ordered_within_1_week_of_click;
                                        $c_g += $val->other_sku_units_ordered_within_1_week_of_click;
                                        $c_h += $val->same_sku_units_product_sales_within_1_week_of_click;
                                        $c_i += $val->other_sku_units_product_sales_within_1_week_of_click;
                                    }

                                    $a = (int)$data['impressions'] - (int)$c_a;
                                    $b = (int)$data['clicks'] - (int)$c_b;
                                    $c = (double)$data['total_spend'] - (double)$c_c;
                                    $d = (int)$data['orders_placed_within_1_week_of_a_click'] - (int)$c_d;
                                    $e = (double)$data['product_sales_within_1_week_of_a_click'] - (double)$c_e;
                                    $f = (int)$data['same_sku_units_ordered_within_1_week_of_click'] - (int)$c_f;
                                    $g = (int)$data['other_sku_units_ordered_within_1_week_of_click'] - (int)$c_g;
                                    $h = (double)$data['same_sku_units_product_sales_within_1_week_of_click'] - (double)$c_h;
                                    $i = (double)$data['other_sku_units_product_sales_within_1_week_of_click'] - (double)$c_i;

                                    $a = ($a <= 0) ? 0 : $a;
                                    $b = ($b <= 0) ? 0 : $b;
                                    $c = ($c <= 0) ? 0 : $c;
                                    $d = ($d <= 0) ? 0 : $d;
                                    $e = ($e <= 0) ? 0 : $e;
                                    $f = ($f <= 0) ? 0 : $f;
                                    $g = ($g <= 0) ? 0 : $g;
                                    $h = ($h <= 0) ? 0 : $h;
                                    $i = ($i <= 0) ? 0 : $i;

                                    $ctr = ($a == 0) ? 0 : round(($b/$a)*100,3);
                                    $acos = ($e == 0) ? 0 : round(($c/$e)*100,2);
                                    $cr = ($b == 0) ? 0 : round(($d/$b)*100,2);
                                    $ave_cpc = ($b == 0) ? 0 : round(($c/$b),2);

                                    $total_records++;
                                    $item['seller_id'] = $this->seller_id;
                                    $item['campaign_name'] = $data['campaign_name'];
                                    $item['ad_group_name'] = $data['ad_group_name'];
                                    $item['type'] = $type;
                                    $item['keyword'] = $data['keyword'];
                                    $item['currency'] = $data['currency'];
                                    $item['country'] = $country;
                                    $item['customer_search_term'] = $data['customer_search_term'];
                                    $item['match_type'] = $data['match_type'];
                                    $item['first_day_of_impression'] = $data['first_day_of_impression'];
                                    $item['last_day_of_impression'] = $data['last_day_of_impression'];
                                    $item['impressions'] = $a;
                                    $item['clicks'] = $b;
                                    $item['ctr'] = $ctr;
                                    $item['total_spend'] = $c;
                                    $item['average_cpc'] = $ave_cpc;
                                    $item['acos'] = $acos;
                                    $item['orders_placed_within_1_week_of_a_click'] = $d;
                                    $item['product_sales_within_1_week_of_a_click'] = $e;
                                    $item['conversion_rate_within_1_week_of_a_click'] = $cr;
                                    $item['same_sku_units_ordered_within_1_week_of_click'] = $f;
                                    $item['other_sku_units_ordered_within_1_week_of_click'] = $g;
                                    $item['same_sku_units_product_sales_within_1_week_of_click'] = $h;
                                    $item['other_sku_units_product_sales_within_1_week_of_click'] = $i;
                                    $item['posted_date'] = $start_date;
                                    $item['created_at'] = date('Y-m-d H:i:s');
                                    $save = $univ->insertData('campaign_advertisings',$item);

                                } else {

                                    $total_records++;
                                    $item['seller_id'] = $this->seller_id;
                                    $item['campaign_name'] = $data['campaign_name'];
                                    $item['ad_group_name'] = $data['ad_group_name'];
                                    $item['type'] = $type;
                                    $item['keyword'] = $data['keyword'];
                                    $item['currency'] = $data['currency'];
                                    $item['country'] = $country;
                                    $item['customer_search_term'] = $data['customer_search_term'];
                                    $item['match_type'] = $data['match_type'];
                                    $item['first_day_of_impression'] = $data['first_day_of_impression'];
                                    $item['last_day_of_impression'] = $data['last_day_of_impression'];
                                    $item['impressions'] = $data['impressions'];
                                    $item['clicks'] = $data['clicks'];
                                    $item['ctr'] = $data['ctr'];
                                    $item['total_spend'] = $data['total_spend'];
                                    $item['average_cpc'] = $data['average_cpc'];
                                    $item['acos'] = $data['acos'];
                                    $item['orders_placed_within_1_week_of_a_click'] = $data['orders_placed_within_1_week_of_a_click'];
                                    $item['product_sales_within_1_week_of_a_click'] = $data['product_sales_within_1_week_of_a_click'];
                                    $item['conversion_rate_within_1_week_of_a_click'] = $data['conversion_rate_within_1_week_of_a_click'];
                                    $item['same_sku_units_ordered_within_1_week_of_click'] = $data['same_sku_units_ordered_within_1_week_of_click'];
                                    $item['other_sku_units_ordered_within_1_week_of_click'] = $data['other_sku_units_ordered_within_1_week_of_click'];
                                    $item['same_sku_units_product_sales_within_1_week_of_click'] = $data['same_sku_units_product_sales_within_1_week_of_click'];
                                    $item['other_sku_units_product_sales_within_1_week_of_click'] = $data['other_sku_units_product_sales_within_1_week_of_click'];
                                    $item['posted_date'] = $start_date;
                                    $item['created_at'] = date('Y-m-d H:i:s');
                                    $save = $univ->insertData('campaign_advertisings',$item);
                                }
                            }

                        } else { // daily
                            if ($day_count == 1) {

                                $item['seller_id'] = $this->seller_id;
                                $item['country'] = $country;
                                $item['campaign_name'] = $data['campaign_name'];
                                $item['ad_group_name'] = $data['ad_group_name'];
                                $item['customer_search_term'] = $data['customer_search_term'];
                                $item['keyword'] = $data['keyword'];
                                $item['match_type'] = $data['match_type'];
                                $item['first_day_of_impression'] = $data['first_day_of_impression'];
                                $item['last_day_of_impression'] = $data['last_day_of_impression'];
                                $item['currency'] = $data['currency'];
                                $item['posted_date'] = $start_date;

                                if(!$univ->isExist('campaign_advertisings',$item)){
                                    $total_records++;

                                    $item['type'] = $type;
                                    $item['impressions'] = $data['impressions'];
                                    $item['clicks'] = $data['clicks'];
                                    $item['ctr'] = $data['ctr'];
                                    $item['total_spend'] = $data['total_spend'];
                                    $item['average_cpc'] = $data['average_cpc'];
                                    $item['acos'] = $data['acos'];
                                    $item['orders_placed_within_1_week_of_a_click'] = $data['orders_placed_within_1_week_of_a_click'];
                                    $item['product_sales_within_1_week_of_a_click'] = $data['product_sales_within_1_week_of_a_click'];
                                    $item['conversion_rate_within_1_week_of_a_click'] = $data['conversion_rate_within_1_week_of_a_click'];
                                    $item['same_sku_units_ordered_within_1_week_of_click'] = $data['same_sku_units_ordered_within_1_week_of_click'];
                                    $item['other_sku_units_ordered_within_1_week_of_click'] = $data['other_sku_units_ordered_within_1_week_of_click'];
                                    $item['same_sku_units_product_sales_within_1_week_of_click'] = $data['same_sku_units_product_sales_within_1_week_of_click'];
                                    $item['other_sku_units_product_sales_within_1_week_of_click'] = $data['other_sku_units_product_sales_within_1_week_of_click'];
                                    $item['created_at'] = date('Y-m-d H:i:s');
                                    $save = $univ->insertData('campaign_advertisings',$item);
                                }

                            } else {

                                $camp_adv = DB::connection('mysql2')
                                            ->table('campaign_advertisings')
                                            ->where('seller_id','=',$this->seller_id)
                                            ->where('country','=',$country)
                                            ->where('campaign_name','=',$data['campaign_name'])
                                            ->where('ad_group_name','=',$data['ad_group_name'])
                                            ->where('customer_search_term','=',$data['customer_search_term'])
                                            ->where('keyword','=',$data['keyword'])
                                            ->where('match_type','=',$data['match_type'])
                                            ->whereDate('posted_date', '>', date_format(date_create($start_date), 'Y-m-d'))
                                            ->get([
                                                'impressions',
                                                'clicks',
                                                'total_spend',
                                                'orders_placed_within_1_week_of_a_click',
                                                'product_sales_within_1_week_of_a_click',
                                                'same_sku_units_ordered_within_1_week_of_click',
                                                'other_sku_units_ordered_within_1_week_of_click',
                                                'same_sku_units_product_sales_within_1_week_of_click',
                                                'other_sku_units_product_sales_within_1_week_of_click'
                                            ]);

                                if (isset($camp_adv)) {
                                    $c_a = 0;
                                    $c_b = 0;
                                    $c_c = 0;
                                    $c_d = 0;
                                    $c_e = 0;
                                    $c_f = 0;
                                    $c_g = 0;
                                    $c_h = 0;
                                    $c_i = 0;
                                    foreach ($camp_adv as $val) {
                                        $c_a += $val->impressions;
                                        $c_b += $val->clicks;
                                        $c_c += $val->total_spend;
                                        $c_d += $val->orders_placed_within_1_week_of_a_click;
                                        $c_e += $val->product_sales_within_1_week_of_a_click;
                                        $c_f += $val->same_sku_units_ordered_within_1_week_of_click;
                                        $c_g += $val->other_sku_units_ordered_within_1_week_of_click;
                                        $c_h += $val->same_sku_units_product_sales_within_1_week_of_click;
                                        $c_i += $val->other_sku_units_product_sales_within_1_week_of_click;
                                    }

                                    $a = (int)$data['impressions'] - (int)$c_a;
                                    $b = (int)$data['clicks'] - (int)$c_b;
                                    $c = (double)$data['total_spend'] - (double)$c_c;
                                    $d = (int)$data['orders_placed_within_1_week_of_a_click'] - (int)$c_d;
                                    $e = (double)$data['product_sales_within_1_week_of_a_click'] - (double)$c_e;
                                    $f = (int)$data['same_sku_units_ordered_within_1_week_of_click'] - (int)$c_f;
                                    $g = (int)$data['other_sku_units_ordered_within_1_week_of_click'] - (int)$c_g;
                                    $h = (double)$data['same_sku_units_product_sales_within_1_week_of_click'] - (double)$c_h;
                                    $i = (double)$data['other_sku_units_product_sales_within_1_week_of_click'] - (double)$c_i;

                                    $a = ($a <= 0) ? 0 : $a;
                                    $b = ($b <= 0) ? 0 : $b;
                                    $c = ($c <= 0) ? 0 : $c;
                                    $d = ($d <= 0) ? 0 : $d;
                                    $e = ($e <= 0) ? 0 : $e;
                                    $f = ($f <= 0) ? 0 : $f;
                                    $g = ($g <= 0) ? 0 : $g;
                                    $h = ($h <= 0) ? 0 : $h;
                                    $i = ($i <= 0) ? 0 : $i;

                                    $ctr = ($a == 0) ? 0 : round(($b/$a)*100,3);
                                    $acos = ($e == 0) ? 0 : round(($c/$e)*100,2);
                                    $cr = ($b == 0) ? 0 : round(($d/$b)*100,2);
                                    $ave_cpc = ($b == 0) ? 0 : round(($c/$b),2);

                                    $camp_adv_duplicate = DB::connection('mysql2')
                                            ->table('campaign_advertisings')
                                            ->where('seller_id','=',$this->seller_id)
                                            ->where('country','=',$country)
                                            ->where('campaign_name','=',$data['campaign_name'])
                                            ->where('ad_group_name','=',$data['ad_group_name'])
                                            ->where('customer_search_term','=',$data['customer_search_term'])
                                            ->where('keyword','=',$data['keyword'])
                                            ->where('match_type','=',$data['match_type'])
                                            ->whereDate('posted_date', '=', date_format(date_create($start_date), 'Y-m-d'))
                                            ->first();

                                    if (isset($camp_adv_duplicate)) {
                                        $total_records++;
                                        $ca = CampaignAdvertising::find($camp_adv_duplicate->id);

                                        $ca->first_day_of_impression = $data['first_day_of_impression'];
                                        $ca->last_day_of_impression = $data['last_day_of_impression'];
                                        $ca->impressions = $a;
                                        $ca->clicks = $b;
                                        $ca->ctr = $ctr;
                                        $ca->total_spend = $c;
                                        $ca->average_cpc = $ave_cpc;
                                        $ca->acos = $acos;
                                        $ca->orders_placed_within_1_week_of_a_click = $d;
                                        $ca->product_sales_within_1_week_of_a_click = $e;
                                        $ca->conversion_rate_within_1_week_of_a_click = $cr;
                                        $ca->same_sku_units_ordered_within_1_week_of_click = $f;
                                        $ca->other_sku_units_ordered_within_1_week_of_click = $g;
                                        $ca->same_sku_units_product_sales_within_1_week_of_click = $h;
                                        $ca->other_sku_units_product_sales_within_1_week_of_click = $i;
                                        $ca->updated_at = date('Y-m-d H:i:s');

                                        $ca->save();

                                    } else {

                                        $total_records++;
                                        $item['seller_id'] = $this->seller_id;
                                        $item['campaign_name'] = $data['campaign_name'];
                                        $item['ad_group_name'] = $data['ad_group_name'];
                                        $item['type'] = $type;
                                        $item['keyword'] = $data['keyword'];
                                        $item['currency'] = $data['currency'];
                                        $item['country'] = $country;
                                        $item['customer_search_term'] = $data['customer_search_term'];
                                        $item['match_type'] = $data['match_type'];
                                        $item['first_day_of_impression'] = $data['first_day_of_impression'];
                                        $item['last_day_of_impression'] = $data['last_day_of_impression'];
                                        $item['impressions'] = $a;
                                        $item['clicks'] = $b;
                                        $item['ctr'] = $ctr;
                                        $item['total_spend'] = $c;
                                        $item['average_cpc'] = $ave_cpc;
                                        $item['acos'] = $acos;
                                        $item['orders_placed_within_1_week_of_a_click'] = $d;
                                        $item['product_sales_within_1_week_of_a_click'] = $e;
                                        $item['conversion_rate_within_1_week_of_a_click'] = $cr;
                                        $item['same_sku_units_ordered_within_1_week_of_click'] = $f;
                                        $item['other_sku_units_ordered_within_1_week_of_click'] = $g;
                                        $item['same_sku_units_product_sales_within_1_week_of_click'] = $h;
                                        $item['other_sku_units_product_sales_within_1_week_of_click'] = $i;
                                        $item['posted_date'] = $start_date;
                                        $item['created_at'] = date('Y-m-d H:i:s');

                                        $save = $univ->insertData('campaign_advertisings',$item);
                                    }

                                } else {

                                    $camp_adv_duplicate = DB::connection('mysql2')
                                            ->table('campaign_advertisings')
                                            ->where('seller_id','=',$this->seller_id)
                                            ->where('country','=',$country)
                                            ->where('campaign_name','=',$data['campaign_name'])
                                            ->where('ad_group_name','=',$data['ad_group_name'])
                                            ->where('customer_search_term','=',$data['customer_search_term'])
                                            ->where('keyword','=',$data['keyword'])
                                            ->where('match_type','=',$data['match_type'])
                                            ->whereDate('posted_date', '=', date_format(date_create($start_date), 'Y-m-d'))
                                            ->first();

                                    if (isset($camp_adv_duplicate)) {

                                        $total_records++;
                                        $ca = CampaignAdvertising::find($camp_adv_duplicate->id);

                                        $ca->first_day_of_impression = $data['first_day_of_impression'];
                                        $ca->last_day_of_impression = $data['last_day_of_impression'];
                                        $ca->impressions = $data['impressions'];
                                        $ca->clicks = $data['clicks'];
                                        $ca->ctr = $data['ctr'];
                                        $ca->total_spend = $data['total_spend'];
                                        $ca->average_cpc = $data['average_cpc'];
                                        $ca->acos = $data['acos'];
                                        $ca->orders_placed_within_1_week_of_a_click =$data['orders_placed_within_1_week_of_a_click'];
                                        $ca->product_sales_within_1_week_of_a_click = $data['product_sales_within_1_week_of_a_click'];
                                        $ca->conversion_rate_within_1_week_of_a_click = $$data['conversion_rate_within_1_week_of_a_click'];
                                        $ca->same_sku_units_ordered_within_1_week_of_click = $data['same_sku_units_ordered_within_1_week_of_click'];
                                        $ca->other_sku_units_ordered_within_1_week_of_click = $data['other_sku_units_ordered_within_1_week_of_click'];
                                        $ca->same_sku_units_product_sales_within_1_week_of_click = $data['same_sku_units_product_sales_within_1_week_of_click'];
                                        $ca->other_sku_units_product_sales_within_1_week_of_click = $data['other_sku_units_product_sales_within_1_week_of_click'];
                                        $ca->updated_at = date('Y-m-d H:i:s');

                                        $ca->save();

                                    } else {

                                        $total_records++;
                                        $item['seller_id'] = $this->seller_id;
                                        $item['campaign_name'] = $data['campaign_name'];
                                        $item['ad_group_name'] = $data['ad_group_name'];
                                        $item['type'] = $type;
                                        $item['keyword'] = $data['keyword'];
                                        $item['currency'] = $data['currency'];
                                        $item['country'] = $country;
                                        $item['customer_search_term'] = $data['customer_search_term'];
                                        $item['match_type'] = $data['match_type'];
                                        $item['first_day_of_impression'] = $data['first_day_of_impression'];
                                        $item['last_day_of_impression'] = $data['last_day_of_impression'];
                                        $item['impressions'] = $data['impressions'];
                                        $item['clicks'] = $data['clicks'];
                                        $item['ctr'] = $data['ctr'];
                                        $item['total_spend'] = $data['total_spend'];
                                        $item['average_cpc'] = $data['average_cpc'];
                                        $item['acos'] = $data['acos'];
                                        $item['orders_placed_within_1_week_of_a_click'] = $data['orders_placed_within_1_week_of_a_click'];
                                        $item['product_sales_within_1_week_of_a_click'] = $data['product_sales_within_1_week_of_a_click'];
                                        $item['conversion_rate_within_1_week_of_a_click'] = $data['conversion_rate_within_1_week_of_a_click'];
                                        $item['same_sku_units_ordered_within_1_week_of_click'] = $data['same_sku_units_ordered_within_1_week_of_click'];
                                        $item['other_sku_units_ordered_within_1_week_of_click'] = $data['other_sku_units_ordered_within_1_week_of_click'];
                                        $item['same_sku_units_product_sales_within_1_week_of_click'] = $data['same_sku_units_product_sales_within_1_week_of_click'];
                                        $item['other_sku_units_product_sales_within_1_week_of_click'] = $data['other_sku_units_product_sales_within_1_week_of_click'];
                                        $item['posted_date'] = $start_date;
                                        $item['created_at'] = date('Y-m-d H:i:s');

                                        $save = $univ->insertData('campaign_advertisings',$item);
                                    }
                                }
                            }
                        }
                    }
                    if ($isEmpty == true) {
                        $tc = TrendleChecker::where('seller_id', '=', $this->seller_id)
                            ->where('checker_name', '=', 'campaign_ad_first')
                            ->where('checker_country', '=', $country)
                            ->first();
                        if (isset($tc)){
                            $tc->checker_status = $day_count;
                            $tc->checker_date = $start_date;
                            $tc->updated_at = date('Y-m-d H:i:s');
                            $tc->save();
                        } else {
                            $tc = new TrendleChecker();
                            $tc->seller_id = $this->seller_id;
                            $tc->checker_name = 'campaign_ad_first';
                            $tc->checker_country = $country;
                            $tc->checker_status = $day_count;
                            $tc->checker_date = $start_date;
                            $tc->created_at = date('Y-m-d H:i:s');
                            $tc->updated_at = date('Y-m-d H:i:s');
                            $tc->save();
                        }
                    } else {
                        $tc = TrendleChecker::where('seller_id', '=', $this->seller_id)
                            ->where('checker_name', '=', 'campaign_ad_daily')
                            ->where('checker_country', '=', $country)
                            ->first();
                        if (isset($tc)){
                            $tc->checker_status = $day_count;
                            $tc->checker_date = $start_date;
                            $tc->updated_at = date('Y-m-d H:i:s');
                            $tc->save();
                        } else {
                            $tc = new TrendleChecker();
                            $tc->seller_id = $this->seller_id;
                            $tc->checker_name = 'campaign_ad_daily';
                            $tc->checker_country = $country;
                            $tc->checker_status = $day_count;
                            $tc->checker_date = $start_date;
                            $tc->created_at = date('Y-m-d H:i:s');
                            $tc->updated_at = date('Y-m-d H:i:s');
                            $tc->save();
                        }
                    }
                    $day_count++;
                    //$start_date->subDay();
                //}
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
        $log->description = 'Campaign Advertisings';
        $log->date_sent = date('Y-m-d H:i:s');
        $log->subject = 'Cron Notification for Campaign Advertisings';
        $log->api_used = $report_type;
        $log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
        $log->record_fetched = $total_records;
        $log->message = $message;
        $log->save();

        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Advertisings for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
        } catch (\Exception $e) {
          $time_end = time();
          $response['time_start'] = date('Y-m-d H:i:s', $time_start);
          $response['time_end'] = date('Y-m-d H:i:s', $time_end);
          $response['total_time_of_execution'] = ($time_end - $time_start)/60;
          $response['tries'] = 1;
          $response['total_records'] = (isset($total_records) ? $total_records : 0);
          $response['isError'] = $isError;
          $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
          Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Advertisings for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
        }
    }

    private function convert_keys_to_english($data){
    	$new_data = array();
    	foreach ($data as $key => $value) {
    		switch ( strtolower(trim($key)) ) {
    			case 'campaign_name':
    			case 'kampagne':
    			case 'nombre_de_campaña':
    			case 'nom_de_la_campagne':
    			case 'nome_campagna':
    				$new_data['campaign_name'] = $value;
    				break;

    			case 'ad_group_name':
    			case 'anzeigengruppenname':
    			case 'nombre_grupo_anuncios':
    			case "nom_du_groupe_d'annonces":
    			case 'nome_gruppo_di_annunci':
    				$new_data['ad_group_name'] = $value;
    				break;

    			case 'customer_search_term':
    			case 'suchbegriff_des_kunden':
    			case 'termino_de_busqueda_del_cliente':
    			case 'termine_di_ricerca_del_cliente':
    				$new_data['customer_search_term'] = $value;
    				break;

    			case 'keyword':
    			case 'palabra_clave':
    			case 'mot_clé':
    			case 'parola_chiave':
    				$new_data['keyword'] = $value;
    				break;

    			case 'match_type':
    			case strtolower('Übereinstimmungstyp'):
    			case 'tipo_de_concordancia':
    			case 'type_de_correspondance':
    			case 'tipo_di_corrispondenza':
    				$new_data['match_type'] = $value;
    				break;

    			case 'first_day_of_impression':
    			case 'datum_erster_aufruf':
    			case 'fecha_de_primera_impresion':
    			case 'data_di_prima_impressione':
    				$new_data['first_day_of_impression'] = $value;
    				break;

    			case 'last_day_of_impression':
    			case 'datum_letzter_aufruf':
    			case 'fecha_de_ultima_impresion':
    			case 'data_di_ultima_impressione':
    				$new_data['last_day_of_impression'] = $value;
    				break;

    			case 'clicks':
    			case 'klicks':
    			case 'clics':
    			case 'nombre_de_clics':
    				$new_data['clicks'] = $value;
    				break;

    			case 'ctr':
    			case 'klickrate_(ctr)':
    				$new_data['ctr'] = $value;
    				break;

    			case 'impressions':
    			case 'aufrufe':
    			case 'impresiones':
    			case 'impressions':
    			case 'impressioni':
    				$new_data['impressions'] = $value;
    				break;

    			case 'total_spend':
    			case 'gesamtausgaben':
    			case 'gasto_total':
    			case 'dépenses_totales':
    			case 'spesa_totale':
    				$new_data['total_spend'] = $value;
    				break;

    			case 'average_cpc':
    			case 'durchschnittliche_cpc':
    			case 'cpc_medio':
    			case 'cpc_moyen':
    			case 'cpc_medio':
    				$new_data['average_cpc'] = $value;
    				break;

    			case 'acos':
    			case 'zugeschriebene_umsatzkosten':
    			case 'coste_publicitario_de_las_ventas':
    			case 'ratio_dépenses_publicitaires/chiffre_d’affaires':
    			case 'costo_delle_vendite_pubblicitarie':
    				$new_data['acos'] = $value;
    				break;

    			case 'currency':
    			case 'währung':
    			case 'divisa':
    			case 'devise':
    			case 'valuta':
    				$new_data['currency'] = $value;
    				break;

    			case 'orders_placed_within_1_week_of_a_click':
    			case 'aufgegebene_bestellungen,_1_woche':
    			case 'pedidos_realizados_en_1 semana':
    			case 'ordini_effettuati_entro_1_settimana':
    				$new_data['orders_placed_within_1_week_of_a_click'] = $value;
    				break;

    			case 'product_sales_within_1_week_of_a_click':
    			case 'bestellumsatz,_1_woche_(€)':
    			case 'ventas_de_productos_pedidos_en_1 semana_(€)':
    			case 'vendite_di_prodotti_ordinati_entro_1_settimana_(€)':
    				$new_data['product_sales_within_1_week_of_a_click'] = $value;
    				break;

    			case 'conversion_rate_within_1_week_of_a_click':
    			case 'konversionsrate,_1_woche':
    			case 'tasa_de_conversión_en_1 semana':
    			case 'tasso_di_conversione_entro_1_settimana':
    				$new_data['conversion_rate_within_1_week_of_a_click'] = $value;
    				break;

    			case 'same_sku_units_ordered_within_1_week_of_click':
    			case 'bestellte_gleiche_sku_einheiten,_1_woche':
    			case 'unidades_sku_iguales_pedidas_en_1 semana':
    			case 'unità_con_lo_stesso_sku_ordinate_entro_1_settimana':
    				$new_data['same_sku_units_ordered_within_1_week_of_click'] = $value;
    				break;

    			case 'other_sku_units_ordered_within_1_week_of_click':
    			case 'bestellte_andere_sku_einheiten,_1_woche':
    			case 'unidades_sku_diferentes_pedidas_en_1 semana':
    			case 'unità_con_altri_sku_ordinate_entro_1_settimana':
    				$new_data['other_sku_units_ordered_within_1_week_of_click'] = $value;
    				break;

    			case 'same_sku_units_product_sales_within_1_week_of_click':
    			case 'bestellumsatz_gleiche_sku,_1_woche':
    			case 'ventas_de_productos_pedidos_correspondientes_a_unidades_sku_iguales_en_1 semana':
    			case 'vendite_di_prodotti_con_lo_stesso_sku_ordinati_entro_1_settimana':
    				$new_data['same_sku_units_product_sales_within_1_week_of_click'] = $value;
    				break;

    			case 'other_sku_units_product_sales_within_1_week_of_click':
    			case 'bestellumsatz_andere_skus,_1_woche':
    			case 'ventas_de_productos_pedidos_correspondientes_a_unidades_sku_diferentes_en_1 semana':
    			case 'vendite_di_prodotti_con_altri_sku_ordinati_entro_1_settimana':
    				$new_data['other_sku_units_product_sales_within_1_week_of_click'] = $value;
    				break;

    			default:
    				# code...
    				break;
    		}
    	}
        return $new_data;
    }


}
