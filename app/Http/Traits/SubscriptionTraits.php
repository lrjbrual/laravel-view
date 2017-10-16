<?php

namespace App\Http\Traits;

use Auth;

use App\Subscription;
use App\SubscriptionPlan;

trait SubscriptionTraits
{
    public function storeSubscriptionPlans($plans = null)
    {
        $subscription = Subscription::where('seller_id', Auth::user()->seller->id)->first();

        // Remove current Subscription Plans
        $currentSPlans = SubscriptionPlan::where('seller_id', Auth::user()->seller->id)->get();
        foreach ($currentSPlans as $plan) {
            $plan->delete();
        }

        if(is_array($plans)){
            // Insert newly selected plans
            foreach ($plans as $plan_id) {
                $subscriptionPlans = new SubscriptionPlan;
                $subscriptionPlans->seller_id = $subscription->seller_id;
                $subscriptionPlans->subscription_id = $subscription->id;
                $subscriptionPlans->plan_id = $plan_id;
                $subscriptionPlans->save();
            }
        } else {
            $subscriptionPlans = new SubscriptionPlan;
            $subscriptionPlans->seller_id = $subscription->seller_id;
            $subscriptionPlans->subscription_id = $subscription->id;
            $subscriptionPlans->plan_id = $plans;
            $subscriptionPlans->save();
        }

    }
}
