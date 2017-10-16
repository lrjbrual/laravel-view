<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlanCurrency extends Model
{
    public function country(){
      return $this->belongsTo('Webpatser\Countries\Countries','country_id');
    }

    public function plan()
    {
        return $this->belongsTo('App\Plan','plan_id');
    }

    public function subscription_plan()
    {
        return $this->hasMany('App\SubscriptionPlan');
    }
}