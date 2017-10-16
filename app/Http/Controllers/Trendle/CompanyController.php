<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

use Countries;
use Route;
use Auth;

use App\Seller;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $countries = Countries::getListForSelect();
        $seller = Seller::find(Auth::user()->seller_id);
        $data = $this->callBaseSubscriptionName($seller->id);

        return view('trendle.company.index')
            ->with('countries', $countries)
            ->with('seller', $seller)
            ->with('bs',$data->base_subscription);
    }

    public function update(Request $request, $id)
    {
        $seller = Seller::find($id);
        $seller->firstname = $request->firstname;
        $seller->lastname = $request->lastname;
        $seller->company = $request->company;
        $seller->country_id = $request->country_id;
        $seller->save();

        flash('Contact successfully saved.', 'success');
        return redirect('company');
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ]);

        Auth::user()->fill([
            'password' => Hash::make($request->password)
        ])->save();

        flash('Password successfully changed.', 'success');
        return redirect('company');
    }

    /**
     *
     * Gets the bs_name from base_subscription_sellers table
     * and adds a checker for the radio buttons of the view
     *
     * @param    integer    $seller_id
     * @return   object     $data
     *
     */
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

    static function routes()
    {
        Route::resource('company', 'Trendle\CompanyController');

        Route::group(array('prefix' => 'company'), function() {
            Route::post('changePassword', 'Trendle\CompanyController@changePassword');
        });
    }
}
