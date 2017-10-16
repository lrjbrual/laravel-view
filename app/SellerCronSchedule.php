<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SellerCronSchedule extends Model
{
	public function getRecords($fields=array('*'),$cond=array(),$order=array()){

		$q = DB::table('seller_cron_schedules');
		$q = $q->select($fields);
		$q = $q->join('cron_master_lists', 'seller_cron_schedules.cron_id', '=', 'cron_master_lists.id');

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

		if(count($order)>0){
			$q = $q->orderBy($order[0],$order[1]);
		}

		$q = $q->get();
		return $q;
	}
}
