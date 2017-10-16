<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Billing;

class ValidateStripe
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */


    public function handle($request, Closure $next)
    {
        $sid = Auth::user()->seller->id;
        $billing_model = new Billing;
        $bb = $billing_model->getRecords('billings','payment_valid',array('seller_id'=>$sid))->first();

        if(Auth::user()->seller->is_trial == 0)
        {
            if(!isset(Auth::user()->seller->billing) || $bb->payment_valid == 0)
            {
                return redirect('billing');
            }
            if(Auth::user()->seller->basesubscription->count() == 0)
            {
                return redirect('subscription');
            }
        }
        return $next($request);
    }
}
