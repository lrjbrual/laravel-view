<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class BaseSubscriptionSeller extends Model
{
    //
    public function getBSSandBST($seller_id)
    {
    	$getBSS = DB::table('base_subscription_sellers as bss')
	                        ->leftJoin('base_subscription_seller_transactions as bst','bss.id', '=', 'bst.bss_id')
	                        ->where('currently_used', 1)
	                        ->where('bss.seller_id',$seller_id)
	                        ->first();
	    return $getBSS;
    }
}
