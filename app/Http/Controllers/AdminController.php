<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;


use App\Seller;
use App\CronMasterList;
use App\SellerCronSchedule;
use App\Log;
use App\FinancialEventsReport;
use App\InventoryAdjustmentReport;
use App\MarketplaceAssign;
use App\UniversalModel;
use App\AdminSeller;
use Auth;
use App\OrderIdClaim;
use App\FnskuClaim;
use App\Reimbursement;
use DB;
use App\Http\Helpers\HelpersFacade;

class AdminController extends Controller
{

  use AuthenticatesUsers;
  private $adminsellers;
  private $helper;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->adminsellers = new AdminSeller;
        $this->helper = new HelpersFacade;
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.dashboard.adminhome');
    }

    public function cronsched(){

        $q = Auth::user()->class;

        if(($q != "dev") && ($q != "admin"))
        {
          return redirect('admin');
        }


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

        $q = Auth::user()->class;

        if(($q != "dev") && ($q != "admin"))
        {
          return redirect('admin');
        }


        $seller = Seller::all();
        $desc = Log::distinct()->select('description')->get();
        return view('admin.cronlogs.cronlogs', ['seller'=>$seller, 'desc'=>$desc]);
    }
    public function getLogs(Request $request){
        if($request->seller_id != 0 AND $request->desc != '0'){
            $logs = Log::all()->where('seller_id',$request->seller_id)->where('description', $request->desc);
        }
        else if($request->seller_id != 0){
            $logs = Log::all()->where('seller_id',$request->seller_id);
            //$isget=true;
        }else if($request->desc != '0'){
            $logs = Log::all()->where('description', $request->desc);
            //$isget=true;
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

    public function getFulfillmentCenters(){
        $q = new UniversalModel();
        $fcid = $q->getRecords('fulfillment_country',array('*'),[],['country_code', 'asc']);

        $result= array();
        foreach ($fcid as $value) {
            $data = array();
            $data['rowId'] = $value->id;
            $data['DT_RowId'] = $value->id;
            $data[0] = $value->id;
            $data[1] = $value->fulfillment_center_id;
            $uk='';
            $it='';
            $fr='';
            $es='';
            $de='';
            $us='';
            $ca='';
            $n='';
            $country_code = strtolower($value->country_code);
            if($country_code == 'uk')
                $uk = 'selected=selected';
            else if($country_code == 'it')
                $it = 'selected=selected';
            else if($country_code == 'fr')
                $fr = 'selected=selected';
            else if($country_code == 'es')
                $es = 'selected=selected';
            else if($country_code == 'de')
                $de = 'selected=selected';
            else if($country_code == 'us')
                $us = 'selected=selected';
            else if($country_code == 'ca')
                $ca = 'selected=selected';
            else
                $n = 'selected=selected';

            $str = '<select id="f_country_'.$value->fulfillment_center_id.'" onchange="updateFulfillmentCountry(\''.$value->id.'\',\''.$value->fulfillment_center_id.'\');">';
            $str.= '<option '.$n.'></option>';
            $str.= '<option '.$uk.'>uk</option>';
            $str.= '<option '.$it.'>it</option>';
            $str.= '<option '.$fr.'>fr</option>';
            $str.= '<option '.$es.'>es</option>';
            $str.= '<option '.$de.'>de</option>';
            $str.= '<option '.$us.'>us</option>';
            $str.= '<option '.$ca.'>ca</option>';
            $str.='</select>';
            $data[2] = $str;
            $result[] = $data;
        }
        echo json_encode($result);
    }

    public function updateFulfillmentCenter(Request $request){
        $q = new UniversalModel();
        $data['country_code'] = $request->country_code;
        $where['id'] = $request->id;
        $q->updateData('fulfillment_country', $where, $data);

        $data = array();
        $where = array();
        $q = new UniversalModel();
        $where['fulfillment_center_id'] = $request->id;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $q->updateData('inventory_adjustment_reports', $where, $data);
    }

    public function fbarefund(){

        $q = Auth::user()->class;

        if(($q != "cs") && ($q != "admin"))
        {
          return redirect('admin');
        }

        return view('admin.fbarefunds.index');
    }

    public function getfbasellers(){
        $as = AdminSeller::all();
        $response = array();
        foreach ($as as $val) {
            $s = Seller::where('email', '=', $val->seller_email)->first();
            $data = array();
            $data['rowId'] = $val->id;
            $data['DT_RowId'] = $val->id;
            $data['id_seller'] = $s->id;
            $data['country'] = $val->country_code;
            $data['fba_mode'] = $val->fba_mode;
            $data[] = $val->company_name;
            $data[] = $val->seller_email;
            $difference = $val->total_owed - $val->total_saved;
            if ($val->country_code == 'us') $country = 'United States';
            elseif ($val->country_code == 'ca') $country = 'Canada';
            elseif ($val->country_code == 'uk') $country = 'United Kingdom';
            elseif ($val->country_code == 'fr') $country = 'France';
            elseif ($val->country_code == 'de') $country = 'Germany';
            elseif ($val->country_code == 'it') $country = 'Italy';
            elseif ($val->country_code == 'es') $country = 'Spain';
            $data[] = $country;
            $data[] = $val->central_login_email;
            $data[] = $val->central_login_password;
            $data[] = $val->support_cases;
            if ($val->currency == 'usd') $curr = '$';
            elseif ($val->currency == 'gbp') $curr = '£';
            elseif ($val->currency == 'eur') $curr = '€';
            $data[] = $curr.' '.number_format($difference, 2);
            $data[] = $curr.' '.number_format($val->total_saved, 2);
            $data[] = ($val->fba_mode == 'DIY') ? '' : $curr.' '.number_format($val->total_collected, 2);
            $data[] = ($val->fba_mode == 'DIY') ? '' : $curr.' '.number_format($val->total_owed_to_collect, 2);
            $data[] = $val->status;
            if(isset($s->billing))
            {
                if($s->billing->payment_valid = -1 || $s->billing->payment_valid = 0)
                $data[] = 'No';
                else
                {
                    $data[] = 'Yes';
                }
            }
            else
            {
                $data[] = 'No';
            }
            $data[] = $val->fba_mode;
            $data[] = 'Seller ID: '.$s->id;
            $response[] = $data;
        }
        echo json_encode($response);
    }

    public function getfbasellersFiltered(Request $request){
        $companyName = $request->companyname;
        $country = $request->country;

        if((!empty($companyName)) && (!empty($country)))
        {
            $as = DB::table('admin_sellers')
                  ->where('company_name', 'like', '%'.$companyName.'%')
                  ->where('country_code', '=', $country)
                  ->get();
        }
        else if(empty($companyName) && (!empty($country)))
        {
            $as = DB::table('admin_sellers')
                  ->where('country_code','=', $country)
                  ->get();
        }
        else if(!empty($companyName) && (empty($country)))
        {
            $as = DB::table('admin_sellers')
                  ->where('company_name', 'like', '%'.$companyName.'%')
                  ->get();
        }
        else
        {
            $as = AdminSeller::all();
        }

        $response = array();
        foreach ($as as $val) {
            $s = Seller::where('email', '=', $val->seller_email)->first();
            $data = array();
            $data['rowId'] = $val->id;
            $data['DT_RowId'] = $val->id;
            $data['id_seller'] = $s->id;
            $data['country'] = $val->country_code;
            $data['fba_mode'] = $val->fba_mode;
            $total_saved = (int)$val->total_saved;
            $difference = $val->total_owed - $val->total_saved;
            $data[] = $val->company_name;
            $data[] = $val->seller_email;
            if ($val->country_code == 'us') $country = 'United States';
            elseif ($val->country_code == 'ca') $country = 'Canada';
            elseif ($val->country_code == 'uk') $country = 'United Kingdom';
            elseif ($val->country_code == 'fr') $country = 'France';
            elseif ($val->country_code == 'de') $country = 'Germany';
            elseif ($val->country_code == 'it') $country = 'Italy';
            elseif ($val->country_code == 'es') $country = 'Spain';
            $data[] = $country;
            $data[] = $val->central_login_email;
            $data[] = $val->central_login_password;
            $data[] = $val->support_cases;
            if ($val->currency == 'usd') $curr = '$';
            elseif ($val->currency == 'gbp') $curr = '£';
            elseif ($val->currency == 'eur') $curr = '€';
            $data[] = $curr.' '.number_format($difference, 2);
            $data[] = $curr.' '.number_format($val->total_saved, 2);
            $data[] = ($val->fba_mode == 'DIY') ? '' : $curr.' '.number_format($val->total_collected, 2);
            $data[] = ($val->fba_mode == 'DIY') ? '' : $curr.' '.number_format($val->total_owed_to_collect, 2);
            $data[] = $val->status;
            $data[] = '';
            $data[] = $val->fba_mode;
            $data[] = 'Seller ID: '.$s->id;
            $response[] = $data;
        }
        echo json_encode($response);
    }

    public function update_adminsellers(Request $request){
      $id = $request->row_id;
      $colindex = $request->column;
      $newval = $request->newval;

      $col = $this->_get_adminseller_column_by_UItable_columnindex($colindex);

      $a = $this->_make_adminsellers_update_array($id,$col,$newval);

      $this->adminsellers->updateRecord($a);

      return $newval;
    }

    public function update_adminOIC(Request $request){
      $response = (object) null;

      $id = $request->row_id;
      $colindex = $request->column;
      $newval = $request->newval;
      $order_id = $request->order_id;
      $claim_amount = $request->claim_amount;

      $oic = OrderIdClaim::find($id);      
      $status = $oic->status;

      if ($colindex == 13) {
        $case_id = $newval;

        $r = Reimbursement::where('case_id', '=', $case_id)
                          ->get();

        $rid = array();
        $i = 0;
        $tot_amount_r = 0;
        foreach ($r as $v) {
            $i++;
            $rid[$i] = $v->reimbursement_id;
            if ($v->amount_total == 0) {
                $tot_amount_r += ($v->amount_per_unit*$v->quantity_reimbursed_total);
            } else {
                $tot_amount_r += $v->amount_total;
            }
        }

        if (isset($rid[1])) {
            $oic->reimbursement_id1 = $rid[1];
            $response->rid1 = $rid[1];
        } else {
            $response->rid1 = '';
        }
        if (isset($rid[2])) {
            $oic->reimbursement_id2 = $rid[2];
            $response->rid2 = $rid[2];
        } else {
            $response->rid2 = '';
        }
        if (isset($rid[3])) {
            $oic->reimbursement_id3 = $rid[3];
            $response->rid3 = $rid[3];
        } else {
            $response->rid3 = '';
        }
        if (isset($tot_amount_r)) {
            if ($tot_amount_r == 0) {
                $response->tar = 0;
                $response->dif = (double)$claim_amount;
            } else {
                $oic->total_amount_reimbursed = round($tot_amount_r,2);
                $oic->difference = round((double)$claim_amount - $tot_amount_r,2);
                $response->tar = round($tot_amount_r,2);
                $response->dif = round((double)$claim_amount - $tot_amount_r,2);
            }
        } else {
            $response->tar = 0;
            $response->dif = (double)$claim_amount;
        }

        $oic->support_ticket = $newval;
      } elseif ($colindex == 14) {
        $case_id = $newval;

        $r = Reimbursement::where('case_id', '=', $case_id)
                          ->get();

        $rid = array();
        $i = 0;
        $tot_amount_r = 0;
        foreach ($r as $v) {
            $i++;
            $rid[$i] = $v->reimbursement_id;
            if ($v->amount_total == 0) {
                $tot_amount_r += ($v->amount_per_unit*$v->quantity_reimbursed_total);
            } else {
                $tot_amount_r += $v->amount_total;
            }
        }

        if ($oic->reimbursement_id1 == '') {
            if (isset($rid[1])) {
                $oic->reimbursement_id1 = $rid[1];
                $response->rid1 = $rid[1];
            } else {
                $response->rid1 = '';
            }
        } else {
            $response->rid1 = $oic->reimbursement_id1;
        }
        if ($oic->reimbursement_id2 == '') {
            if (isset($rid[1])) {
                if ($oic->reimbursement_id1 != $rid[1]) {
                    $oic->reimbursement_id2 = $rid[1];
                    $response->rid2 = $rid[1];
                } else {
                    if (isset($rid[2])) {
                        if ($oic->reimbursement_id1 != $rid[2]) {
                            $oic->reimbursement_id2 = $rid[2];
                            $response->rid2 = $rid[2];
                        } else {
                            if (isset($rid[3])) {
                                if ($oic->reimbursement_id1 != $rid[3]) {
                                    $oic->reimbursement_id2 = $rid[3];
                                    $response->rid2 = $rid[3];
                                }
                            } else {
                                $response->rid2 = '';
                            }
                        }
                    } else {
                        $response->rid2 = '';
                    }
                }
            } else {
                $response->rid2 = '';
            }
        } else {
            $response->rid2 = $oic->reimbursement_id2;
        }
        if ($oic->reimbursement_id3 == '') {
            if (isset($rid[1])) {
                if ($oic->reimbursement_id1 != $rid[1] || $oic->reimbursement_id2 != $rid[1]) {
                    $oic->reimbursement_id3 = $rid[1];
                    $response->rid3 = $rid[1];
                } else {
                    if (isset($rid[2])) {
                        if ($oic->reimbursement_id1 != $rid[2] || $oic->reimbursement_id2 != $rid[2]) {
                            $oic->reimbursement_id3 = $rid[2];
                            $response->rid3 = $rid[2];
                        } else {
                            if (isset($rid[3])) {
                                if ($oic->reimbursement_id1 != $rid[3] || $oic->reimbursement_id2 != $rid[3]) {
                                    $oic->reimbursement_id3 = $rid[3];
                                    $response->rid3 = $rid[3];
                                }
                            } else {
                                $response->rid3 = '';
                            }
                        }
                    } else {
                        $response->rid3 = '';
                    }
                }
            } else {
                $response->rid3 = '';
            }
        } else {
            $response->rid3 = $oic->reimbursement_id3;
        }
        if ($oic->total_amount_reimbursed == 0) {
            if (isset($tot_amount_r)) {
                if ($tot_amount_r == 0) {
                    $response->tar = 0;
                    $response->dif = (double)$claim_amount;
                } else {
                    $oic->total_amount_reimbursed = round($tot_amount_r,2);
                    $oic->difference = round((double)$claim_amount - $tot_amount_r,2);
                    $response->tar = round($tot_amount_r,2);
                    $response->dif = round((double)$claim_amount - $tot_amount_r,2);
                }
            } else {
                $response->tar = 0;
                $response->dif = (double)$claim_amount;
            }
        } else {
            if (isset($tot_amount_r)) {
                if ($tot_amount_r == 0) {
                    $response->tar = 0 + $oic->total_amount_reimbursed;
                    $response->dif = round((double)$claim_amount - $oic->total_amount_reimbursed,2);
                } else {
                    $orig = $oic->total_amount_reimbursed;
                    $oic->total_amount_reimbursed = round($tot_amount_r + $orig,2);
                    $oic->difference = round((double)$claim_amount - ($tot_amount_r + $orig),2);
                    $response->tar = round($tot_amount_r + $orig,2);
                    $response->dif = round((double)$claim_amount - ($tot_amount_r + $orig),2);
                }
            } else {
                $response->tar = 0 + $oic->total_amount_reimbursed;
                $response->dif = round((double)$claim_amount - $oic->total_amount_reimbursed,2);
            }
        }        

        $oic->support_ticket2 = $newval;
      } elseif ($colindex == 19) {
        $oic->status = $newval;
      } elseif ($colindex == 20) {
        $oic->comments = $newval;
      }
      $oic->save();

      if ($status == 'All Ok' || $status == 'Refund issued by seller' || $status == 'Amz won'."'".'t refund difference') {
        $response->dif = 0;
      }

      $response->value = $newval;
      return json_encode($response);
    }

    public function update_adminFNSKU(Request $request){
      $response = (object) null;

      $id = $request->row_id;
      $colindex = $request->column;
      $newval = $request->newval;
      $fnsku = $request->fnsku;
      $total_owed = $request->total_owed;

      $fc = FnskuClaim::find($id);
      $status = $fc->status;

      if ($colindex == 19) {
        $case_id = $newval;

        $r = Reimbursement::where('case_id', '=', $case_id)
                          ->get();

        $rid = array();
        $i = 0;
        $tot_amount_r = 0;
        foreach ($r as $v) {
            $i++;
            $rid[$i] = $v->reimbursement_id;
            if ($v->amount_total == 0) {
                $tot_amount_r += ($v->amount_per_unit*$v->quantity_reimbursed_total);
            } else {
                $tot_amount_r += $v->amount_total;
            }
        }

        if (isset($rid[1])) {
            $fc->reimbursement_id1 = $rid[1];
            $response->rid1 = $rid[1];
        } else {
            $response->rid1 = '';
        }
        if (isset($rid[2])) {
            $fc->reimbursement_id2 = $rid[2];
            $response->rid2 = $rid[2];
        } else {
            $response->rid2 = '';
        }
        if (isset($rid[3])) {
            $fc->reimbursement_id3 = $rid[3];
            $response->rid3 = $rid[3];
        } else {
            $response->rid3 = '';
        }
        if (isset($tot_amount_r)) {
            if ($tot_amount_r == 0) {
                $response->tar = 0;
                $response->dif = round($total_owed,2);
            } else {
                $fc->total_amount_reimbursed = round($tot_amount_r,2);
                $fc->difference = round((double)$total_owed - $tot_amount_r,2);
                $response->tar = round($tot_amount_r,2);
                $response->dif = round((double)$total_owed - $tot_amount_r,2);
            }
        } else {
            $response->tar = 0;
            $response->dif = round($total_owed,2);
        }

        $fc->support_ticket = $newval;
      } elseif ($colindex == 21) {
        $case_id = $newval;

        $r = Reimbursement::where('case_id', '=', $case_id)
                          ->get();

        $rid = array();
        $i = 0;
        $tot_amount_r = 0;
        foreach ($r as $v) {
            $i++;
            $rid[$i] = $v->reimbursement_id;
            if ($v->amount_total == 0) {
                $tot_amount_r += ($v->amount_per_unit*$v->quantity_reimbursed_total);
            } else {
                $tot_amount_r += $v->amount_total;
            }
        }

        if ($fc->reimbursement_id1 == '') {
            if (isset($rid[1])) {
                $fc->reimbursement_id1 = $rid[1];
                $response->rid1 = $rid[1];
            } else {
                $response->rid1 = '';
            }
        } else {
            $response->rid1 = $fc->reimbursement_id1;
        }
        if ($fc->reimbursement_id2 == '') {
            if (isset($rid[1])) {
                if ($fc->reimbursement_id1 != $rid[1]) {
                    $fc->reimbursement_id2 = $rid[1];
                    $response->rid2 = $rid[1];
                } else {
                    if (isset($rid[2])) {
                        if ($fc->reimbursement_id1 != $rid[2]) {
                            $fc->reimbursement_id2 = $rid[2];
                            $response->rid2 = $rid[2];
                        } else {
                            if (isset($rid[3])) {
                                if ($fc->reimbursement_id1 != $rid[3]) {
                                    $fc->reimbursement_id2 = $rid[3];
                                    $response->rid2 = $rid[3];
                                }
                            } else {
                                $response->rid2 = '';
                            }
                        }
                    } else {
                        $response->rid2 = '';
                    }
                }
            } else {
                $response->rid2 = '';
            }
        } else {
            $response->rid2 = $fc->reimbursement_id2;
        }
        if ($fc->reimbursement_id3 == '') {
            if (isset($rid[1])) {
                if ($fc->reimbursement_id1 != $rid[1] || $fc->reimbursement_id2 != $rid[1]) {
                    $fc->reimbursement_id3 = $rid[1];
                    $response->rid3 = $rid[1];
                } else {
                    if (isset($rid[2])) {
                        if ($fc->reimbursement_id1 != $rid[2] || $fc->reimbursement_id2 != $rid[2]) {
                            $fc->reimbursement_id3 = $rid[2];
                            $response->rid3 = $rid[2];
                        } else {
                            if (isset($rid[3])) {
                                if ($fc->reimbursement_id1 != $rid[3] || $fc->reimbursement_id2 != $rid[3]) {
                                    $fc->reimbursement_id3 = $rid[3];
                                    $response->rid3 = $rid[3];
                                }
                            } else {
                                $response->rid3 = '';
                            }
                        }
                    } else {
                        $response->rid3 = '';
                    }
                }
            } else {
                $response->rid3 = '';
            }
        } else {
            $response->rid3 = $fc->reimbursement_id3;
        }
        if ($fc->total_amount_reimbursed == 0) {
            if (isset($tot_amount_r)) {
                if ($tot_amount_r == 0) {
                    $response->tar = 0;
                    $response->dif = (double)$total_owed;
                } else {
                    $fc->total_amount_reimbursed = round($tot_amount_r,2);
                    $fc->difference = round((double)$total_owed - $tot_amount_r,2);
                    $response->tar = round($tot_amount_r,2);
                    $response->dif = round((double)$total_owed - $tot_amount_r,2);
                }
            } else {
                $response->tar = 0;
                $response->dif = (double)$total_owed;
            }
        } else {
            if (isset($tot_amount_r)) {
                if ($tot_amount_r == 0) {
                    $response->tar = 0 + $fc->total_amount_reimbursed;
                    $response->dif = round((double)$total_owed - $fc->total_amount_reimbursed,2);
                } else {
                    $orig = $fc->total_amount_reimbursed;
                    $fc->total_amount_reimbursed = round($tot_amount_r + $orig,2);
                    $fc->difference = round((double)$total_owed - ($tot_amount_r + $orig),2);
                    $response->tar = round($tot_amount_r + $orig,2);
                    $response->dif = round((double)$total_owed - ($tot_amount_r + $orig),2);
                }
            } else {
                $response->tar = 0 + $fc->total_amount_reimbursed;
                $response->dif = round((double)$total_owed - $fc->total_amount_reimbursed,2);
            }
        }

        $fc->support_ticket2 = $newval;
      } elseif ($colindex == 25) {
        $fc->status = $newval;
      } elseif ($colindex == 26) {
        $fc->comments = $newval;
      }
      $fc->save();

      if ($status == 'All Ok' || $status == 'Refund issued by seller' || $status == 'Amz won'."'".'t refund difference') {
        $response->dif = 0;
      }

      $response->value = $newval;
      return json_encode($response);
    }

    private function _get_adminseller_column_by_UItable_columnindex($i){
      $r = '';
      switch($i){
        case 3: $r = 'central_login_email'; break;
        case 4: $r = 'central_login_password'; break;
        case 5: $r = 'support_cases'; break;
        case 10: $r = 'status'; break;
      }
      return $r;
    }

    private function _make_adminsellers_update_array($id,$col,$newval){
      return array(
        'id'=>$id,
        $col=>$newval
      );
    }

    public function updateStatusOIC(Request $request){

      $oci = OrderIdClaim::where('order_id', '=', $request->id)
                        ->first();
      $oci->status = $request->value;
      $oci->save();

      $seller_id = $oci->seller_id;

      $s = Seller::where('id', $seller_id)
                        ->first();

      $email = $s->email;
      $country = $oci->country_code;
      $total_saved = $this->getTotalSaved($email,$seller_id,$country);
    }


    public function getSellerOIC(Request $request){        
        $seller_id = $request->seller_id;
        $country = $request->country;
        $oic = OrderIdClaim::where('seller_id', '=', $seller_id)
                        ->where('country_code', '=', $country)
                        ->get();
        $response = array();
        foreach ($oic as $val) {
            if ($val->total_refunded < 0) {
            if ($val->claim_amount > 0){

            $open = ($val->status == 'Open') ? 'selected' : '';
            $ok = ($val->status == 'All Ok') ? 'selected' : '';
            $refund = ($val->status == 'Refund issued by seller') ? 'selected' : '';
            $amz = ($val->status == 'Amz won'."'".'t refund difference') ? 'selected' : '';

            $status =   '<div>'
                    .'<select class="form-control" onchange="oicupdateStatus(this)" id="'.$val->order_id.'" style="font-size: 12px;padding: 0px 0px">'
                      .'<option '.$open.'>Open</option>'
                      .'<optgroup label="Closed">'
                      .'<option '.$ok.'>All Ok</option>'
                      .'<option '.$refund.'>Refund issued by seller</option>'
                      .'<option '.$amz.'>Amz won'."'".'t refund difference</option>'
                      .'</optgroup>'
                    .'</select>'
                .'</div>';

                $array = ['All Ok','Refund issued by seller','Amz won'."'".'t refund difference'];

               $hidden = '<input type="hidden" id="clipboard-'.$val->order_id.'" value="'.$val->claim_type.'" />';
                $hidden0 = '<input type="hidden" id="clipboard0-'.$val->order_id.'" value="'.$val->status.'" />';
                $hidden1 = '<input type="hidden" id="clipboard1-'.$val->order_id.'" value="Order ID: '.$val->order_id.'" />';
                $hidden2 = '<input type="hidden" id="clipboard2-'.$val->order_id.'" value="Amount due: '.$val->claim_amount.'" />';
                $hidden3 = '<input type="hidden" id="clipboard3-'.$val->order_id.'" value="'.$val->detailed_disposition.'" />';
                $hidden4 = '<input type="hidden" id="clipboard4-'.$val->order_id.'" value="Reimbursement reason: '.$val->detailed_disposition.'" />';
                $hidden5 = '<input type="hidden" id="clipboard5-'.$val->order_id.'" value="Reimbursement amount: '.$val->total_adjusted.'" />';
                $hidden6 = '<input type="hidden" id="clipboard6-'.$val->order_id.'" value="'.$val->claim_amount.'" />';
                $hidden7 = '<input type="hidden" id="clipboard7-'.$val->order_id.'" value="Difference: '.round($val->difference,2).'" />';
                $hidden8 = '<input type="hidden" id="clipboard8-'.$val->order_id.'" value="'.$val->return_reason.'" />';
                $hidden9 = '<input type="hidden" id="clipboard9-'.$val->order_id.'" value="'.$val->quantity_unsellable.'" />';
                $hidden10 = '<input type="hidden" id="clipboard10-'.$val->order_id.'" value="'.$country.'" />';
                $hidden11 = '<input type="hidden" id="clipboard11-'.$val->order_id.'" value="'.$val->claim_amount.'" />';
                $calculation1 = ($val->total_refunded*(-1));
                $calculation = (string)$calculation1.'-'.(string)$val->total_ordered;
                $hidden12 = '<input type="hidden" id="clipboard12-'.$val->order_id.'" value="'.$calculation.'" />';
                $hidden13 = '<input type="hidden" id="clipboard13-'.$val->order_id.'" value="'.$val->claim_name.'" />';          
                $hidden14 = '<input type="hidden" id="clipboard14-'.$val->order_id.'" value="'.$val->quantity_unsellable.'" />';            
                $hidden15 = '<input type="hidden" id="clipboard15-'.$val->order_id.'" value="'.$val->fmv.'" />';

                $clip = '<button class="btn btn-primary btn-sm no-radius" id="clip-'.$val->order_id.'" onclick="oicClip(this)"> <i class="fa fa-clipboard"></i></button>';

                        $data = array();
                        $data['DT_RowId'] = $val->id;
                        $data[] = $clip.' '.$val->order_id.$hidden.$hidden0.$hidden1.$hidden2.$hidden3.$hidden4.$hidden5.$hidden6.$hidden7.$hidden8.$hidden9.$hidden10.$hidden11.$hidden12.$hidden13.$hidden14.$hidden15;
                        $data[] = $val->quantity_ordered;
                        $data[] = $val->quantity_refunded;
                        $data[] = $val->quantity_adjusted;
                        $data[] = $val->total_ordered;
                        $data[] = $val->total_refunded;
                        $data[] = $val->total_adjusted;
                        $data[] = $val->quantity_returned;
                        $data[] = $val->date_of_return;
                        $data[] = $val->over_45days; //$over_45days->over_45days;
                        $data[] = $val->claim_type;
                        $data[] = $val->claim_name;
                        $data[] = $val->detailed_disposition; //(is_null($val->oid)) ? "Item Not Returned" : $val->detailed_disposition;
                        $ave_sales3 = ($val->fmv3_quantity == 0) ? 0 : $val->fmv3_sales/$val->fmv3_quantity;
                        $data[] = '<span title="Total number of sales: '.$val->fmv3_quantity.' Average selling price: '.round($ave_sales3,2).'">'.$val->fmv3.'</span>';                        
                        $ave_sales = ($val->fmv_quantity == 0) ? 0 : $val->fmv_sales/$val->fmv_quantity;
                        $data[] = '<span title="Total number of sales: '.$val->fmv_quantity.' Average selling price: '.round($ave_sales,2).'">'.$val->fmv.'</span>';
                        $data[] = $val->claim_amount;
                        $data[] = $this->helper->editableColumnCell((string)$val->support_ticket,'Click to edit','supportTicketCell');
                        $data[] = $this->helper->editableColumnCell((string)$val->support_ticket2,'Click to edit','supportTicketCell2');
                        $data[] = $val->reimbursement_id1;
                        $data[] = $val->reimbursement_id2;
                        $data[] = $val->reimbursement_id3;
                        $data[] = $val->total_amount_reimbursed;
                        (in_array($val->status,$array) == true) ? $data[] = 0 : $data[] = round($val->difference,2);
                        // $data[] = round($val->difference,2);
                        $data[] = $status;
                        $data[] = $this->helper->editableColumnCell((string)$val->comments,'Click to edit','commentCell');
                        $response[] = $data;
                    // }
                // }
            }
            }
        }
        echo json_encode($response);
    }

    public function getSellerOICFiltered(Request $request){        
        $seller_id = $request->seller_id;
        $country = $request->country;

        $claimType = $request->claimtype;
        $supportTicket = $request->support_ticket;
        $status = $request->status;
        $orderId = $request->orderid;

       $oic = DB::connection('mysql2')->table('order_id_claims')
            ->where(function($query) use ($seller_id,$country,$status,$supportTicket,$orderId){
            $query->where('seller_id',$seller_id);
            $query->where('country_code',$country);

            if(!empty($claimType))
            {
                $query->where('claim_type',$claimType);
            }

            if(!empty($status) && $status == 'Open')
            {
                $query->where(function ($query) {
                $query->whereNull('status');
                $query->OrWhere('status','Open');
                });
            }

            else if(!empty($status) && $status != 'Open')
            {
                $query->where('status', $status);
            }

            if(!empty($supportTicket))
            {
                $query->where('support_ticket',$supportTicket);
            }

            if(!empty($orderId))
            {
                $query->where('order_id',$orderId);
            }

           })
        ->get();


        $response = array();

        foreach ($oic as $val) {
            if ($val->total_refunded < 0) {
            if ($val->claim_amount > 0){

                $open = ($val->status == 'Open') ? 'selected' : '';
                $ok = ($val->status == 'All Ok') ? 'selected' : '';
                $refund = ($val->status == 'Refund issued by seller') ? 'selected' : '';
                $amz = ($val->status == 'Amz won'."'".'t refund difference') ? 'selected' : '';

                $status =   '<div>'
                        .'<select class="form-control" onchange="oicupdateStatus(this)" id="'.$val->order_id.'" style="font-size: 12px;padding: 0px 0px">'
                          .'<option '.$open.'>Open</option>'
                          .'<optgroup label="Closed">'
                          .'<option '.$ok.'>All Ok</option>'
                          .'<option '.$refund.'>Refund issued by seller</option>'
                          .'<option '.$amz.'>Amz won'."'".'t refund difference</option>'
                          .'</optgroup>'
                        .'</select>'
                    .'</div>';

                $array = ['All Ok','Refund issued by seller','Amz won'."'".'t refund difference'];

                $hidden = '<input type="hidden" id="clipboard-'.$val->order_id.'" value="'.$val->claim_type.'" />';
                $hidden0 = '<input type="hidden" id="clipboard0-'.$val->order_id.'" value="'.$val->status.'" />';
                $hidden1 = '<input type="hidden" id="clipboard1-'.$val->order_id.'" value="Order ID: '.$val->order_id.'" />';
                $hidden2 = '<input type="hidden" id="clipboard2-'.$val->order_id.'" value="Amount due: '.$val->claim_amount.'" />';
                $hidden3 = '<input type="hidden" id="clipboard3-'.$val->order_id.'" value="'.$val->detailed_disposition.'" />';
                $hidden4 = '<input type="hidden" id="clipboard4-'.$val->order_id.'" value="Reimbursement reason: '.$val->detailed_disposition.'" />';
                $hidden5 = '<input type="hidden" id="clipboard5-'.$val->order_id.'" value="Reimbursement amount: '.$val->total_adjusted.'" />';
                $hidden6 = '<input type="hidden" id="clipboard6-'.$val->order_id.'" value="'.$val->claim_amount.'" />';
                $hidden7 = '<input type="hidden" id="clipboard7-'.$val->order_id.'" value="Difference: '.round($val->difference,2).'" />';
                $hidden8 = '<input type="hidden" id="clipboard8-'.$val->order_id.'" value="'.$val->return_reason.'" />';
                $hidden9 = '<input type="hidden" id="clipboard9-'.$val->order_id.'" value="'.$val->quantity_unsellable.'" />';
                $hidden10 = '<input type="hidden" id="clipboard10-'.$val->order_id.'" value="'.$country.'" />';
                $hidden11 = '<input type="hidden" id="clipboard11-'.$val->order_id.'" value="'.$val->claim_amount.'" />';
                $calculation1 = ($val->total_refunded*(-1));
                $calculation = (string)$calculation1.'-'.(string)$val->total_ordered;
                $hidden12 = '<input type="hidden" id="clipboard12-'.$val->order_id.'" value="'.$calculation.'" />';
                $hidden13 = '<input type="hidden" id="clipboard13-'.$val->order_id.'" value="'.$val->claim_name.'" />';          
                $hidden14 = '<input type="hidden" id="clipboard14-'.$val->order_id.'" value="'.$val->quantity_unsellable.'" />';            
                $hidden15 = '<input type="hidden" id="clipboard15-'.$val->order_id.'" value="'.$val->fmv.'" />';

                $clip = '<button class="btn btn-primary btn-sm no-radius" id="clip-'.$val->order_id.'" onclick="oicClip(this)"> <i class="fa fa-clipboard"></i></button>';

                        $data = array();
                        $data['DT_RowId'] = $val->id;
                        $data[] = $clip.' '.$val->order_id.$hidden.$hidden0.$hidden1.$hidden2.$hidden3.$hidden4.$hidden5.$hidden6.$hidden7.$hidden8.$hidden9.$hidden10.$hidden11.$hidden12.$hidden13.$hidden14.$hidden15;
                        $data[] = $val->quantity_ordered;
                        $data[] = $val->quantity_refunded;
                        $data[] = $val->quantity_adjusted;
                        $data[] = $val->total_ordered;
                        $data[] = $val->total_refunded;
                        $data[] = $val->total_adjusted;
                        $data[] = $val->quantity_returned;
                        $data[] = $val->date_of_return;
                        $data[] = $val->over_45days; //$over_45days->over_45days;
                        $data[] = $val->claim_type;
                        $data[] = $val->claim_name;
                        $data[] = $val->detailed_disposition; //(is_null($val->oid)) ? "Item Not Returned" : $val->detailed_disposition;
                        $ave_sales3 = ($val->fmv3_quantity == 0) ? 0 : $val->fmv3_sales/$val->fmv3_quantity;
                        $data[] = '<span title="Total number of sales: '.$val->fmv3_quantity.' Average selling price: '.round($ave_sales3,2).'">'.$val->fmv3.'</span>';                        
                        $ave_sales = ($val->fmv_quantity == 0) ? 0 : $val->fmv_sales/$val->fmv_quantity;
                        $data[] = '<span title="Total number of sales: '.$val->fmv_quantity.' Average selling price: '.round($ave_sales,2).'">'.$val->fmv.'</span>';
                        $data[] = $val->claim_amount;
                        $data[] = $this->helper->editableColumnCell((string)$val->support_ticket,'Click to edit','supportTicketCell');
                        $data[] = $this->helper->editableColumnCell((string)$val->support_ticket2,'Click to edit','supportTicketCell2');
                        $data[] = $val->reimbursement_id1;
                        $data[] = $val->reimbursement_id2;
                        $data[] = $val->reimbursement_id3;
                        $data[] = $val->total_amount_reimbursed;
                        (in_array($val->status,$array) == true) ? $data[] = 0 : $data[] = round($val->difference,2);
                        $data[] = $status;
                        $data[] = $this->helper->editableColumnCell((string)$val->comments,'Click to edit','commentCell');
                        $response[] = $data;

           }//end if
           }//end if
       }//end forloop
      echo json_encode($response);
    }//end method

    public function updateStatusFNSKU(Request $request){

      $fnsku = $request->id;
      $fc = FnskuClaim::where('fnsku', '=', $request->id)
                        ->first();
      $fc->status = $request->value;
      $fc->save();

      $seller_id = $fc->seller_id;

      $s = Seller::where('id', $seller_id)
                        ->first();

      $email = $s->email;
      $country = $fc->country_code;
      $this->getTotalSaved($email,$seller_id,$country);
    }

    public function getTotalSaved($email,$seller_id,$country)
    {
        $total_saved = 0;
        $oic = OrderIdClaim::where('seller_id', '=', $seller_id)
                                   ->where('country_code', '=', $country)
                                   ->whereIn('status', ['All Ok', 'Refund issued by seller', 'Amz won\'t refund difference'])
                                   ->get();

        foreach($oic as $oc)
        {
            $total_saved += $oc->total_amount_reimbursed;
        }

        $fnsku = FnskuClaim::where('seller_id', '=', $seller_id)
                                   ->where('country_code', '=', $country)
                                   ->whereIn('status', ['All Ok', 'Refund issued by seller', 'Amz won\'t refund difference'])
                                   ->get();

        foreach($fnsku as $fs)
        {
            $total_saved += $fs->total_amount_reimbursed;
        }

        $curr = '';
        if($country == 'usd')
        {
            $curr = 'USD';
        }
        else if($country == 'ca')
        {
            $curr = 'CAD';
        }
        else if($country == 'uk')
        {
            $curr = 'GBP';
        }
        else if($country == 'de' || $country == 'it' || $country == 'es' || $country == 'fr')
        {
            $curr = 'EUR';
        }

        $ad = AdminSeller::where('seller_email', $email)
                        ->where('country_code', $country)
                         ->first();
        $currency = $ad->currency;
        $total_saved = currency($total_saved, $curr, strtoupper($currency), false);
        $ad->total_saved = $total_saved;
        $ad->save();
        return $total_saved;
    }

    public function getSellerFNSKU(Request $request){                
        $seller_id = $request->seller_id;
        $country = $request->country;

        $fc = FnskuClaim::where('seller_id', '=', $seller_id)
                        ->where('country_code', '=', $country)
                        ->get();

        $response = array();

        foreach ($fc as $val) {

            $open = ($val->status == 'Open') ? 'selected' : '';
            $ok = ($val->status == 'All Ok') ? 'selected' : '';
            $refund = ($val->status == 'Refund issued by seller') ? 'selected' : '';
            $amz = ($val->status == 'Amz won'."'".'t refund difference') ? 'selected' : '';

                $status =   '<div>'
                    .'<select class="form-control" onchange="fnskuUpdateStatus(this)" id="'.$val->fnsku.'" style="font-size: 12px;padding: 0px 0px">'
                      .'<option '.$open.'>Open</option>'
                      .'<optgroup label="Closed">'
                      .'<option '.$ok.'>All Ok</option>'
                      .'<option '.$refund.'>Refund issued by seller</option>'
                      .'<option '.$amz.'>Amz won'."'".'t refund difference</option>'
                      .'</optgroup>'
                    .'</select>'
                .'</div>';

            $array = ['All Ok','Refund issued by seller','Amz won'."'".'t refund difference'];

            $hidden = '<input type="hidden" id="clipboard-'.$val->fnsku.'" value="'.$val->is_third_scenario.'" />';
            $hidden0 = '<input type="hidden" id="clipboard0-'.$val->fnsku.'" value="'.$val->status.'" />';
            $hidden1 = '<input type="hidden" id="clipboard1-'.$val->fnsku.'" value="FnSKU: '.$val->fnsku.'" />';
            $hidden2 = '<input type="hidden" id="clipboard2-'.$val->fnsku.'" value="Value per item: '.$val->average_value.'" />';
            $hidden3 = '<input type="hidden" id="clipboard3-'.$val->fnsku.'" value="Number of items Lost: '.$val->items_lost.'" />';
            $hidden4 = '<input type="hidden" id="clipboard4-'.$val->fnsku.'" value="Number of items Damaged: '.$val->items_damaged.'" />';
            $hidden5 = '<input type="hidden" id="clipboard5-'.$val->fnsku.'" value="Total amount owed: '.$val->total_owed.'" />';

            $clip = '<button class="btn btn-primary btn-sm no-radius" id="clip-'.$val->fnsku.'" data-clip-id="'.$val->fnsku.'" onclick="skuClip(this)"> <i class="fa fa-clipboard"></i></button>';
            // if ($val->summation < 0){
                $data = array();
                $data['DT_RowId'] = $val->id;
                $data[] = $clip.''.$hidden.$hidden0.$hidden1.$hidden2.$hidden3.$hidden4.$hidden5.$val->fnsku;
                $data[] = $val->three;
                $data[] = $val->four;
                $data[] = $val->five;
                $data[] = $val->d;
                $data[] = $val->e;
                $data[] = $val->f;
                $data[] = $val->m;
                $data[] = $val->n;
                $data[] = $val->o;
                $data[] = $val->p;
                $data[] = $val->q;
                $data[] = $val->summation;
                $data[] = $val->reimbursed_units;
                $data[] = $val->summation + $val->reimbursed_units;
                $data[] = $val->fnsku;
                $data[] = $val->units; //$val->summation*(-1);
                $data[] = $val->average_value; //($val->order_nb != 0) ? round(round($val->revenue, 2)/$val->order_nb,2) : "0.00";
                $ave_sales3 = ($val->fmv3_quantity == 0) ? 0 : $val->fmv3_sales/$val->fmv3_quantity;
                $data[] = '<span title="Total number of sales: '.$val->fmv3_quantity.' Average selling price: '.round($ave_sales3,2).'">'.$val->fmv3.'</span>';                        
                $ave_sales = ($val->fmv_quantity == 0) ? 0 : $val->fmv_sales/$val->fmv_quantity;
                $data[] = '<span title="Total number of sales: '.$val->fmv_quantity.' Average selling price: '.round($ave_sales,2).'">'.$val->fmv.'</span>';
                $data[] = $val->total_owed; //($val->order_nb != 0) ? round((round($val->revenue, 2)/$val->order_nb)*($val->summation*(-1)),2) : "0.00";
                $data[] = $this->helper->editableColumnCell((string)$val->support_ticket,'Click to edit','supportTicketCell');
                $data[] = $this->helper->editableColumnCell((string)$val->support_ticket2,'Click to edit','supportTicketCell2');
                $data[] = $val->reimbursement_id1;
                $data[] = $val->reimbursement_id2;
                $data[] = $val->reimbursement_id3;
                $data[] = $val->total_amount_reimbursed;
                (in_array($val->status,$array) == true) ? $data[] = 0 : $data[] = round($val->difference,2);
                $data[] = $status;
                $data[] = $this->helper->editableColumnCell((string)$val->comments,'Click to edit','commentCell');
                $response[] = $data;
            // }
        }
        echo json_encode($response);
    }

    public function getSellerFNSKUFiltered(Request $request){        
        $seller_id = $request->seller_id;
        $country = $request->country;

        $supportTicket = $request->support_ticket;
        $status = $request->status;
        $fnsku = $request->fnsku;

        $fc = DB::connection('mysql2')->table('fnsku_claims')
            ->where(function($query) use ($seller_id,$country,$status,$supportTicket,$fnsku){
            $query->where('seller_id',$seller_id);
            $query->where('country_code',$country);

            if(!empty($status) && $status == 'Open')
            {
                $query->where(function ($query) {
                $query->whereNull('status');
                $query->OrWhere('status','Open');
                });
            }

            else if(!empty($status) && $status != 'Open')
            {
                $query->where('status', $status);
            }

            if(!empty($supportTicket))
            {
                $query->where('support_ticket',$supportTicket);
            }

            if(!empty($fnsku))
            {
                $query->where('fnsku',$fnsku);
            }

           })
        ->get();

        $response = array();

        foreach ($fc as $val) {

            $open = ($val->status == 'Open') ? 'selected' : '';
            $ok = ($val->status == 'All Ok') ? 'selected' : '';
            $refund = ($val->status == 'Refund issued by seller') ? 'selected' : '';
            $amz = ($val->status == 'Amz won'."'".'t refund difference') ? 'selected' : '';

                $status =   '<div>'
                    .'<select class="form-control" onchange="fnskuUpdateStatus(this)" id="'.$val->fnsku.'" style="font-size: 12px;padding: 0px 0px">'
                      .'<option '.$open.'>Open</option>'
                      .'<optgroup label="Closed">'
                      .'<option '.$ok.'>All Ok</option>'
                      .'<option '.$refund.'>Refund issued by seller</option>'
                      .'<option '.$amz.'>Amz won'."'".'t refund difference</option>'
                      .'</optgroup>'
                    .'</select>'
                .'</div>';

            $array = ['All Ok','Refund issued by seller','Amz won'."'".'t refund difference'];

            $hidden = '<input type="hidden" id="clipboard-'.$val->fnsku.'" value="'.$val->is_third_scenario.'" />';
            $hidden0 = '<input type="hidden" id="clipboard0-'.$val->fnsku.'" value="'.$val->status.'" />';
            $hidden1 = '<input type="hidden" id="clipboard1-'.$val->fnsku.'" value="FnSKU: '.$val->fnsku.'" />';
            $hidden2 = '<input type="hidden" id="clipboard2-'.$val->fnsku.'" value="Value per item: '.$val->average_value.'" />';
            $hidden3 = '<input type="hidden" id="clipboard3-'.$val->fnsku.'" value="Number of items Lost: '.$val->items_lost.'" />';
            $hidden4 = '<input type="hidden" id="clipboard4-'.$val->fnsku.'" value="Number of items Damaged: '.$val->items_damaged.'" />';
            $hidden5 = '<input type="hidden" id="clipboard5-'.$val->fnsku.'" value="Total amount owed: '.$val->total_owed.'" />';

            $clip = '<button class="btn btn-primary btn-sm no-radius" id="clip-'.$val->fnsku.'" data-clip-id="'.$val->fnsku.'" onclick="skuClip(this)"> <i class="fa fa-clipboard"></i></button>';
            // if ($val->summation < 0){
                $data = array();
                $data['DT_RowId'] = $val->id;
                $data[] = $clip.''.$hidden.$hidden0.$hidden1.$hidden2.$hidden3.$hidden4.$hidden5.$val->fnsku;
                $data[] = $val->three;
                $data[] = $val->four;
                $data[] = $val->five;
                $data[] = $val->d;
                $data[] = $val->e;
                $data[] = $val->f;
                $data[] = $val->m;
                $data[] = $val->n;
                $data[] = $val->o;
                $data[] = $val->p;
                $data[] = $val->q;
                $data[] = $val->summation;
                $data[] = $val->reimbursed_units;
                $data[] = $val->summation + $val->reimbursed_units;
                $data[] = $val->fnsku;
                $data[] = $val->units; //$val->summation*(-1);
                $data[] = $val->average_value; //($val->order_nb != 0) ? round(round($val->revenue, 2)/$val->order_nb,2) : "0.00";
                $ave_sales3 = ($val->fmv3_quantity == 0) ? 0 : $val->fmv3_sales/$val->fmv3_quantity;
                $data[] = '<span title="Total number of sales: '.$val->fmv3_quantity.' Average selling price: '.round($ave_sales3,2).'">'.$val->fmv3.'</span>';                        
                $ave_sales = ($val->fmv_quantity == 0) ? 0 : $val->fmv_sales/$val->fmv_quantity;
                $data[] = '<span title="Total number of sales: '.$val->fmv_quantity.' Average selling price: '.round($ave_sales,2).'">'.$val->fmv.'</span>';
                $data[] = $val->total_owed; //($val->order_nb != 0) ? round((round($val->revenue, 2)/$val->order_nb)*($val->summation*(-1)),2) : "0.00";
                $data[] = $this->helper->editableColumnCell((string)$val->support_ticket,'Click to edit','supportTicketCell');
                $data[] = $this->helper->editableColumnCell((string)$val->support_ticket2,'Click to edit','supportTicketCell2');
                $data[] = $val->reimbursement_id1;
                $data[] = $val->reimbursement_id2;
                $data[] = $val->reimbursement_id3;
                $data[] = $val->total_amount_reimbursed;
                (in_array($val->status,$array) == true) ? $data[] = 0 : $data[] = round($val->difference,2);
                $data[] = $status;
                $data[] = $this->helper->editableColumnCell((string)$val->comments,'Click to edit','commentCell');
                $response[] = $data;
            // }
        }
        echo json_encode($response);
    }

}
