<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialEventsReport extends Model
{
  public function get_oic($seller_id = null, $country = null, $today=null, $dt=null) {
    if ($country == 'us') $country = 'com';
    if ($country != null && $today == 'YES') {
      $date_from = Carbon::today()->subMonths(18)->toDateString();
      $date_to = Carbon::today()->subDays(46)->toDateString();

      return DB::connection('mysql2')
        ->table('financial_events_reports')
        ->where('financial_events_reports.seller_id', '=', $seller_id)
        ->where('financial_events_reports.marketplace_name', 'like', '%'.strtolower($country))
        ->where('financial_events_reports.type', '=', 'Refund')
        ->where('financial_events_reports.price_type', '=', 'Principal')
        ->whereRaw("DATE(financial_events_reports.posted_date) >= '".$date_from."'")
        ->whereRaw("DATE(financial_events_reports.posted_date) <= '".$date_to."'")
        ->groupBy('financial_events_reports.order_id')
        ->get([
          'financial_events_reports.order_id',
          DB::raw('SUM(financial_events_reports.quantity) AS quantity_refunded'),
          DB::raw('SUM(financial_events_reports.price_amount
                    +financial_events_reports.promotional_rebates
                    +financial_events_reports.other_amount
                    +financial_events_reports.shipment_fee_amount
                    +financial_events_reports.order_fee_amount
                    +financial_events_reports.direct_payment_amount
                    +financial_events_reports.item_related_fee_amount
                    +financial_events_reports.tax_amount) AS total_refunded')
        ]);
    } else if($country != null && $today == 'NO') {
      return DB::connection('mysql2')
        ->table('financial_events_reports')
        ->where('financial_events_reports.seller_id', '=', $seller_id)
        ->where('financial_events_reports.marketplace_name', 'like', '%'.strtolower($country))
        ->where(DB::raw("financial_events_reports.posted_date + INTERVAL 45 DAY"), '>=', $dt)
        ->where('financial_events_reports.type', '=', 'Refund')
        ->where('financial_events_reports.price_type', '=', 'Principal')
        ->groupBy('financial_events_reports.order_id')
        ->get([
          'financial_events_reports.order_id',
          DB::raw('SUM(financial_events_reports.quantity) AS quantity_refunded'),
          DB::raw('SUM(financial_events_reports.price_amount
                    +financial_events_reports.promotional_rebates
                    +financial_events_reports.other_amount
                    +financial_events_reports.shipment_fee_amount
                    +financial_events_reports.order_fee_amount
                    +financial_events_reports.direct_payment_amount
                    +financial_events_reports.item_related_fee_amount
                    +financial_events_reports.tax_amount) AS total_refunded')
        ]);
    }
  }

  public function over_45days($order_id) {
    return DB::connection('mysql2')
              ->table('financial_events_reports')
              ->where('financial_events_reports.type', '=', 'Refund')
              ->where('financial_events_reports.order_id', '=', $order_id)
              ->select(DB::raw("IF(financial_events_reports.posted_date + INTERVAL 44 DAY <= CURDATE(),'YES','NO') AS over_45days"))
              ->first();
  }

  public function getAsinRefund($order_id) {
    $array = array();
    $query = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->where('financial_events_reports.type', '=', 'Refund')
              ->where('financial_events_reports.order_id', '=', $order_id)
              ->get(['financial_events_reports.asin',
                     'financial_events_reports.quantity']);

    foreach ($query as $val) {
      $array[$val->asin] = $val->quantity;
    }
    return $array;
  }

  public function getNbOrder($asin, $country) {
    $data = (object) null;
    if ($country == 'us') {
      $r_exp = '.com';
    } elseif ($country == 'ca') {
      $r_exp = '.ca';
    } elseif ($country == 'uk') {
      $r_exp = '.uk';
    } elseif ($country == 'it') {
      $r_exp = '.it';
    } elseif ($country == 'fr') {
      $r_exp = '.fr';
    } elseif ($country == 'de') {
      $r_exp = '.de';
    } elseif ($country == 'es') {
      $r_exp = '.es';
    }
    $query1 = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->whereRaw("financial_events_reports.type = 'Order'")
              ->whereRaw("financial_events_reports.asin = '".$asin."'")
              ->whereRaw("financial_events_reports.quantity = 1")
              ->whereRaw("DATE(financial_events_reports.posted_date + INTERVAL 89 DAY) >= CURDATE()")
              ->whereRaw("financial_events_reports.marketplace_name REGEXP '".$r_exp."'")
              ->get([
                'financial_events_reports.quantity',
                DB::raw("(financial_events_reports.price_amount
                          +financial_events_reports.promotional_rebates
                          +financial_events_reports.other_amount
                          +financial_events_reports.shipment_fee_amount
                          +financial_events_reports.order_fee_amount
                          +financial_events_reports.direct_payment_amount
                          +financial_events_reports.item_related_fee_amount
                          +financial_events_reports.tax_amount) AS revenue")
              ]);
    $quantity = 0;
    $revenue = 0;
    foreach ($query1 as $val1) {
      if ($quantity < 2000) {
        $quantity += $val1->quantity;
        $revenue += $val1->revenue;
      }
    }

    if ($quantity >= 2000) {
      $quantity = 2000;
    } else {
      $query2 = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->whereRaw("financial_events_reports.type = 'Order'")
              ->whereRaw("financial_events_reports.asin = '".$asin."'")
              ->whereRaw("financial_events_reports.quantity = 1")
              ->whereRaw("financial_events_reports.marketplace_name REGEXP '".$r_exp."'")
              ->get([
                'financial_events_reports.quantity',
                DB::raw("(financial_events_reports.price_amount
                          +financial_events_reports.promotional_rebates
                          +financial_events_reports.other_amount
                          +financial_events_reports.shipment_fee_amount
                          +financial_events_reports.order_fee_amount
                          +financial_events_reports.direct_payment_amount
                          +financial_events_reports.item_related_fee_amount
                          +financial_events_reports.tax_amount) AS revenue")
              ]);
      $quantity = 0;
      $revenue = 0;
      foreach ($query2 as $val2) {
        if ($quantity < 2000) {
          $quantity += $val2->quantity;
          $revenue += $val2->revenue;
        }
      }
    }
    $data->quantity = $quantity;
    $data->revenue = $revenue;
    if ($quantity == 0) {
      $data->average = 0;
    } else {
      $data->average = $revenue / $quantity;
    }
    return $data;
  }

  public function getFMV($asin, $country) {
    $data = (object) null;
    if ($country == 'us') {
      $r_exp = '.com';
    } elseif ($country == 'ca') {
      $r_exp = '.ca';
    } elseif ($country == 'uk') {
      $r_exp = '.uk';
    } elseif ($country == 'it') {
      $r_exp = '.it';
    } elseif ($country == 'fr') {
      $r_exp = '.fr';
    } elseif ($country == 'de') {
      $r_exp = '.de';
    } elseif ($country == 'es') {
      $r_exp = '.es';
    }

    $query = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->whereRaw("financial_events_reports.type = 'Order'")
              ->whereRaw("financial_events_reports.asin = '".$asin."'")
              ->whereRaw("financial_events_reports.quantity = 1")
              ->whereRaw("financial_events_reports.price_type = 'Principal'")
              ->whereRaw("financial_events_reports.marketplace_name REGEXP '".$r_exp."'")
              ->get([
                  DB::raw("(financial_events_reports.price_amount
                          +financial_events_reports.promotional_rebates
                          +financial_events_reports.other_amount
                          +financial_events_reports.shipment_fee_amount
                          +financial_events_reports.order_fee_amount
                          +financial_events_reports.direct_payment_amount
                          +financial_events_reports.item_related_fee_amount
                          +financial_events_reports.tax_amount) AS price_amount"),
                  'financial_events_reports.item_related_fee_amount',
                  'financial_events_reports.quantity',
              ]);
    $sales = 0;
    $cost = 0;
    $quantity = 0;
    foreach ($query as $val) {
      if ($quantity < 2000) {
        $sales += $val->price_amount;
        $cost += $val->item_related_fee_amount;
        $quantity += $val->quantity;
      }
    }
    $data->sales = $sales;
    $data->cost = $cost;
    $data->quantity = $quantity;
    return $data;

  }

  public function getFMV3months($asin, $country) {
    $data = (object) null;
    if ($country == 'us') {
      $r_exp = '.com';
    } elseif ($country == 'ca') {
      $r_exp = '.ca';
    } elseif ($country == 'uk') {
      $r_exp = '.uk';
    } elseif ($country == 'it') {
      $r_exp = '.it';
    } elseif ($country == 'fr') {
      $r_exp = '.fr';
    } elseif ($country == 'de') {
      $r_exp = '.de';
    } elseif ($country == 'es') {
      $r_exp = '.es';
    }

    $query = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->whereRaw("financial_events_reports.type = 'Order'")
              ->whereRaw("financial_events_reports.asin = '".$asin."'")
              ->whereRaw("financial_events_reports.quantity = 1")
              ->whereRaw("financial_events_reports.price_type = 'Principal'")
              ->whereRaw("DATE(financial_events_reports.posted_date + INTERVAL 89 DAY) >= CURDATE()")
              ->whereRaw("financial_events_reports.marketplace_name REGEXP '".$r_exp."'")
              ->get([
                  DB::raw("(financial_events_reports.price_amount
                          +financial_events_reports.promotional_rebates
                          +financial_events_reports.other_amount
                          +financial_events_reports.shipment_fee_amount
                          +financial_events_reports.order_fee_amount
                          +financial_events_reports.direct_payment_amount
                          +financial_events_reports.item_related_fee_amount
                          +financial_events_reports.tax_amount) AS price_amount"),
                  'financial_events_reports.item_related_fee_amount',
                  'financial_events_reports.quantity',
              ]);
    $sales = 0;
    $cost = 0;
    $quantity = 0;
    foreach ($query as $val) {
      $sales += $val->price_amount;
      $cost += $val->item_related_fee_amount;
      $quantity += $val->quantity;
    }
    $data->sales = $sales;
    $data->cost = $cost;
    $data->quantity = $quantity;
    return $data;
    
  }

  public function getOrdered($order_id) {
    $data = (object) null;
    $query = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->where('financial_events_reports.order_id', '=', $order_id)
              ->where('financial_events_reports.type', '=', 'Order')
              ->get([
                  DB::raw("(financial_events_reports.quantity) AS quantity_ordered"),
                  DB::raw("(financial_events_reports.price_amount
                              +financial_events_reports.promotional_rebates
                              +financial_events_reports.other_amount
                              +financial_events_reports.shipment_fee_amount
                              +financial_events_reports.order_fee_amount
                              +financial_events_reports.direct_payment_amount
                              +financial_events_reports.item_related_fee_amount
                              +financial_events_reports.tax_amount) AS total_ordered")
                ]);
    $quantity = 0;
    $amount = 0;
    foreach ($query as $val) {
      $quantity += $val->quantity_ordered;
      $amount += $val->total_ordered;
    }
    $data->quantity_ordered = $quantity;
    $data->total_ordered = $amount;

    return $data;
  }

  public function getRefunded($order_id) {
    $data = (object) null;
    $query = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->where('financial_events_reports.order_id', '=', $order_id)
              ->where('financial_events_reports.type', '=', 'Refund')
              ->where('financial_events_reports.price_type', '=', 'Principal')
              ->get([
                  DB::raw("(financial_events_reports.quantity) AS quantity_refunded"),
                  DB::raw("(financial_events_reports.price_amount
                              +financial_events_reports.promotional_rebates
                              +financial_events_reports.other_amount
                              +financial_events_reports.shipment_fee_amount
                              +financial_events_reports.order_fee_amount
                              +financial_events_reports.direct_payment_amount
                              +financial_events_reports.item_related_fee_amount
                              +financial_events_reports.tax_amount) AS total_refunded")
                ]);
    $quantity = 0;
    $amount = 0;
    foreach ($query as $val) {
      $quantity += $val->quantity_refunded;
      $amount += $val->total_refunded;
    }
    $data->quantity_refunded = $quantity;
    $data->total_refunded = $amount;

    return $data;
  }

  public function getAdjusted($order_id) {
    $data = (object) null;
    $query = DB::connection('mysql2')
              ->table('reimbursements')
              ->where('reimbursements.amazon_order_id', '=', $order_id)
              ->get([
                  'reimbursements.quantity_reimbursed_total',
                  'reimbursements.amount_total',
                  'reimbursements.amount_per_unit',
                  'reimbursements.quantity_reimbursed_cash',
                  'reimbursements.quantity_reimbursed_inventory'
                ]);
    $quantity = 0;
    $amount = 0;
    $cash = 0;
    $inventory = 0;
    foreach ($query as $val) {
      if ($val->amount_total == 0) {
        $amount += ($val->amount_per_unit*$val->quantity_reimbursed_total);
      } else {
        $amount += $val->amount_total;
      }
      $quantity += $val->quantity_reimbursed_total;
      $cash += $val->quantity_reimbursed_cash;
      $inventory += $val->quantity_reimbursed_inventory;
    }
    if ($amount == 0) {
      $claim = 'Full';
    } else {
      $claim = 'Partial';
    }
    $fmv_good = true;
    if ($cash == 0 && $inventory > 0) {
      $fmv_good = false;
    }
    $data->fmv_good = $fmv_good;
    $data->claim_type = $claim;
    $data->quantity_adjusted = $quantity;
    $data->total_adjusted = $amount;

    return $data;
  }

  public function getReturns($order_id) {

    $array = array();
    $query = DB::connection('mysql2')
              ->table('returns_reports')
              ->where('returns_reports.order_id', '=', $order_id)
              ->get([
                  'returns_reports.quantity AS quantity_returned',
                  'returns_reports.return_date AS date_of_return',
                  'returns_reports.order_id AS oid',
                  'returns_reports.detailed_disposition',
                  'returns_reports.reason'
                ]);

    foreach ($query as $val) {
      $data = array();

      $data['quantity'] = $val->quantity_returned;
      $data['date'] = $val->date_of_return;
      $data['oid'] = $val->oid;
      $data['dd'] = $val->detailed_disposition;
      $data['rr'] = $val->reason;
      if ($val->detailed_disposition == 'DAMAGED' || $val->detailed_disposition == 'CARRIER_DAMAGED') {
        $data['quantity_dd'] = $val->quantity_returned;
      } else {
        $data['quantity_dd'] = 0;
      }

      if ($val->reason == "DAMAGED_BY_FC" || $val->reason == "MISSED_ESTIMATED_DELIVERY" || $val->reason == "DAMAGED_BY_CARRIER" || $val->reason == "EXTRA_ITEM") {
        $data['quantity_rr'] = $val->quantity_returned;
        if ($val->detailed_disposition != 'SELLABLE') {
          $data['quantity_unsellable'] = $val->quantity_returned;
        } else {
          $data['quantity_unsellable'] = 0;
        }
      } else {
        $data['quantity_rr'] = 0;
        $data['quantity_unsellable'] = 0;
      }
      $array[] = $data;
    }

    return $array;
  }

  public function getOic($order_id) {
    $data = (object) null;
    $query = DB::connection('mysql2')
              ->table('order_id_claims')
              ->where('order_id_claims.order_id', '=', $order_id)
              ->get([
                  'order_id_claims.support_ticket',
                  'order_id_claims.support_ticket2',
                  'order_id_claims.status',
                  'order_id_claims.comments'
                ]);
    $st = NULL;
    $st2 = NULL;
    $s = NULL;
    $c = NULL;
    foreach ($query as $val) {
      $st = $val->support_ticket;
      $st2 = $val->support_ticket2;
      $s = $val->status;
      $c = $val->comments;
    }
    $data->support_ticket = $st;
    $data->support_ticket2 = $st2;
    $data->status = $s;
    $data->comments = $c;

    return $data;
  }

  public function getSalesChannel($order_id) {
    $data = (object) null;
    $query = DB::connection('mysql2')
              ->table('flat_file_all_orders_by_dates')
              ->select('sales_channel')
              ->where('flat_file_all_orders_by_dates.amazon_order_id', '=', $order_id)
              ->get();
    $sc = NULL;
    foreach ($query as $val) {
      $sc = $val->sales_channel;
    }
    $data->sales_channel = $sc;

    return $data;
  }

  public function getLargerRefundThanPrice($order_id) {
    $data = (object) null;
    $query = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->where('financial_events_reports.order_id', '=', $order_id)
              ->where('financial_events_reports.type', '=', 'Order')
              ->get([
                  'financial_events_reports.price_amount'
                ]);

    if (!$query->isEmpty()) {
      $order = 0;
      foreach ($query as $val) {
        $order += $val->price_amount;
      }
      $order = round($order,2);

      $query1 = DB::connection('mysql2')
                ->table('financial_events_reports')
                ->where('financial_events_reports.order_id', '=', $order_id)
                ->where('financial_events_reports.type', '=', 'Refund')
                ->get([
                    'financial_events_reports.price_amount'
                  ]);
      $refund = 0;
      foreach ($query1 as $val) {
        $refund += $val->price_amount;
      }

      $refund = $refund*(-1);
      $refund = round($refund,2);
      $tof = false;
      $claim_amount = 0;
      if ($refund > $order) {
        $tof = true;
        $claim_amount = $refund - $order;
      } else {
        $tof = false;
      }
    } else {
      $tof = false;
      $claim_amount = 0;
    }

    $data->is_larger = $tof;
    $data->claim_amount = $claim_amount;

    return $data;
  }

  public function getWrongAddressAmountsSellable($order_id) {
    $data = (object) null;
    $query = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->where('financial_events_reports.order_id', '=', $order_id)
              ->where('financial_events_reports.type', '=', 'Order')
              ->get([
                  DB::raw("(financial_events_reports.price_amount
                              +financial_events_reports.promotional_rebates
                              +financial_events_reports.other_amount
                              +financial_events_reports.shipment_fee_amount
                              +financial_events_reports.order_fee_amount
                              +financial_events_reports.direct_payment_amount
                              +financial_events_reports.item_related_fee_amount
                              +financial_events_reports.tax_amount) AS amount")
                ]);
    $order = 0;
    $pr = 0;
    foreach ($query as $val) {
      $order = $val->amount;
    }

    $query1 = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->where('financial_events_reports.order_id', '=', $order_id)
              ->where('financial_events_reports.type', '=', 'Refund')
              ->get([
                  DB::raw("(financial_events_reports.price_amount
                              +financial_events_reports.promotional_rebates
                              +financial_events_reports.other_amount
                              +financial_events_reports.shipment_fee_amount
                              +financial_events_reports.order_fee_amount
                              +financial_events_reports.direct_payment_amount
                              +financial_events_reports.item_related_fee_amount
                              +financial_events_reports.tax_amount) AS amount")
                ]);
    $refund = 0;
    foreach ($query1 as $val) {
      $refund = $val->amount;
    }
    $refund = $refund*(-1);
    $claim_amount = $refund - $order;

    $data->claim_amount = $claim_amount;

    return $data;
  }

  public function getWrongAddressAmounts($order_id) {
    $data = (object) null;
    $query = DB::connection('mysql2')
              ->table('financial_events_reports')
              ->where('financial_events_reports.order_id', '=', $order_id)
              ->get([
                  'financial_events_reports.type',
                  DB::raw("(financial_events_reports.price_amount
                              +financial_events_reports.promotional_rebates
                              +financial_events_reports.other_amount
                              +financial_events_reports.shipment_fee_amount
                              +financial_events_reports.order_fee_amount
                              +financial_events_reports.direct_payment_amount
                              +financial_events_reports.item_related_fee_amount
                              +financial_events_reports.tax_amount) AS amount")
                ]);
    $order = 0;
    $refund = 0;
    foreach ($query as $val) {
      if ($val->type == 'Order') {
        $order += $val->amount;
      } elseif ($val->type == 'Refund') {
        $refund += $val->amount;
      }
    }

    $claim_amount = $order + $refund;

    $data->claim_amount = $claim_amount*(-1);

    return $data;
  }

	public function isExist($data=array()){
        $db_query = DB::connection('mysql2');
    	$query = $db_query->table('financial_events_reports')->select('*');
        foreach ($data as $key => $value) {
            $query->where( $key, $value );
        }
    	$data = $query->count();
    	if($data>0) return true;
    	else return false;
    }
	public function insertData($data = array()){
        $db_query = DB::connection('mysql2');
        if($data!=null AND count($data)>0){
            $db_query->table('financial_events_reports')->insert($data);
            return true;
        }else{
            return false;
        }
    }
    public function getRecords($fields = array('*'),$cond=array(),$order=array(),$checkifempty=false){
        $q = DB::connection('mysql2')->table('financial_events_reports');
        $q = $q->select($fields);

        if(count($cond)>0){
        end($cond);$last=key($cond);reset($cond);$first = key($cond);
        foreach($cond as $key => $c){
          if ($key === $first){
            reset($cond);
          }
          $thiskey = key($cond);
          if(count($c)>1) $q = $q->where($thiskey,$c[0],$c[1]);
          else $q = $q->where($thiskey,$c);
          next($cond);
        }
        }

        if(count($order)>0){
        $q = $q->orderBy($order[0],$order[1]);
        }

        if($checkifempty){
             $q = $q->limit(1)->get()->count();
        }else{
             $q = $q->get();
        }
        return $q;
    }








    /*----------added by fran------------------*/
    /*-----------------------------------------*/

    public function getFinancialEventsRawsSumByMKP($field = '*',$cond=array(),$groupby='marketplace_name',$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_events_report_raws');
        $q = $q->selectRaw('sum(' . $field . ') as sum, '.$groupby.' as g');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }
        $q = $q->where('posted_date','>=',$date_from);
        $q = $q->where('posted_date','<',$date_to);

        $q = $q->groupBy($groupby);
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }



    public function getFinancialEventServiceFeeFeelistByPostedDateRange($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_service_fee_fee_lists as t1');
        $q = $q->selectRaw('sum(amount) as sum,currencycode as g');
        $q = $q->join('financial_event_service_fees as t2', 't2.id', '=', 't1.financial_event_service_fees_id');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }
        $q = $q->where('t2.posted_date','>=',$date_from);
        $q = $q->where('t2.posted_date','<',$date_to);

        $q = $q->groupBy('t1.currencycode');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }

    public function getFinancialEventRentalTransactionFeeListByPostedDateRange($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_rental_transaction_rental_fee_lists as t1');
        $q = $q->selectRaw('sum(amount) as sum,currencycode as g');
        $q = $q->join('financial_event_rental_transactions as t2', 't2.id', '=', 't1.financial_event_rental_transactions_id');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }
        $q = $q->where('t2.posteddate','>=',$date_from);
        $q = $q->where('t2.posteddate','<',$date_to);

        $q = $q->groupBy('t1.currencycode');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }


    public function getFinancialEventSAFETReimbursementItemListByPostedDateRange($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_s_a_f_e_t_reimbursement_item_lists as t1');
        $q = $q->selectRaw('sum(amount) as sum,currencycode as g');
        $q = $q->join('financial_event_s_a_f_e_t_reimbursements as t2', 't2.id', '=', 't1.financial_event_s_a_f_e_t_reimbursements_id');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }
        $q = $q->where('t2.posteddate','>=',$date_from);
        $q = $q->where('t2.posteddate','<',$date_to);

        $q = $q->groupBy('t1.currencycode');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }

    public function getFinancialEventRentalTransactionChargeListByPostedDateRange($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_rental_transaction_rental_charge_lists as t1');
        $q = $q->selectRaw('sum(amount) as sum,currencycode as g');
        $q = $q->join('financial_event_rental_transactions as t2', 't2.id', '=', 't1.financial_event_rental_transactions_id');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }
        $q = $q->where('t2.posteddate','>=',$date_from);
        $q = $q->where('t2.posteddate','<',$date_to);

        $q = $q->groupBy('t1.currencycode');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }

    public function getFinancialEventRetrochargeByPostedDateRange($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_retrocharges');
        $q = $q->selectRaw('sum(basetax_amount) as sum,marketplacename as g');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }
        $q = $q->where('posteddate','>=',$date_from);
        $q = $q->where('posteddate','<',$date_to);

        $q = $q->groupBy('marketplacename');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }


    public function getFinancialEventDebtRecoveryByPostedDateRange($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_debt_recoveries');
        $q = $q->selectRaw('sum(amount) as sum,currencycode as g');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }
        $q = $q->where('posted_date','>=',$date_from);
        $q = $q->where('posted_date','<',$date_to);

        $q = $q->groupBy('currencycode');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }

    public function getFinancialEventLoanServicingByPostedDateRange($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_loan_servicings');
        $q = $q->selectRaw('sum(amount) as sum,currency as g');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }
        $q = $q->where('posted_date','>=',$date_from);
        $q = $q->where('posted_date','<',$date_to);

        $q = $q->groupBy('currency');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }



    public function getFinancialEventsRawsSumByDateCurr($field = '*',$cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_events_report_raws');
        $q = $q->selectRaw("sum(".$field.") as sum, currency as curr, date_format(posted_date,'%Y-%m-%d') as ddate");

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);
        $seller_id = $cond['seller_id'];unset($cond['seller_id']);

        $q = $q->where('posted_date','>=',$date_from);
        $q = $q->where('posted_date','<',$date_to);
        $q = $q->where('seller_id',$seller_id);
        $q = $q->where(function ($q) use ($cond) {
          if(count($cond)>0){
            foreach($cond as $c1){
              $q = $q->orWhere(function ($q) use ($c1) {
                end($c1);$last=key($c1);reset($c1);$first=key($c1);
                foreach($c1 as $key => $c){
                  if ($key === $first){
                    reset($c1);
                  }
                  $thiskey = key($c1);
                  $q = $q->where($thiskey,$c);
                  next($c1);
                }
              });
            }
          }
        });



        $q = $q->groupBy('currency','ddate');
        $q = $q->orderBy('ddate');

        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }

    public function getFinancialDebtRecoverySumByDateCurr($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_debt_recoveries');
        $q = $q->selectRaw("sum(amount) as sum, currencycode as curr, date_format(posted_date,'%Y-%m-%d') as ddate");

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);
        $seller_id = $cond['seller_id'];unset($cond['seller_id']);

        $q = $q->where('posted_date','>=',$date_from);
        $q = $q->where('posted_date','<',$date_to);
        $q = $q->where('seller_id',$seller_id);

        $q = $q->groupBy('currencycode','ddate');
        $q = $q->orderBy('ddate');

        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }


    public function getFinancialLoanServicingSumByDateCurr($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_loan_servicings');
        $q = $q->selectRaw("sum(amount) as sum, currency as curr, date_format(posted_date,'%Y-%m-%d') as ddate");

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);
        $seller_id = $cond['seller_id'];unset($cond['seller_id']);

        $q = $q->where('posted_date','>=',$date_from);
        $q = $q->where('posted_date','<',$date_to);
        $q = $q->where('seller_id',$seller_id);

        $q = $q->groupBy('currency','ddate');
        $q = $q->orderBy('ddate');

        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }

    public function getFinancialRetroChargeSumByDateCurr($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_retrocharges');
        $q = $q->selectRaw("sum(basetax_amount) as sum, basetax_currencycode as curr, date_format(posteddate,'%Y-%m-%d') as ddate");

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);
        $seller_id = $cond['seller_id'];unset($cond['seller_id']);

        $q = $q->where('posteddate','>=',$date_from);
        $q = $q->where('posteddate','<',$date_to);
        $q = $q->where('seller_id',$seller_id);

        $q = $q->groupBy('basetax_currencycode','ddate');
        $q = $q->orderBy('ddate');

        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }


    public function getFinancialEventServiceFeeFeelistBySumByDateCurr($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_service_fee_fee_lists as t1');
        $q = $q->selectRaw("sum(amount) as sum,currencycode as curr, date_format(posted_date,'%Y-%m-%d') as ddate");
        $q = $q->join('financial_event_service_fees as t2', 't2.id', '=', 't1.financial_event_service_fees_id');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);
        $seller_id = $cond['seller_id'];unset($cond['seller_id']);

        $q = $q->where('t2.posted_date','>=',$date_from);
        $q = $q->where('t2.posted_date','<',$date_to);
        $q = $q->where('seller_id',$seller_id);


        $q = $q->groupBy('currencycode','ddate');
        $q = $q->orderBy('ddate');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }

    public function getFinancialEventRentalTransactionFeeListBySumByDateCurr($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_rental_transaction_rental_fee_lists as t1');
        $q = $q->selectRaw("sum(amount) as sum,currencycode as curr, date_format(posteddate,'%Y-%m-%d') as ddate");
        $q = $q->join('financial_event_rental_transactions as t2', 't2.id', '=', 't1.financial_event_rental_transactions_id');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);
        $seller_id = $cond['seller_id'];unset($cond['seller_id']);

        $q = $q->where('t2.posteddate','>=',$date_from);
        $q = $q->where('t2.posteddate','<',$date_to);
        $q = $q->where('seller_id',$seller_id);


        $q = $q->groupBy('currencycode','ddate');
        $q = $q->orderBy('ddate');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }

    public function getFinancialEventRentalTransactionChargeListBySumByDateCurr($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_rental_transaction_rental_charge_lists as t1');
        $q = $q->selectRaw("sum(amount) as sum,currencycode as curr, date_format(posteddate,'%Y-%m-%d') as ddate");
        $q = $q->join('financial_event_rental_transactions as t2', 't2.id', '=', 't1.financial_event_rental_transactions_id');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);
        $seller_id = $cond['seller_id'];unset($cond['seller_id']);

        $q = $q->where('t2.posteddate','>=',$date_from);
        $q = $q->where('t2.posteddate','<',$date_to);
        $q = $q->where('seller_id',$seller_id);


        $q = $q->groupBy('currencycode','ddate');
        $q = $q->orderBy('ddate');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }



    public function getFinancialEventSAFETReimbursementItemListBySumByDateCurr($cond=array(),$dbconnection='mysql2'){

        $q = DB::connection($dbconnection)->table('financial_event_s_a_f_e_t_reimbursement_item_lists as t1');
        $q = $q->selectRaw("sum(amount) as sum,currencycode as curr, date_format(posteddate,'%Y-%m-%d') as ddate");
        $q = $q->join('financial_event_s_a_f_e_t_reimbursements as t2', 't2.id', '=', 't1.financial_event_s_a_f_e_t_reimbursements_id');

        $date_from = $cond['date_from'];unset($cond['date_from']);
        $date_to = $cond['date_to'];unset($cond['date_to']);
        $seller_id = $cond['seller_id'];unset($cond['seller_id']);

        $q = $q->where('t2.posteddate','>=',$date_from);
        $q = $q->where('t2.posteddate','<',$date_to);
        $q = $q->where('seller_id',$seller_id);

        $q = $q->groupBy('currencycode','ddate');
        $q = $q->orderBy('ddate');
        $q = $q->get();
        $q = collect($q)->map(function($x){ return (array) $x; })->toArray();
        return $q;
    }


    /*-----------------------------------------*/
    /*-----------------------------------------*/


}
