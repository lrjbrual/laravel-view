<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\FulfilledShipment;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use App\Seller;
use App\CampaignEmail;
use App\CampaignEmailAttachment;
use App\CampaignCountry;
use App\CampaignTrigger;
use App\Campaign;
use App\EmailTag;
use App\Product;
use App\SubscriptionPlan;
use App\Plan;
use App\ProductMatch;
use \Config;
use URL;
use Storage;
use App\Log;
use Carbon\Carbon;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;

use Mail;
class CRMAutoCampaignController extends Controller
{
    //
    private $seller_id;
    private $tags;
    private $is_trial;

	public function index(){
    try {
		ini_set('memory_limit', '512M');
		ini_set("zlib.output_compression", 0);  // off
		ini_set("implicit_flush", 1);  // on
		ini_set("max_execution_time", 0);  // on
		$start_run_time = time();
		$isError = false;
		$total_records = 0;
		$total_sent=0;
		$fulfilled = new FulfilledShipment();
		$fulfilled->setConnection('mysql2');
		$tag = new EmailTag();
		$attmts = new CampaignEmailAttachment();
		$attmts->setConnection('mysql2');

		if( Input::get('seller_id') == null OR Input::get('seller_id') == "" ) {
			echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
			exit;
		} else {
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

      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('CRM Auto Campaign for seller'.$this->seller_id, true));

      // $user_trial_end = date_format(date_create($seller->trialperiod->trial_end_date), 'Y-m-d');
      // $today = date('Y-m-d');
      // if ($user_trial_end >= $today) {
      if ($seller->is_trial == 1) {
          $this->is_trial = true;
      } else {
          $this->is_trial = false;
      }

	    if ($this->is_trial == true) {
	    	$limit = 9999999;
	    	$bonus;
	        $bonus_checker = 0;
	    } else {

	        $cbsn = $this->callBaseSubscriptionName($this->seller_id);
	        $bonus = $cbsn->bonus_load;
	        $used = $cbsn->email_used;
	        $bonus_checker = 0;

	        if(($bonus - $used) <= 0) $bonus = 0;

	    	$limit = $this->getCRMLimit();
	    	$limit = $limit + $bonus;

			if($limit < 1){
				echo "<p style='color:red;'><b>This Seller does not subscribe or reach its limit in the CRM Module!</b></p>";
				$message['message'] = 'This Seller does not subscribe or reach its limit in the CRM Module!';
				$this->limitReach();
				$isError = true;
			}
    	}

		if(!$isError){
	    	$report_type = 'NONE';
	    	//start cron
	    	$seller_info = array();
	    	$seller = new Seller();
	    	$seller_info = $seller->find($this->seller_id);

	    	$temp_email = array();
	    	$camp = new Campaign();
	    	$where = array('seller_id' => $this->seller_id,
	    					'campaigns.is_active'=> true,
	    					'campaigns.is_deleted' => false );
	    	$temp_email = $camp->getRecordJoinCampaignEmails(array('*'), $where);

	    	echo "<p style='color:green;'><b>CRM Email Campaign...</b></p>";
			echo "-------------------------------------------------------------------------------------------------------------------<br>";
			ob_flush();
			flush();

			$EmailTemplate = array();
			$fulfilledShipments = array();
			$delivered_delay='';
			$confirmed_delay='';
			$shipped_delay='';
			$error_desc="";
			$isError = false;
			$highest_delay = 0;
			$delay_column="";
			$delay_cols = array('Confirmed' => 'purchase_date',
								'Shipped'=> 'shipment_date',
								'Delivered'=> 'estimated_arrival_date');

			if(count($temp_email)>0)
			{
				foreach($temp_email as $temp){
					$trigger = new CampaignTrigger();
					$event = "";
					$event = $trigger->find($temp->campaign_trigger_id)->description;
					$Qcount = new CampaignCountry();
					$campaign_country = array();
					$campaign_country = $Qcount->getRecords(array('*'), array('campaign_id'=>$temp->campaign_id));
					foreach($campaign_country as $cc){
						$arr = array();
						$arr['TemplateName'] = $temp->template_name;
						$arr['TriggerEventDelay'] = $temp->days_delay;
						$arr['TrigerEvent'] = $event;
						$arr['EmailSubject'] = $temp->subject;
						$arr['EmailBody'] = $temp->email_body;

						//getting highest delay for limit of query in fulfilled_shipments
						if($highest_delay <= $temp->days_delay){
								$highest_delay = $temp->days_delay;
								$delay_column = $delay_cols[$event];
						}
						//attachments path
						//$realpath = URL::to('/')."/storage/app/crm_uploads/campaign/";
						$realpath = env('UPLOADS_LOCATION', "/var/www/html/storage/app/crm_uploads/campaign/");
						$atts = $attmts->all()->where('campaign_email_id', $temp->id);
						$arr['EmailAttachments'] = array();
						foreach($atts as $att) $arr['EmailAttachments'][$att['original_filename']] = $realpath."".$att['path'];

						$arr['IDCRM_Campaign'] = $temp->campaign_id;
						$arr['IDCRM_EmlTemplate'] = $temp->id;
						$delay = date('Y-m-d', strtotime('-'.$temp->days_delay.' days'));
						$arr['delay'] = $delay;

						$c_code = DB::table('countries')->find($cc->country_id)->iso_3166_2;
						
						$EmailTemplate[strtolower($event)."_".$this->_country_converter($c_code)."_".$delay][] = $arr;
					}
					
				}

				//for tags
				$this->tags = $tag->all();

				//for getting fulfilled shipments
				$fields = array('id','purchase_date','shipment_date','buyer_email','buyer_name','sku','product_name','estimated_arrival_date','sales_channel','recipient_name');
				$c = array(
						'seller_id' => $this->seller_id,
						$delay_column => array('>=', date('Y-m-d', strtotime('-'.($highest_delay+10).' days')))
					);
				$fulfilledShipments = $fulfilled->getRecords($fields, $c);

			}else{
				echo "<p style='color:red;'><b>No Template Available.</b></p>";
				$error_desc = "No Template Available.";
				$isError = true;
			}

			if(count($fulfilledShipments)>0){
				//create client list to sparkpost
				$this->requestClientList($fulfilledShipments);
				foreach($fulfilledShipments as $shipments){
					if($limit < 1){
						$this->limitReach();
						break;
					}
					$msg='';
					$subj = '';
					$to = '';
					$attachments='';
					$status='';
					$shipment_id = $shipments->id;
					$buyer_email = $shipments->buyer_email;
					$campaign_id = 0;
					$template_id = 0;

					if(array_key_exists('confirmed_'.$this->_country_converter($shipments->sales_channel)."_".date('Y-m-d',strtotime($shipments->purchase_date)),$EmailTemplate)){
	                    foreach($EmailTemplate['confirmed_'.$this->_country_converter($shipments->sales_channel)."_".date('Y-m-d',strtotime($shipments->purchase_date))] as $val){
							$msg = $val['EmailBody'];
							$subj = $val['EmailSubject'];
							$attachments = $val['EmailAttachments'];
							if(env('APP_ENV') == 'prod'){
								$to = $shipments->buyer_email;
							}else{
								$to = 'crons@trendle.io';
							}
							$status = "Confirmed";
							$campaign_id = $val['IDCRM_Campaign'];
							$template_id = $val['IDCRM_EmlTemplate'];

							$total_sent++;

							echo "Sending ".$status." Email to ".$to." with SKU: ".$shipments->sku."....";
							ob_flush();
							flush();

							$subject = $this->convertTags($subj, $shipments->id);
							$body = $this->convertTags($msg, $shipments->id);
							ob_flush();
							flush();
							$this->_send_email($to,$body,$subject,$attachments,$campaign_id);
							echo "<b style='color:green;' > Sent!!</b><br>";
							echo "<br>Sent No. ".$total_sent."<br>";
							ob_flush();
							flush();
							sleep(5);

							if ($bonus_checker > $bonus) {
								$bonus_checker++;
							}
							$limit--;
						}
					}
					if(array_key_exists('shipped_'.$this->_country_converter($shipments->sales_channel)."_".date('Y-m-d',strtotime($shipments->shipment_date)),$EmailTemplate)){
						foreach($EmailTemplate['shipped_'.$this->_country_converter($shipments->sales_channel)."_".date('Y-m-d',strtotime($shipments->shipment_date))] as $val){
							$msg = $val['EmailBody'];
							$subj = $val['EmailSubject'];
							$attachments = $val['EmailAttachments'];
							if(env('APP_ENV') == 'prod'){
								$to = $shipments->buyer_email;
							}else{
								$to = 'crons@trendle.io';
							}
							$status = "Shipped";
							$campaign_id = $val['IDCRM_Campaign'];
							$template_id = $val['IDCRM_EmlTemplate'];

							$total_sent++;

							echo "Sending ".$status." Email to ".$to." with SKU: ".$shipments->sku."....";
							ob_flush();
							flush();

							$subject = $this->convertTags($subj, $shipments->id);
							$body = $this->convertTags($msg, $shipments->id);
							ob_flush();
							flush();
							$this->_send_email($to,$body,$subject,$attachments,$campaign_id);
							echo "<b style='color:green;' > Sent!!</b><br>";
							echo "<br>Sent No. ".$total_sent."<br>";
							ob_flush();
							flush();
							sleep(5);

							if ($bonus_checker > $bonus) {
								$bonus_checker++;
							}
							$limit--;
						}
					}

					if(array_key_exists('delivered_'.$this->_country_converter($shipments->sales_channel)."_".date('Y-m-d',strtotime($shipments->estimated_arrival_date)),$EmailTemplate)){
						foreach($EmailTemplate['delivered_'.$this->_country_converter($shipments->sales_channel)."_".date('Y-m-d',strtotime($shipments->estimated_arrival_date))] as $val){
							$msg = $val['EmailBody'];
							$subj = $val['EmailSubject'];
							$attachments = $val['EmailAttachments'];
							if(env('APP_ENV') == 'prod'){
								$to = $shipments->buyer_email;
							}else{
								$to = 'crons@trendle.io';
							}
							$status = "Delivered";
							$campaign_id = $val['IDCRM_Campaign'];
							$template_id = $val['IDCRM_EmlTemplate'];

							$total_sent++;

							echo "Sending ".$status." Email to ".$to." with SKU: ".$shipments->sku."....";
							ob_flush();
							flush();

							$subject = $this->convertTags($subj, $shipments->id);
							$body = $this->convertTags($msg, $shipments->id);
							ob_flush();
							flush();

							$this->_send_email($to,$body,$subject,$attachments,$campaign_id);
							echo "<b style='color:green;' > Sent!!</b><br>";
							echo "<br>Sent No. ".$total_sent."<br>";
							ob_flush();
							flush();
							sleep(5);

							if ($bonus_checker > $bonus) {
								$bonus_checker++;
							}
							$limit--;
						}
					}
				}

				//update credit left for seller
				// if ($this->is_trial == false) {
	   //  			$this->updateLimit($limit+$bonus_checker);
	   //  		}

				//delete client list to sparkpost
				$this->requestClientList($fulfilledShipments,false);
			}

		$message['message'] = "CRM Auto Campaign Run Successfully!";
		}

		$end_run_time = time();

		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = 1;
		$message['total_records'] = $total_sent;
		$message['isError'] = $isError;

    	$log = new Log;
		$log->seller_id = $this->seller_id;
		$log->description = 'CRM Auto Campaign';
		$log->date_sent = date('Y-m-d H:i:s');
		$log->subject = 'Cron Notification for CRM Auto Campaign';
		$log->api_used = 'None';
		$log->start_time = $message['time_start'];
		$log->end_sent = $message['time_end'];
		$log->record_fetched = $total_sent;
		$log->message = $message['message'];
		$log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('CRM Auto Campaign for seller'.$this->seller_id, false, $message));
    } catch (\Exception $e) {
      $end_run_time = time();
      $message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
      $message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
      $message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
      $message['tries'] = 1;
      $message['total_records'] = (isset($total_records) ? $total_records : 0);
      $message['isError'] = $isError;
      $message['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('CRM Auto Campaign for seller'.$this->seller_id.' (error)', false, $message));
    }

    }

    private function limitReach(){
    	$seller_data = Seller::find($this->seller_id);
    	$this->to_seller = $seller_data->email;
    	$this->subject = "CRM Trendle.io";
    	$message_body = 'Your availed plan for CRM Email Campaign has run out. Please Subscribe again. Thank you!';
    // /*to be fixed on issue #491*/
    // 	Mail::send(['html' => 'emails.autocampaign'], array('message_body' => $message_body), function($message)
		// {
		//     $message->to($this->to_seller)
		//     		->subject($this->subject)
		//     		->from('crm@trendle.io' , 'CRM Trendle.io');
		// });
		$this->updateLimit(0);
    }

    private function updateLimit($limit){
    	$q = DB::table('crm_loads');
        $c = $q->where('seller_id', $this->seller_id)->update(array('credit'=> $limit));
    }

    private function _send_email($to, $message_body, $subject, $attachments, $campaign_id, $stat_id=0){
    	if ($this->is_trial == false) {
			$cbsn = $this->callBaseSubscriptionName($this->seller_id);
	    	$bonus = $cbsn->bonus_load;
	    	$use = $cbsn->email_used;

	    	if(($bonus - $use) <= 0)
	    	{
	    		$bonus = 0;
	    	}
    	} else {
    		$bonus = 0;
    	}


	    if ($bonus == 0) {
    		DB::table('crm_loads')->where('seller_id', $this->seller_id)->increment('number_email_sent');
    		DB::table('crm_loads')->where('seller_id', $this->seller_id)->decrement('credit');

    	} else {
    		$bss = BaseSubscriptionSeller::where('seller_id', '=', $this->seller_id)->first();
	        if (isset($bss)) {
	            DB::table('base_subscription_seller_transactions')->where('bss_id', '=', $bss->id)
	                                                        ->where('currently_used', '=', true)
	                                                        ->increment('email_used');
	        }
    	}
    	$this->to = $to;
    	$this->subject = $subject;
    	$this->attachments = $attachments;
    	$head = [
		    'campaign_id' => $campaign_id,
		    'metadata' => [
		        'foo' => 'bar'
		    ],
		    /*'tags' => [
		    	$stat_id
		    ],*/
		    'options' => [
		        'open_tracking' => true,
		        'click_tracking' => true
		    ]
		];

		$head = json_encode($head);
		//backup default config
		$backup = Config::get('mail');
		//set new config for sparkpost
		if(env('SPARKPOST_MAIL_DRIVER') != ""){
			Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));
		}

		Mail::send(['html' => 'emails.autocampaign'], array('message_body' => $message_body), function($message) use ($head)
		{
		    $message->to($this->to)
		    		->subject($this->subject)
		    		->from('crm@trendle.io' , 'CRM Trendle.io');
		    if(count($this->attachments)>0){
		    	foreach ($this->attachments as $key => $value) {
		    		$message->attach($value, ['as'=>$key]);
		    	}
		    }
		    $message->getHeaders()
		    		->addTextHeader('X-MSYS-API', $head);
		});
		//restore default config
		Config::set('mail', $backup);
    }



	private function _country_converter($string){
		$country = 'uk';

		if(stripos(strtolower($string),'uk')!=false) $country = 'uk';
		else if(stripos(strtolower($string),'it')!==false) $country = 'it';
		else if(stripos(strtolower($string),'fr')!==false) $country = 'fr';
		else if(stripos(strtolower($string),'de')!==false) $country = 'de';
		else if(stripos(strtolower($string),'es')!==false) $country = 'es';
		else if(stripos(strtolower($string),'ca')!==false) $country = 'ca';
		else if(stripos(strtolower($string),'us')!==false OR stripos(strtolower($string),'com')!==false) $country = 'us';

		return $country;
	}

    public function convertTags($content, $fulfilled_shipment_id){
    	$f = new FulfilledShipment();
    	$f->setConnection('mysql2');
        $fulfilled_shipment = $f->find($fulfilled_shipment_id);

        $asin = "";
        if(count($fulfilled_shipment)>0){
	        $p = new Product();
	        $p->setConnection('mysql2');
	        $asin = $p->where('sku',$fulfilled_shipment->sku)
	        				->where('country', $this->_country_converter($fulfilled_shipment->sales_channel))
	        				->where('seller_id', $this->seller_id)
	        				->first()
	        				->asin;
        }


        $url_image = "";

        if(isset($asin))
        {
        	$pm = ProductMatch::where('country', $this->_country_converter($fulfilled_shipment->sales_channel))
        					  ->where('seller_id', $this->seller_id)
        					  ->where('asin', $asin)
        					  ->first();

        	if(isset($pm))
        	{
        		$url = $pm->url;
        		echo $url;
        		$url = str_replace("75","100",$url);
        		if(isset($url))
        		{

        			$url_image = '<div align="center"><img src="'.$url.'" align="center"></div>';
        			echo '<br>';
        			echo $url_image;
        		}
        		
        	}
        	
        }

        //$product = $this->Fulfilled_Shipments_model->getRecordASIN($fulfilled_shipment->sku);
        //$asin = "";
        //$asin = $product->asin;
		$tgs = $this->tags;
		//print_r($tgs[0]->description);
		$findString = array();
		foreach($tgs as $tag){
			//print_r($tag->description);
			$findString[] = "{{ ".$tag->description." }}";
		}
        $replaceWith = array(
            $fulfilled_shipment->buyer_name,
            $fulfilled_shipment->product_name,
            $fulfilled_shipment->amazon_order_id,
            "https://www." . $fulfilled_shipment->sales_channel . "/review/review-your-purchases/asins=" . $asin,
            $fulfilled_shipment->estimated_arrival_date,
            $fulfilled_shipment->purchase_date,
            $url_image
        );

        $body = str_replace($findString, $replaceWith, $content);

        return $body;
    }

    private function getCRMLimit(){
    	$limit = 0;
    	$q = DB::table('crm_loads');
        $c = $q->select('*')->where('seller_id', $this->seller_id)->get();
        if(count($c)>0){
	        if($c[0]->credit!='' OR $c[0]->credit!=null)
	        	$limit = $c[0]->credit;
		}
        return $limit;
        //return 1220;
    }

    private function requestClientList($data, $isCreate = true){
    	//$client = new Client(); //GuzzleHttp\Client
		$headers = array(
			"Accept: application/json",
			"Authorization: ".env('SPARKPOST_SECRET')
		);
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl_handle, CURLOPT_HEADER, FALSE);
		curl_setopt($curl_handle, CURLOPT_POST, true);

    	if($isCreate){
    		$url = "https://api.sparkpost.com/api/v1/recipient-lists?num_rcpt_errors=3";
    		$recipients;
    		foreach ($data as $value) {
    			$recipients[] = [
    				'address' => [
    					'email' => $value->buyer_email,
    					'name' => $value->buyer_name
    				]
    			];
    		}
    		$obj = [
    			'name' => 'AutoCampaignSellerID_'.$this->seller_id,
    			'description' => 'AutoCampaignSellerID_'.$this->seller_id,
    			'attributes' => [
    				'internal_id' => $this->seller_id,
    				'list_group_id' => $this->seller_id
    			],
    			'recipients' =>
    				$recipients

    		];
    		$obj = json_encode($obj);
    		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $obj);


    	}else{
    		$url = "https://api.sparkpost.com/api/v1/recipient-lists/AutoCampaignSellerID_".$this->seller_id;
    	}
		curl_setopt($curl_handle, CURLOPT_URL, $url);
    	$result = curl_exec($curl_handle);
		//var_dump($result);
    	curl_close($curl_handle);
    }

    /**
     *
     * Gets the bs_name from base_subscription_sellers table
     * and adds a checker for the radio buttons of the view
     *
     * @param    integer    $seller_id
     * @return   object     $data
     *
     */
    private function callBaseSubscriptionName($seller_id) {
        $data = (object) null;

        $data->base_subscription = '';
        $data->bonus_load = 0;
        $data->email_used = 0;
        $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller_id)->first();
        if (isset($bss)) {
            $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                        ->where('currently_used', '=', true)
                                                        ->first();
            $data->base_subscription = $bsst->bs_name;
            $data->bonus_load = $bsst->bonus_mail - $bsst->email_used;
            $data->email_used = $bsst->email_used;
        }

        return $data;
    }
}
