<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Laravel\Cashier\Billable;
use Countries;

class Billing extends Model
{
    use Billable;

    public function has_subscription()
    {
      return $this->hasOne('App\Subscription', 'billing_id');
    }

    public function country_code($country_code)
    {
      $country = Countries::getOne($country_code);
      return $country['iso_3166_2'];
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

}
