<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignTrigger extends Model
{
    //

    public function campaign_email()
    {
      return $this->hasMany('App\CampaignEmail', 'campaign_id');
    }

    public function campaign_template()
    {
      return $this->hasMany('App\CampaignTemplate', 'campaign_id');
    }

    public function getRecords($fields=array('*'),$cond=array(),$order=array()){

        $q = DB::table('campaign_triggers');
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
