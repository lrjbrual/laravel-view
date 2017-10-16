<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrialPeriod extends Model
{
    //

    protected $fillable = [
        'seller_id',
        'date_registered',
        'trial_start_date',
        'trial_end_date',
        'is_activated',
        'date_activated'
    ];

    protected $hidden = [

    ];

    public function getRecordsByDateEnd($date_end){
      $q = DB::table('trial_periods');
      $q = $q->select('*')->whereDate('trial_end_date', '=', $date_end)->get();
      return $q;
    }


    public function getRecords($table="",$fields=array('*'),$cond=array(),$order=array()){

        $q = DB::table($table);
        $q = $q->select($fields);

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



    public function insertRecord(array $data)
    {

      return TrialPeriod::create([
          'seller_id' => $data['new_seller_id'],
          'date_registered' => $data['date_today'],
          'trial_start_date' => null,
          'trial_end_date' =>  null,
          'is_activated' => 0,
          'date_activated' => null,
      ]);

    }
}
