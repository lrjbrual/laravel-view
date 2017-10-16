<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\CronNotification;
use Mail;
use App\Seller;
use App\User;
use App\Billing;
use App\TrialPeriod;
use \Config;
use Carbon\Carbon;

class SellerTrialPeriodCheckerController extends Controller
{
	private $userQ;
	private $billingQ;
	private $sellerQ;

	public function __construct(){
		$this->userQ = new User();
		$this->billingQ = new Billing();
		$this->sellerQ = new Seller();
	}

    public function index(){

			try {
    	$start_run_time = time();
		$isError = false;
    	$total_records = 0;

    	$date_today = date('Y-m-d');
		$date_today_str = strtotime($date_today);
		$day3before_expdate = (date('Y-m-d',$date_today_str + (60*60*24*3)));
		$day_expdate = (date('Y-m-d',$date_today_str));
		$day3after_expdate = (date('Y-m-d',$date_today_str - (60*60*24*3)));
		$day4after_expdate = (date('Y-m-d',$date_today_str - (60*60*24*4)));

		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Seller Trial Period Checker', true));

		$q = new TrialPeriod();

		//send email for 3days before exp
		$d = $q->getRecordsByDateEnd($day3before_expdate);
		foreach($d as $data){
			$this->_send_email($data->seller_id, 1);
			$total_records++;
		}

		//send email for exp today
		$d = $q->getRecordsByDateEnd($day_expdate);
		foreach($d as $data){
			$this->_send_email($data->seller_id, 2);
			$u = Seller::where('id',$data->seller_id)
				  ->first();

			
			$u->is_trial = 0;

			$b = Billing::where('seller_id',$data->seller_id)
						->first();

			if(isset($b))
			{
				if($b->payment_valid == false)
				{
					if(is_null($b->payment_invalid_date))
					{
						$b->payment_invalid_date = Carbon::now();
						$b->save();
					}
				}
			}
			$u->save();
			$total_records++;
		}
		//send email for 3days after exp
		$d = $q->getRecordsByDateEnd($day3after_expdate);
		foreach($d as $data){
			$this->_send_email($data->seller_id, 3);
			$total_records++;
		}

		//delete account for 4th day after exp
		// $d = $q->getRecordsByDateEnd($day4after_expdate);
		// foreach($d as $data){
		// 	$this->automaticRemoveAccount($data->seller_id);
		// 	$total_records++;
		// }

		$end_run_time = time();
    	$message['message'] = "Seller Trial Period Checker Cron Script Run Successfully!";
		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = 1;
		$message['total_records'] = $total_records;
		$message['isError'] = $isError;
		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Seller Trial Period Checker', false, $message));
		} catch (\Exception $e) {
			$end_run_time = time();
			$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
			$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
			$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
			$message['tries'] = 1;
			$message['total_records'] = (isset($total_records) ? $total_records : 0);
			$message['isError'] = $isError;
			$message['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Seller Trial Period Checker(error)', false, $message));
		}


		}

    private function _send_email($sellerid, $flag = 1){

		echo $sellerid;
		$has_creditcard=false;

		$has_creditcard = $this->_hasCreditCard($sellerid);

		if(!$has_creditcard){
			$d = $this->sellerQ->getRecords('sellers', array('*'), array('id'=>$sellerid));
			// print_r($d);
			$to = $d[0]->email;
			$fname = $d[0]->firstname;
			$lname = $d[0]->lastname;
			$sub = 'Trendle.io Trial Period';
			$msg = $this->getEmailBody($flag);

			$this->sendMail($fname, $lname, $to, $msg, $sub);
		}
	}

	private function _hasCreditCard($sellerid){

		$has_creditcard=false;

		$user_billing_info = $this->billingQ->getRecords('billings', array('*'), array('seller_id'=>$sellerid));
		if(count($user_billing_info) > 0) {
			if($user_billing_info{0}->stripe_id != "") {
				echo "stripe_registered";
				$has_creditcard=true;
			} else {
				echo "not_stripe_registered";
				$has_creditcard=false;
			}
		} else {
			echo "not_stripe_registered";
			$has_creditcard=false;
		}

		return $has_creditcard;
	}

	private function sendMail($fname, $lname, $to, $message_body, $subject){
		//backup default config
		$this->to = $to;
		$this->subject = $subject;
		$backup = Config::get('mail');
		//set new config for sparkpost
   		Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));

   		$head="";

		Mail::send('emails.sellertrialperiod', array('message_body' => $message_body, 'fname' => $fname, 'lname' => $lname), function($message) use ($head)
		{
		    $message->to($this->to)
		    		->subject($this->subject)
		    		->from('info@trendle.io' , 'Trendle.io');
		});

		//restore default config
		Config::set('mail', $backup);
	}


	public function automaticRemoveAccount($seller_id) {

		echo $seller_id;
		$has_creditcard=false;

        $reason = 'no credit card provided until the end of 3rd day after expiration';

		$has_creditcard = $this->_hasCreditCard($seller_id);

		if(!$has_creditcard){
			// BYE BYE

			// Update seller status
			Seller::where('id', $seller_id)->update(['reason_for_leaving' => $reason, 'is_deleted' => 1, 'is_trial' => 0]);

			// remove all seller users
			User::where('seller_id', $seller_id)->delete();

			echo " account removed";
		}
        // -------------------
    }

	private function getEmailBody($flag){
		$msg = "";
		switch ($flag) {
			case '1':
				# code... // 3 days before expiry date
				$msg='Your Free Trial Ends in 3 days!';
				echo ' 3days before';
				echo '<br>';

				break;
			case '2':
				# code... On Expiry date
				$msg='Your Free Trial Ends today!';

				echo ' expire today';
				echo '<br>';

				break;
			case '3':
				# code... 3 days after expiry date
				$msg='Your Free Trial Ends 3 days ago!';
				echo ' 3days after';
				echo '<br>';

				break;
		}
		return $msg;
	}
}
