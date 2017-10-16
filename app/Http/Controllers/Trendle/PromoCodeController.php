<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;

use App\PromoCode;
use App\PromocodeA;
use App\PromoSubscription;

class PromoCodeController extends Controller
{
    public function store(Request $request)
    {
        // $seller_id = Auth::user()->seller->id;

        // // Clear all active promo code
        // Promocode::where('seller_id', '=', $seller_id)->where('is_active', '=', 1)->update(array('is_active' => 0));

        // $pc = Promocode::where('stripe_coupon_id', '=', $request->coupon_id)
        //                 ->where('seller_id', '=', $seller_id)->first();

        // // Save latest promo code
        // if (isset($pc)) {
        //     $promocode = Promocode::find($pc->id);
        // } else {
        //     $promocode = new PromoCode;
        // }
        // $promocode->stripe_coupon_id = $request->coupon_id;
        // $promocode->currency = $request->currency;
        // $promocode->amount_off = $request->amount_off;
        // $promocode->percent_off = $request->percent_off;
        // $promocode->is_active = 1;
        // $promocode->seller_id = $seller_id;
        // $promocode->save();

        $seller_id = Auth::user()->seller->id;

        $ps = new PromoSubscription;

        $ps->seller_id = $seller_id;
        $ps->voucher_code = $request->voucher_code;
        $ps->is_used = 0;

        $ps->save();

        return response()->json([
                    'status' => 'success',
                ]);
    }
}
