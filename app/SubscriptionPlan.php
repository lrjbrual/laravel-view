<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    public function plan()
    {
        return $this->belongsTo('App\Plan','plan_id');
    }

    public function plan_currency()
    {
        return $this->belongsTo('App\PlanCurrency','plan_currency_id');
    }
}
