<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    // public function country(){
    //   return $this->belongsTo('Webpatser\Countries\Countries','country_id');
    // }

	public function plan_currency()
    {
        return $this->hasMany('App\PlanCurrency');
    }

    public function subscription_plan()
    {
        return $this->hasMany('App\SubscriptionPlan');
    }
}
	