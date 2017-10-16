<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;

use App\MarketplaceAssign;
use App\FbaFulfillmentInventoryReceipts;
use App\Log;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use App\Seller;
use Carbon\Carbon;
use App\UniversalModel;

use Mail;

class UpdateFBAFulfillmentInventoryReceiptsController extends Controller
  {
    private $seller_id;
    private $mkp='';

    public function index(){
    try {

      ini_set('memory_limit', '-1');
      ini_set("max_execution_time", 0);  // on
      ob_start();
      $total_records = 0;

      $univ = new UniversalModel();

      if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
        {
          echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
            exit;
        }
        else
        {
            $this->seller_id = trim(Input::get('seller_id'));
        }

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

      //response for mail
      $time_start = time();
      $cronname='FBA Fulfillment Inventory Receipts Data';
      $isError=false;
      $message = $cronname . " Cron Successfully Fetch Data!";
      $response['time_start'] = date('Y-m-d H:i:s');
      $response['total_time_of_execution'] = 0;
      $response['message'] = $message;
      $response['isError'] = false;
      $response['tries'] = 0;
      $tries=0;

      $report_type = '_GET_FBA_FULFILLMENT_INVENTORY_RECEIPTS_DATA_';
      $maintable = 'fba_fulfillment_inventory_receipts';
      $response = array();

      $isEmpty = false;
      $unified_mkp_data = true;
      $q= new MarketplaceAssign();

      if( Input::get('mkp') != null OR Input::get('mkp') != "" )
      {
          $this->mkp = trim(Input::get('mkp'));
      }





      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification($cronname.' for seller'.$this->seller_id.' mkp'.$this->mkp, true));
      $mkp_assign = $q->getRecords(config('constant.tables.mkp'),array('*'),array('seller_id'=>$this->seller_id),array());

      $seller_details = array();
  		$data = array();
      $where = array('seller_id'=>$this->seller_id);
  		if(count($mkp_assign)>0) $data_count = $univ->getRecords($maintable,array('*'),$where,array(),true);
  		$start_date = '-1 month';
  		$end_date = null;
  		$m_ctr = "-1 month";
  		if(count($data_count)>0  AND !$isEmpty) $isEmpty = false;
  		else{
  			$isEmpty = true;
  			$start_date = "-20 months";
  		}
      // dd($start_date);
      if(count($mkp_assign)<=0){
        $response['time_start'] = date('Y-m-d H:i:s');
        $response['time_end'] = date('Y-m-d H:i:s');
        $response['isError'] = true;
        $response['message'] = "No Marketplace assigned!";
        $response['tries'] = 0;
        $message = "No Marketplace assigned!";
        echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
      }

      foreach ($mkp_assign as $value) {
        if($value->marketplace_id == 1){
          $mkp = config('constant.amz_keys.na.marketplaces');
          $mkpstring = 'na';
        }
        if($value->marketplace_id == 2) {
          $mkp = config('constant.amz_keys.eu.marketplaces');
          $mkpstring = 'eu';
        }

        $merchantId = $value->mws_seller_id;
        $MWSAuthToken = $value->mws_auth_token;
          $columnChecked=false;
          foreach ($mkp as $key => $mkp_data) {
            $tries++;
            $country = $key;
            $init;
            $init = array();
            $init = array(
            'merchantId'    => $merchantId,
                  'MWSAuthToken'  => $MWSAuthToken,		//mkp_auth_token
                  'country'		=> $country,			//mkp_country
                  'marketPlace'	=> $mkp_data['id'],		//seller marketplace id
                  'start_date'	=> $start_date,
        	    		'end_date'		=> null,
              'name'			=> $cronname
              );

            $amz = new MWSFetchReportClass();
            $amz->initialize($init);
            $return = $amz->fetchData($report_type);
            
            echo count($return['data']);
            echo "<br>Saving to database...";

            $where=array('seller_id'=> $this->seller_id,'mkp'=>$mkpstring);
            $ss = FbaFulfillmentInventoryReceipts::where($where)->take(1)->get();
            if(count($ss) > 0) $isEmpty = false;
            else $isEmpty = true;


            if(($columnChecked==false)&&(isset($return['data'][0]))){
              $amz->checkForNewColumn($maintable,$return['data'][0]);
              $columnChecked=true;
            }
            $temp_data_arr=array();

            foreach ($return['data'] as $value) {
              $item2 = array();
              $item2['received_date'] = (!isset($value['received_date'])) ? "" : $value['received_date'];
              $item2['fnsku'] = (!isset($value['fnsku'])) ? "" : $value['fnsku'];
              $item2['sku'] = (!isset($value['sku'])) ? "" : $value['sku'];
              $item2['mkp'] = $mkpstring;
              $item2['seller_id'] = $this->seller_id;

              $item = array();
              $item['seller_id'] = $this->seller_id;
              $item['received_date'] = (!isset($value['received_date'])) ? "" : $value['received_date'];
              $item['fnsku'] = (!isset($value['fnsku'])) ? "" : $value['fnsku'];
              $item['sku'] = (!isset($value['sku'])) ? "" : $value['sku'];
              $item['product_name'] = (!isset($value['product_name'])) ? "" : $value['product_name'];
              $item['quantity'] = (!isset($value['quantity'])) ? "0" : $value['quantity'];
              $item['fba_shipment_id'] = (!isset($value['fba_shipment_id'])) ? "0" : $value['fba_shipment_id'];
              $item['fulfillment_center_id'] = (!isset($value['fulfillment_center_id'])) ? "0" : $value['fulfillment_center_id'];
              // $item['created_at'] = date('Y-m-d H:i:s');
              // $item['updated_at'] = date('Y-m-d H:i:s');

              $total_records++;
              if(!$isEmpty){
                if(!$univ->isExist($maintable,$item2)){
                  $temp_data_arr[]=$item;
                }else{
                  $item['updated_at'] = date('Y-m-d H:i:s');
                  $ffir = new FbaFulfillmentInventoryReceipts();
                  $ffir::where($item2)->update($item);
                }
              }else{
                $ffir = new FbaFulfillmentInventoryReceipts();
                $item['created_at'] = date('Y-m-d H:i:s');
                $ffir->seller_id=$this->seller_id;
                $ffir->received_date=$item['received_date'];
                $ffir->fnsku=$item['fnsku'];
                $ffir->sku=$item['sku'];
                $ffir->product_name=$item['product_name'];
                $ffir->quantity=$item['quantity'];
                $ffir->fba_shipment_id=$item['fba_shipment_id'];
                $ffir->fulfillment_center_id=$item['fulfillment_center_id'];
                $ffir->mkp=$mkpstring;
                $ffir->created_at=$item['created_at'];
                $ffir->save();
              }
            }
            foreach($temp_data_arr as $passedrecord){
              $ffir = new FbaFulfillmentInventoryReceipts();
              $passedrecord['created_at'] = date('Y-m-d H:i:s');
              $ffir->seller_id=$this->seller_id;
              $ffir->received_date=$passedrecord['received_date'];
              $ffir->fnsku=$passedrecord['fnsku'];
              $ffir->sku=$passedrecord['sku'];
              $ffir->product_name=$passedrecord['product_name'];
              $ffir->quantity=$passedrecord['quantity'];
              $ffir->fba_shipment_id=$passedrecord['fba_shipment_id'];
              $ffir->fulfillment_center_id=$passedrecord['fulfillment_center_id'];
              $ffir->mkp=$mkpstring;
              $ffir->created_at=$passedrecord['created_at'];
              $ffir->save();
            }

            if($unified_mkp_data){
              break;
            }
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
      $log->description = 'Products';
      $log->date_sent = date('Y-m-d H:i:s');
      $log->subject = 'Cron Notification for '.$cronname;
      $log->api_used = $report_type;
      $log->start_time = $response['time_start'];
          $log->end_sent = date('Y-m-d H:i:s');
      $log->record_fetched = $total_records;
      $log->message = $message;
      $log->save();

    Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification($cronname.' for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
    } catch (\Exception $e) {
      $time_end = time();
      $response['time_start'] = date('Y-m-d H:i:s', $time_start);
      $response['time_end'] = date('Y-m-d H:i:s', $time_end);
      $response['total_time_of_execution'] = ($time_end - $time_start)/60;
      $response['tries'] = 1;
      $response['total_records'] = (isset($total_records) ? $total_records : 0);
      $response['isError'] = $isError;
      $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification($cronname.' for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
    }
  }
}
