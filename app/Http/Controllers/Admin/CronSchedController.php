<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Seller;
use App\CronMasterList;
use App\SellerCronSchedule;
use App\Log;

class CronSchedController extends Controller
{
    public function __contruct()
    {
        $this->middleware('guest:admin');
    }

    public function index(){
        $seller = Seller::all();
        return view('admin.cronsched.cronsched', ['seller'=>$seller]);
    }
    public function getSellerCron(Request $request){
    	$q = new SellerCronSchedule();
    	$seller_crons = $q->getRecords(array('*','seller_cron_schedules.id as sid'), ['seller_cron_schedules.seller_id'=>$request->seller_id]);
    	$table_data = array();
    	foreach ($seller_crons as $seller_cron) {
    		$data = array();
            $data['rowId'] = $seller_cron->sid;
            $data['DT_RowId'] = $seller_cron->sid;
    		$data[0] = $seller_cron->description;
    		$data[1] = $seller_cron->route;
    		$data[2] = $seller_cron->minutes;
    		$data[3] = $seller_cron->hours;
    		$data[4] = $seller_cron->day_of_month;
    		$data[5] = $seller_cron->month;
    		$data[6] = $seller_cron->day_of_week;
            //if($seller_cron->isactive == 0) $data[7] = 'Inactive';
            //else $data[7] = 'Active';
            $data[7] = $seller_cron->isactive;
    		$table_data[] = $data;
    	}
    	echo json_encode($table_data);
    }

    public function getNotSelectedCrons(Request $request){
    	$q = new SellerCronSchedule();
    	$q2 = new CronMasterList();
    	$seller_crons = $q->getRecords(array('*'), ['seller_cron_schedules.seller_id'=>$request->seller_id]);
    	$cron_ids = array();
    	foreach ($seller_crons as $seller_cron) {
    		$cron_ids[] = $seller_cron->cron_id;
    	}
    	if(count($cron_ids)>0) $crons = $q2->where('is_seller_cron', 1)->whereNotIn('id',$cron_ids)->get();
    	else $crons = $q2->where('is_seller_cron', 1)->get();
    	$data_table = array();
    	foreach ($crons as $cron) {
    		$data = array();
    		$data['rowId'] = $cron->id;
    		$data[] = '<input type="checkbox" name="crons" value="'.$cron->id.'"/>';
    		$data[] = $cron->id;
    		$data[] = $cron->description;
    		$data[] = $cron->route;
            $data[] = '<input style="width:100%;" id="inp_minutes_'.$cron->id.'" name="inp_minutes_'.$cron->id.'"/>';
            $data[] = '<input style="width:100%;" id="inp_hours_'.$cron->id.'" name="inp_hours_'.$cron->id.'"/>';
            $data[] = '<input style="width:100%;" id="inp_dom_'.$cron->id.'" name="inp_dom_'.$cron->id.'"/>';
            $data[] = '<input style="width:100%;" id="inp_month_'.$cron->id.'" name="inp_month_'.$cron->id.'"/>';
            $data[] = '<input style="width:100%;" id="inp_dow_'.$cron->id.'" name="inp_dow_'.$cron->id.'"/>';
            $data[] = '<select style="width:100%;" id="inp_isactive_'.$cron->id.'" name="inp_isactive_'.$cron->id.'"><option>0</option><option>1</option></select>';
    		$data_table[] = $data;
    	}
    	echo json_encode($data_table);
    }

    public function addCronToSeller(Request $request){
        $ids = explode('-', rtrim($request->cron_ids,'-'));
        $id_props = explode('+', rtrim($request->cron_id_prop,'+'));
        for ($i=0; $i < count($ids); $i++) { 
            $prop = explode('-', $id_props[$i]);
            $q = new SellerCronSchedule();
            $q->cron_id = $ids[$i];
            $q->seller_id = $request->seller_id;
            $q->minutes = $prop[0];
            $q->hours = $prop[1];
            $q->day_of_month = $prop[2];
            $q->month = $prop[3];
            $q->day_of_week = $prop[4];
            $q->date_created = date('Y-m-d H:i:s');
            $q->isactive = $prop[5];
            echo $q->save();

        }
    }

    public function updateSellerCron(Request $request){
        //value, row_id, seller_id, column
        //var_dump($request);
        $q = SellerCronSchedule::find($request->row_id);
        switch ($request->column) {
            case 2:
                $q->minutes = $request->value;
                break;
            case 3:
                $q->hours = $request->value;
                break;
            case 4:
                $q->day_of_month = $request->value;
                break;
            case 5:
                $q->month = $request->value;
                break;
            case 6:
                $q->day_of_week = $request->value;
                break;
            case 7:
                $q->isactive = $request->value;
                break;
            
        }
        $q->save();
        echo $request->value;
    }
    public function cronlogs(){
        $seller = Seller::all();
        //$desc = CronLog::all()->groupBy('description');
        $desc = Log::distinct()->select('description')->get();
        return view('admin.cronlogs.cronlogs', ['seller'=>$seller, 'desc'=>$desc]);
    }
    public function getLogs(Request $request){
        $logs = Log::all();
        $isget = false;
        if($request->seller_id != 0 AND $request->description != 0){
            $log = Log::all()->where('seller_id',$request->seller_id)->where('description', $request->description);
        }
        else if($request->seller_id != 0){ 
            $logs = Log::all()->where('seller_id',$request->seller_id); 
            $isget=true; 
        }else if($request->description != 0){ 
            $logs = Log::all()->where('description', $request->description); 
            $isget=true; 
        }else{
            $logs = Log::all();
        }
        
        $table_data = array();
        foreach ($logs as $log) {
            $data = array();
            $data['DT_RowId'] = $log->sid;
            $data[0] = $log->description;
            $data[1] = $log->date_sent;
            $data[2] = $log->subject;
            $data[3] = $log->api_used;
            $data[4] = $log->start_time;
            $data[5] = $log->end_time;
            $data[6] = $log->records_fetched;
            $data[7] = $log->message;
            $data[8] = ((strtotime($log->end_time) - strtotime($log->start_time))/60). " min/s";
            $table_data[] = $data;
        }
        echo json_encode($table_data);
    }
}
