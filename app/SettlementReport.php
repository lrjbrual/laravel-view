<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SettlementReport extends Model
{	

    public function get_oic($seller_id = null, $country = null, $today=null) 
    {
      if($country == null && $today == null){
        return DB::connection('mysql2')
            ->table('settlement_reports')
            ->leftJoin('returns_reports', 'settlement_reports.order_id', '=', 'returns_reports.order_id')
            ->where('settlement_reports.seller_id', '=', $seller_id)
            ->groupBy('settlement_reports.order_id')
            ->get([
              'settlement_reports.id',
              'settlement_reports.order_id',
              'settlement_reports.marketplace_name',
              'returns_reports.quantity AS quantity_returned',
              'returns_reports.return_date AS date_of_return',
              'returns_reports.order_id AS oid',
              'returns_reports.detailed_disposition',
              DB::raw("SUM(IF(settlement_reports.type = 'Order',settlement_reports.quantity,0)) AS quantity_ordered"),
              DB::raw("SUM(IF(settlement_reports.type = 'Refund',settlement_reports.quantity,0)) AS quantity_refunded"),
              DB::raw("SUM(IF(settlement_reports.type = 'Adjustment',settlement_reports.quantity,0)) AS quantity_adjusted"),
              DB::raw("SUM(IF(settlement_reports.type = 'Order', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) AS total_ordered"),
              DB::raw("SUM(IF(settlement_reports.type = 'Refund', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) AS total_refunded"),
              DB::raw("SUM(IF(settlement_reports.type = 'Adjustment', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) AS total_adjusted"),
              DB::raw("IF(returns_reports.quantity > 0,IF(returns_reports.return_date + INTERVAL 45 DAY <= NOW(),'YES','NO'),NULL) AS over_45days"),
              DB::raw("IF(returns_reports.quantity > 0,IF(SUM(IF(settlement_reports.type = 'Adjustment',settlement_reports.price_amount,0)) = 0,'Full','Partial'),NULL) AS claim_type"),
              DB::raw("SUM(IF(settlement_reports.type = 'Refund', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) + SUM(IF(settlement_reports.type = 'Adjustment', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) AS claim_amount")
            ]);
        }elseif($country != null && $today != null){
          return DB::connection('mysql2')
            ->table('settlement_reports')
            ->leftJoin('returns_reports', 'settlement_reports.order_id', '=', 'returns_reports.order_id')
            ->where('settlement_reports.seller_id', '=', $seller_id)
            ->where('settlement_reports.marketplace_name', 'like', '%'.strtolower($country))
            ->whereDate('settlement_reports.created_at', date('Y-m-d'))
            ->groupBy('settlement_reports.order_id')
            ->get([
              'settlement_reports.id',
              'settlement_reports.order_id',
              'settlement_reports.marketplace_name',
              'returns_reports.quantity AS quantity_returned',
              'returns_reports.return_date AS date_of_return',
              'returns_reports.order_id AS oid',
              'returns_reports.detailed_disposition',
              DB::raw("SUM(IF(settlement_reports.type = 'Order',settlement_reports.quantity,0)) AS quantity_ordered"),
              DB::raw("SUM(IF(settlement_reports.type = 'Refund',settlement_reports.quantity,0)) AS quantity_refunded"),
              DB::raw("SUM(IF(settlement_reports.type = 'Adjustment',settlement_reports.quantity,0)) AS quantity_adjusted"),
              DB::raw("SUM(IF(settlement_reports.type = 'Order', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) AS total_ordered"),
              DB::raw("SUM(IF(settlement_reports.type = 'Refund', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) AS total_refunded"),
              DB::raw("SUM(IF(settlement_reports.type = 'Adjustment', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) AS total_adjusted"),
              DB::raw("IF(returns_reports.quantity > 0,IF(returns_reports.return_date + INTERVAL 45 DAY <= NOW(),'YES','NO'),NULL) AS over_45days"),
              DB::raw("IF(returns_reports.quantity > 0,IF(SUM(IF(settlement_reports.type = 'Adjustment',settlement_reports.price_amount,0)) = 0,'Full','Partial'),NULL) AS claim_type"),
              DB::raw("SUM(IF(settlement_reports.type = 'Refund', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) + SUM(IF(settlement_reports.type = 'Adjustment', (
                settlement_reports.price_amount
                +settlement_reports.promotional_rebates
                +settlement_reports.other_amount
                +settlement_reports.shipment_fee_amount
                +settlement_reports.order_fee_amount
                +settlement_reports.direct_payment_amount
                +settlement_reports.item_related_fee_amount),0)) AS claim_amount")
            ]);
          }else{
            return DB::connection('mysql2')
              ->table('settlement_reports')
              ->leftJoin('returns_reports', 'settlement_reports.order_id', '=', 'returns_reports.order_id')
              ->where('settlement_reports.seller_id', '=', $seller_id)
              ->where('settlement_reports.marketplace_name', 'like', '%'.strtolower($country))
              ->groupBy('settlement_reports.order_id')
              ->get([
                'settlement_reports.id',
                'settlement_reports.order_id',
                'settlement_reports.marketplace_name',
                'returns_reports.quantity AS quantity_returned',
                'returns_reports.return_date AS date_of_return',
                'returns_reports.order_id AS oid',
                'returns_reports.detailed_disposition',
                DB::raw("SUM(IF(settlement_reports.type = 'Order',settlement_reports.quantity,0)) AS quantity_ordered"),
                DB::raw("SUM(IF(settlement_reports.type = 'Refund',settlement_reports.quantity,0)) AS quantity_refunded"),
                DB::raw("SUM(IF(settlement_reports.type = 'Adjustment',settlement_reports.quantity,0)) AS quantity_adjusted"),
                DB::raw("SUM(IF(settlement_reports.type = 'Order', (
                  settlement_reports.price_amount
                  +settlement_reports.promotional_rebates
                  +settlement_reports.other_amount
                  +settlement_reports.shipment_fee_amount
                  +settlement_reports.order_fee_amount
                  +settlement_reports.direct_payment_amount
                  +settlement_reports.item_related_fee_amount),0)) AS total_ordered"),
                DB::raw("SUM(IF(settlement_reports.type = 'Refund', (
                  settlement_reports.price_amount
                  +settlement_reports.promotional_rebates
                  +settlement_reports.other_amount
                  +settlement_reports.shipment_fee_amount
                  +settlement_reports.order_fee_amount
                  +settlement_reports.direct_payment_amount
                  +settlement_reports.item_related_fee_amount),0)) AS total_refunded"),
                DB::raw("SUM(IF(settlement_reports.type = 'Adjustment', (
                  settlement_reports.price_amount
                  +settlement_reports.promotional_rebates
                  +settlement_reports.other_amount
                  +settlement_reports.shipment_fee_amount
                  +settlement_reports.order_fee_amount
                  +settlement_reports.direct_payment_amount
                  +settlement_reports.item_related_fee_amount),0)) AS total_adjusted"),
                DB::raw("IF(returns_reports.quantity > 0,IF(returns_reports.return_date + INTERVAL 45 DAY <= NOW(),'YES','NO'),NULL) AS over_45days"),
                DB::raw("IF(returns_reports.quantity > 0,IF(SUM(IF(settlement_reports.type = 'Adjustment',settlement_reports.price_amount,0)) = 0,'Full','Partial'),NULL) AS claim_type"),
                DB::raw("SUM(IF(settlement_reports.type = 'Refund', (
                  settlement_reports.price_amount
                  +settlement_reports.promotional_rebates
                  +settlement_reports.other_amount
                  +settlement_reports.shipment_fee_amount
                  +settlement_reports.order_fee_amount
                  +settlement_reports.direct_payment_amount
                  +settlement_reports.item_related_fee_amount),0)) + SUM(IF(settlement_reports.type = 'Adjustment', (
                  settlement_reports.price_amount
                  +settlement_reports.promotional_rebates
                  +settlement_reports.other_amount
                  +settlement_reports.shipment_fee_amount
                  +settlement_reports.order_fee_amount
                  +settlement_reports.direct_payment_amount
                  +settlement_reports.item_related_fee_amount),0)) AS claim_amount")
              ]);
          }
        }

	public function isExist($data=array()){
        $db_query = DB::connection('mysql2');
    	$query = $db_query->table('settlement_reports')->select('*');
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
            $db_query->table('settlement_reports')->insert($data);
            return true;
        }else{
            return false;
        }
    }
    public function getRecords($fields = array('*'),$cond=array(),$order=array(),$checkifempty=false){
        $q = DB::connection('mysql2')->table('settlement_reports');
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
}
