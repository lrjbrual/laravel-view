<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use Mail;

use App\CronMasterList;
use App\SellerCronSchedule;
use App\Log;
use App\Seller;
use Carbon\Carbon;

class MasterCronScriptController extends Controller
{
    //
    private $seller_id;
    private $mkp;

    public function index(){
      try {
        ini_set('memory_limit', '1024M');
        ini_set("zlib.output_compression", 0);  // off
        ini_set("implicit_flush", 1);  // on
        ini_set("max_execution_time", 0);  // on


		$start_run_time = time();
		$isError = false;
    	$total_records = 0;

        if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
        {
        	echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
            exit;
        }else{
        	$this->seller_id = trim(Input::get('seller_id'));
        }


        if( Input::get('mkp') == null OR Input::get('mkp') == "" )
        {
        	echo "<p style='color:red;'><b>Marketplace is required as part of the parameter in the url to run this cron script</b></p>";
            exit;
        }else{
        	$this->mkp = trim(Input::get('mkp'));
        }
        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Master Cron Script for seller'.$this->seller_id.' mkp'.$this->mkp, true));

    	ob_start();
    	echo "Running Cron Master List...<br/>";
        ob_flush();
        flush();


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

    	$mcs = new CronMasterList();
    	$mcs_data = $mcs::orderBy('sequence')->where('sequence', '>', 0)->get();
    	foreach ($mcs_data as $value) {
    		echo "Running " . $value->description;
            ob_flush();
            flush();

    		$url = config('app.url').'/'.$value->route."?seller_id=".$this->seller_id."&mkp=".$this->mkp;
    		echo "<br>".$url;

    		$response = $this->runCron($url);

    		echo " . DONE.<br/>";
            ob_flush();
            flush();
    	}

        $q = new SellerCronSchedule();
        $scs = $q::all()->where('seller_id', $this->seller_id);
        if(count($scs) <= 0){
            foreach ($mcs_data as $value) {
                $q = new SellerCronSchedule();
                $q->cron_id = $value->id;
                $q->seller_id = $this->seller_id;
                $q->minutes = 0;
                $q->hours = $value->sequence;
                $q->day_of_month = '*';
                $q->month = '*';
                $q->day_of_week = '*';
                $q->date_created = date('Y-m-d H:i:s');
                $q->isactive = true;
                $q->save();
            }
        }else{
            foreach ($scs as $val) {
                $q = SellerCronSchedule::find($val->id);
                $q->isactive = true;
                $q->save();
            }
        }
    	echo "<br><br>Done running all crons. Master cron list complete!";
        ob_flush();
        flush();

    	$end_run_time = time();
    	$message['message'] = "Master Cron Script Run Successfully!";
		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = 1;
		$message['total_records'] = 0;
		$message['isError'] = $isError;

        $log = new Log;
        $log->seller_id = $this->seller_id;
        $log->description = 'Master Cron Script';
        $log->date_sent = date('Y-m-d H:i:s');
        $log->subject = 'Cron Notification for Master Cron Script';
        $log->api_used = 'None';
        $log->start_time = $message['time_start'];
        $log->end_sent = $message['time_end'];
        $log->record_fetched = 0;
        $log->message = $message['message'];
        $log->save();

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Master Cron Script for seller'.$this->seller_id.' mkp'.$this->mkp, false, $message));
  } catch (\Exception $e) {
    $end_run_time = time();
    $message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
    $message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
    $message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
    $message['tries'] = 1;
    $message['total_records'] = (isset($total_records) ? $total_records : 0);
    $message['isError'] = $isError;
    $message['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
    Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Master Cron Script for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $message));
  }

    }

    private function runCron($url)
    {
        $curl_handle = curl_init();
        $headers = array(
		    "Content-type: x-www-form-urlencoded",
		    "UserAgent" => "PHP Client Library/2015-06-18 (Language=PHP5)"
		);
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl_handle,CURLOPT_URL, $url);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle,CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl_handle,CURLOPT_CUSTOMREQUEST, "GET");
		$result = curl_exec($curl_handle);

        curl_close($curl_handle);
		return $result;
    }
}
