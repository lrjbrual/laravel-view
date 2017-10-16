<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
// use Auth;

use App\User;
use App\TrialPeriod;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home?action=loggedin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */

   private $user_model;
   private $trialperiod_model;

   public function __construct()
    {
      $this->user_model = new User();
      $this->trialperiod_model = new TrialPeriod();
    }

    public function email(){
        return 'email';
    }

    public function index(){

      return view('welcome');
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }




    public function showLoginForm()
    {
      if (Auth::guard()->check()) {
          return redirect($this->redirectTo);
      }
      return view('auth.login');
    }


    public function login(Request $request)
    {

        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {

            $seller_id=$this->guard()->user()->seller_id;
            $is_active = $this->guard()->user()->is_active;
            $datetoday_string = strtotime(date('Y-m-d H:i:s'));

            $f=array("is_verified");
            $c=array('is_verified'=>1,'seller_id'=>$seller_id);
            $o=array();
            $q=$this->user_model->getRecords(config('constant.tables.user'),$f,$c,$o);
            // print_r($q);die();
            if(!$q->isEmpty()){
              $f=array("trial_end_date","is_activated");
              $c=array('seller_id'=>$seller_id);
              $o=array();
              $q=$this->trialperiod_model->getRecords(config('constant.tables.trialperiod'),$f,$c,$o);
              $d=$q[0];



              if ($d->is_activated==true){
                $account_status = '1';// CC verified/activated
                // return $this->sendFailedLoginResponse($request,1);
              }else if((strtotime($d->trial_end_date) > $datetoday_string) OR ($d->trial_end_date=='0000-00-00 00:00:00')){
                $account_status = '2';// under trial
                // return $this->sendFailedLoginResponse($request,1);
              }else{
                $account_status = '3';  // expired trial
                // return $this->sendFailedLoginResponse($request,1);
              }
              $request->session()->put('account_status', $account_status);
              // Auth::user()['account_status']=$account_status;
              // echo Auth::user();die();
              // return true;

             

              $user_verified = true;
            } else {
              $user_verified = false;
            }

            if ($is_active == 1) {
              $user_active = true;
            } else {
              $user_active = false;
            }

            if ($user_verified == true && $user_active == true) {
              return $this->sendLoginResponse($request);
            } else if ($user_verified == true && $user_active == false) {
              Auth::logout();
              return $this->sendFailedLoginResponse($request,0);
            } else if ($user_verified == false && $user_active == false) {
              Auth::logout();
              return $this->sendFailedLoginResponse($request,2);
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request,0);
    }

    protected function sendFailedLoginResponse(Request $request,$error_code=0)
    {
      // echo $error_code;die();
      switch($error_code){
        case 0:$errors=[$this->username() => trans('auth.failed')];break;
        case 1:$errors=[$this->username() => trans('auth.failed_but_correct_credentials')];break;
        case 2:$errors=[$this->username() => trans('auth.unverified_account')];break;
      }


        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }

    protected function authenticated(Request $request, $user)
    {

    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */

}
