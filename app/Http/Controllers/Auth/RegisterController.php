<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Seller;
use App\TrialPeriod;
use App\RegistrationToken;
use App\Todo;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Mail\Confirmation;
use App\Mail\FbaCSEmailNotif;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use \Config;

use Mail;
use Carbon\Carbon;
use Countries;

class RegisterController extends Controller
{

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $user_model;
    private $seller_model;
    private $trialperiod_model;
    private $regtoken_model;

    public function __construct()
    {
        $this->middleware('guest');

        $this->user_model = new User();
        $this->seller_model = new Seller();
        $this->trialperiod_model = new TrialPeriod();
        $this->regtoken_model = new RegistrationToken();
    }

    public function verify_confirmation($token){

      $dstrtoday=strtotime(date('Y-m-d H:i:s'));

      $f=array("*");
      $c=array('token'=>$token,'status'=>0);
      $o=array();
      $q=$this->regtoken_model->getRecords(config('constant.tables.regtoken'),$f,$c,$o);

      if($q->count()){
        $d=$q[0];
        $token_id=$d->id;
        if((strtotime($d->expiration)>$dstrtoday)&&($d->status==0)){

          $f=array("*");
          $c=array('email'=>$d->email);
          $o=array();
          $q=$this->seller_model->getRecords(config('constant.tables.seller'),$f,$c,$o);
          $d=$q[0];
          $fname = $d->firstname;

          $f=array("*");
          $c=array('email'=>$d->email);
          $o=array();
          $q=$this->user_model->getRecords(config('constant.tables.user'),$f,$c,$o);
          $d=$q[0];
          $sid = DB::table('users')->where('id',$d->id)
              ->first();
          DB::table(config('constant.tables.user'))
            ->where('id', $d->id)
            ->update(['is_verified' => 1, 'is_active' => 1]);
          DB::table(config('constant.tables.seller'))
            ->where('id', $sid->seller_id)
            ->update(['is_trial' => true]);
          DB::table(config('constant.tables.regtoken'))
            ->where('id', $token_id)
            ->update(['status' => 1]);
          DB::table('trial_periods')
            ->where('seller_id', $sid->seller_id)
            ->update(['trial_start_date' => Carbon::now(), 'trial_end_date' => Carbon::now()->addDays(29)]);

          Auth::loginUsingId($d->id);
          return view('pages.thankyou')->with('email',$d->email)
                       ->with('fname',$fname);
        }else{
          return view('pages.registration_expire');
        }
      }else{
        return view('pages.registration_expire');
      }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'fname' => 'required|max:255',
            'lname' => 'required|max:255',
            'company' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    public function showRegistrationForm()
    {
        $topCountries = [
          '250'  => 'France',
          '380'  => 'Italy',
          '276'  => 'Germany',
          '484'  => 'Mexico',
          '724'  => 'Spain',
          '826'  => 'United Kingdom',
          '840'  => 'United States of America',
          '124'  => 'Canada'
        ];

        $countries = $countries = Countries::getListForSelect();

        return view('auth.register')
                  ->with('topCountries', $topCountries)
                  ->with('countries', $countries);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */


   public function register(Request $request)
   {

       $this->validator($request->all())->validate();

       $reg_roken = Str::random(10) . strtotime(date('Y-m-d H:i:s')) . Str::random(10);
       $user = $this->create($request->all(),$reg_roken);
       event(new Registered($user));

       $email = $request->email;
       $fname = $request->fname;
       $lname = $request->lname;

       return $this->registered($request, $user, $reg_roken)
                       ?: view('pages.verify')->with('email',$email)
                       ->with('reg_roken',$reg_roken)
                       ->with('fname',$fname)
                       ->with('lname',$lname);
   }


   protected function registered(Request $request, $user, $token)
   {
      //backup default config
      $backup = Config::get('mail');
      //set new config for sparkpost
      if(env('SPARKPOST_MAIL_DRIVER') != ""){
        Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));
      }

      Mail::to($request->email)->send(new Confirmation($request, $token));
      Auth::logout();

      //restore default config
      Config::set('mail', $backup);
   }

   protected function verify(Request $request)
   {

      //backup default config
      $backup = Config::get('mail');
      //set new config for sparkpost
      if(env('SPARKPOST_MAIL_DRIVER') != ""){
        Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));
      }

      Mail::to($request->email)->send(new Confirmation($request, $request->token));
      Auth::logout();

      //restore default config
      Config::set('mail', $backup);

      $email = $request->email;
      $fname = $request->fname;
      $lname = $request->lname;
      $reg_roken = $request->token;

      return view('pages.verify')->with('email',$email)
                       ->with('reg_roken',$reg_roken)
                       ->with('fname',$fname)
                       ->with('lname',$lname);
   }

   protected function send_cs_email($sc_email, $crm_email){
    $backup = Config::get('mail');
      if(env('SPARKPOST_MAIL_DRIVER') != ""){
        Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));
      }
      Mail::to('customerservice@trendle.io')->send(new FbaCSEmailNotif($sc_email, $crm_email));
      Config::set('mail', $backup);
   }



  protected function create(array $data,$token)
  {
    $date_today = date('Y-m-d H:i:s');
    $date_plus1day = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s'))+86400);
    $new_seller = $this->seller_model->insertRecord($data);
    $new_seller_id = $new_seller->id;
    $crm_email = "crm.".$new_seller_id."@trendle.io";
    $sc_email = "sc.".$new_seller_id."@trendle.io";
    $sc = Seller::find($new_seller_id);
    $sc->email_for_sc = $sc_email;
    $sc->email_for_crm = $crm_email;
    $sc->save();
    $this->send_cs_email($crm_email, $sc_email);
    $data['new_seller_id']=$new_seller_id;
    $data['date_today']=$date_today;
    $data['date_plus1day']=$date_plus1day;
    $data['token']=$token;
    $this->trialperiod_model->insertRecord($data);
    $this->regtoken_model->insertRecord($data);
    $this->createDefaultTodoList($new_seller_id);

    return $this->user_model->insertRecord($data);
  }

  private function createDefaultTodoList($seller_id)
  {
    $todos = collect([
      ['seller_id' => $seller_id, 'todo' => 'Enter API Authorisations credentials in "Marketplace"'],
      ['seller_id' => $seller_id, 'todo' => 'Manage my Subscriptions'],
      ['seller_id' => $seller_id, 'todo' => 'Enter VAT ID (if applicable), complete Billing Address and enter a payment method before Free Trial ends'],
      ['seller_id' => $seller_id, 'todo' => 'Create first Automatic Email Campaign!'],
      ['seller_id' => $seller_id, 'todo' => 'See how much money Amazon owes me under "FBA Refunds"'],
      ['seller_id' => $seller_id, 'todo' => 'Create first Automatic Email Campaign!'],
      ['seller_id' => $seller_id, 'todo' => 'Work through any negative Seller Reviews'],
    ]);

    $todos->each(function ($item, $key) {
        $todo = new Todo;
        $todo->color_class = "todo_mintbadge";
        $todo->item = $item['todo'];
        $todo->is_striked = 0;
        $todo->seller_id = $item['seller_id'];
        $todo->created_at = Carbon::now();
        $todo->save();
    });
  }

}
