<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Route;
use Auth;

use App\Seller;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkStripe');
    }

    public function deleteAccount()
    {
        $seller_id = Auth::user()->seller_id;
        $data = $this->callBaseSubscriptionName($seller_id);
        // if ($data->base_subscription == '' && Auth::user()->seller->is_trial == 0) {
        //     return redirect('subscription');
        // }
        return view('trendle.account.index')->with('bs',$data->base_subscription);
    }

    private function callBaseSubscriptionName($seller_id) {
      $data = (object) null;

      $data->base_subscription = '';
      $is_trial = Auth::user()->seller->is_trial;

      if ($is_trial == 1) {
        $data->base_subscription = 'XL';
      } else {
        $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller_id)->first();
        if (isset($bss)) {
            $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                        ->where('currently_used', '=', true)
                                                        ->first();
            $data->base_subscription = $bsst->bs_name;
        }
      }

      return $data;
    }

    public function deleteConfirmed(Request $request)
    {
        $seller = Seller::find(Auth::user()->seller_id);
        $seller->is_deleted = true;
        $seller->reason_for_leaving = $request->reason;
        $seller->save();

        if ($seller->billing && $seller->billing->hasStripeId()) {
            $seller->billing->deleteCards();
            $seller->billing->delete();
        }

        foreach ($seller->user as $key => $user) {
            $user->is_active = false;
            $user->save();
        }
    }

}
