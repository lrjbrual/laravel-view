<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;

use App\MarketplaceAssign;
use App\Log;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use App\Seller;
use Carbon\Carbon;

use Mail;

class ApiColumnChecker extends Controller
{
  public function index(){

    try {
      ini_set('memory_limit', '-1');
        ini_set("max_execution_time", 0);  // on

    //response for mail
		$time_start = time();
		$isError=false;
		$message = "New column checker finished running.";
		$response['time_start'] = date('Y-m-d H:i:s');
		$response['total_time_of_execution'] = 0;
		$response['message'] = $message;
		$response['isError'] = false;
		$response['tries'] = 0;
		$tries=0;
    $total_records=0;
    Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('New column checker', true));

    /*---------------------------------*/

  $time_end = time();
  $response['total_records'] = $total_records;
  $response['isError'] = $isError;
  $response['time_end'] = date('Y-m-d H:i:s');
  $response['time_start'] = date('Y-m-d H:i:s', $time_start);
  $response['total_time_of_execution'] = ($time_end - $time_start)/60;
  $response['tries'] = $tries;
  $response['message'] = $message;

  $log = new Log;
  $log->seller_id = '0';
  $log->description = 'New column checker';
  $log->date_sent = date('Y-m-d H:i:s');
  $log->subject = 'New column checker';
  $log->api_used = '';
  $log->start_time = $response['time_start'];
      $log->end_sent = date('Y-m-d H:i:s');
  $log->record_fetched = 0;
  $log->message = $message;
  $log->save();



    Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('New column checker', false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('New column checker (error)', false, $response));
    }

  }
}
