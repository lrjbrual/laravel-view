<?php

namespace App\Http\Controllers\Trendle\Marketplace;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use Route;

use App\MarketplaceAssign;
use App\AmazonSellerDetail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\MWSCustomClasses\MWSCurlAdvertisingClass;

use AmazonReport;
use AmazonMWSConfig;
use AmazonReportRequest;
use AmazonReportList;
use AmazonParticipationList;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;


class MarketplaceController extends Controller
{
    private $get_report_waiting_time = 10;
    private $mkpassign_model;

    public function __construct()
    {
      $this->mkpassign_model = new MarketplaceAssign();
      $this->middleware('auth');
    }



    public function getTry () {
      // $mkpassign_model = new MarketplaceAssign();
      // $mkpassign_model->test();
      // die();

      header('X-Accel-Buffering: no');



      $amazonConfig = array(
          'stores' =>
              array('YourAmazonStore' =>
                  array(
                      // 'merchantId'    => 'A3459HHXQZ09VF',
                      'merchantId'    => 'A3459HHXQZ09VK',
                      'marketplaceId' => 'A1F83G8C2ARO7P',
                      'keyId'         => 'AKIAI6K3WYU4WYIDVUTA',
                      'secretKey'     => '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',
                      'serviceUrl'    => '',
                      // 'MWSAuthToken'  => 'amzn.mws.02ab729c-1592-0a87-174f-03ad80b409bf',
                      'MWSAuthToken'  => 'amzn.mws.02ab729c-1592-0a87-174f-03ad80b109bf',
                  )
              ),
          'AMAZON_SERVICE_URL'        => 'https://mws.amazonservices.co.uk', // eu store
          'logpath'                   => __DIR__ . './logs/amazon_mws.log',
          'logfunction'               => '',
          'muteLog'                   => false
      );

      $configObject = new \AmazonMWSConfig($amazonConfig);

      $amz = new AmazonParticipationList($configObject);
      $amz->fetchParticipationList();
      $a = $amz->getParticipationList();
      if(!empty($a)){
        echo 'valid';
      }else{
        echo 'no no';
      }
      //print_r($a);die();

      $amz = new AmazonReportRequest($configObject);

      $amz->setReportType('_GET_FBA_MYI_ALL_INVENTORY_DATA_');
      $amz->setMarketplaces('A13V1IB3VIYZZH');
      $response = $amz->requestReport();

      $ctr=0;
      $request_id = $amz->getResponse()['ReportRequestId'];
      echo $ctr.':'.$request_id.'<br>';
      if($request_id != null && $request_id != ""){
        echo "<br/><b>DONE!!!</b> <i>request_id = " . $request_id . "</i>";

        flush();

        $report_id = null;

        echo "<br/>Getting report data from Amazon ...";
        flush();

        $report_request_tries = 1;

        $get_report_response = array();



        $amz_reportreq = new AmazonReportList($configObject);
        $amz_reportreq->setRequestIds($request_id);
        while($report_id == null)
        {


          $amz_reportreq->fetchReportList();

          $haslist = $amz_reportreq->getList();

          if($haslist!=false){


            $report_id = $amz_reportreq->getReportId();
            if($report_id == null)
            {
              echo "<br/><b>Report Not Yet Available ... retrying after " . ($this->get_report_waiting_time / 60). " minutes ..</b>";
              flush();

              sleep($this->get_report_waiting_time);

              $report_request_tries += 1;
              echo "<br/><b style='color:green'>Retrying ...</b>";
              flush();

            }

          }else{

              echo "<br/><b>Report Not Yet Available ... retrying after " . ($this->get_report_waiting_time / 60). " minutes ..</b>";
              flush();

              sleep($this->get_report_waiting_time);

              $report_request_tries += 1;
              echo "<br/><b style='color:green'>Retrying ...</b>";
              flush();

          }


        }


          echo "<br/><b>DONE after " . $report_request_tries . " tries.</b> <i>report_id = " . $report_id . "</i>";

          flush();

          echo "<br/>Parsing response ... ";

          flush();

          $amz_report = new AmazonReport($configObject);
          $amz_report->setReportId($report_id);

          $amz_report->fetchReport();
          $report = $amz_report->getRawReport();

          print_r($report);


          if($report != null)
          {
            echo "<br/><b>DONE!!!</b>";

            flush();

            echo "<br/>Saving to database ... ";

            flush();


            echo "<br/><b>DONE!!!</b>";

            flush();
            echo $total_records;
          }
          else
          {
          echo 'error2';

            exit;
          }
      }

    }




    public function index(){

      $seller_id = Auth::user()->seller_id;
      $f=array("*");
      $c=array('seller_id'=>$seller_id);
      $o=array();
      $q=$this->mkpassign_model->getRecords(config('constant.tables.mkp'),$f,$c,$o);
      $amz['na'] = AmazonSellerDetail::where('seller_id', $seller_id)->where('mkp_id', 1)->where('is_active', 1)->get();
      $amz['eu'] = AmazonSellerDetail::where('seller_id', $seller_id)->where('mkp_id', 2)->where('is_active', 1)->get();
      $arr=array(
        'mkpdata'=>$q,
        'seller_id'=>$seller_id,
        'seller_amz' => $amz
      );

      $data = $this->callBaseSubscriptionName($seller_id);
      return view('trendle.marketplace.index',$arr)
              ->with('bs',$data->base_subscription);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'mkp_sellerid' => 'required|max:255|unique:marketplace_assigns,mws_seller_id',
            'mkp_authtoken' => 'required|max:255|unique:marketplace_assigns,mws_auth_token',
        ]);
    }

    public function messages()
    {
        return [
            'mkp_sellerid.required' => 'A title is required',
            'mkp_authtoken.required'  => 'A message is required',
        ];
    }

    public function saveMarketplace(Request $request)
    {
        $v = $this->validator($request->all());
        $re = array();
        if ($v->fails())
        {
          $e = $v->errors()->toArray();
          $re['status']='error';
          $re['details']=$e;
          echo json_encode($re);
          die();
        }

        $seller_id = Auth::user()->seller_id;
        $mkp_id=$request->mkpid;

        switch ($request->mkpid){
          case 1:
            $country_key = 'na';
            $urlpref = '.com';
            break;
          case 2:
            $country_key = 'eu';
            $urlpref = '.co.uk';
            break;
        }


        $amazonConfig = array(
            'stores' =>
                array('YourAmazonStore' =>
                    array(
                        'merchantId'    => $request->mkp_sellerid,
                        'MWSAuthToken'  => $request->mkp_authtoken,
                        'marketplaceId' => 'A1F83G8C2ARO7P',
                        'keyId'         => config('constant.amz_keys.'.$country_key.'.access_key'),
                        'secretKey'     => config('constant.amz_keys.'.$country_key.'.secret_key'),
                        'serviceUrl'    => '',

                    )
                ),
            'AMAZON_SERVICE_URL'        => 'https://mws.amazonservices'.$urlpref, // eu store
            'logpath'                   => __DIR__ . './logs/amazon_mws.log',
            'logfunction'               => '',
            'muteLog'                   => false
        );

        $configObject = new \AmazonMWSConfig($amazonConfig);

        $amz = new AmazonParticipationList($configObject);

        $amz->fetchParticipationList();
        $a = $amz->getParticipationList();
        if(!empty($a)){
          $this->mkpassign_model->insertRecord($request->all());
          exec("curl '" . config('app.url') . "/MasterCronScript?seller_id=" . $seller_id . "&mkp=" . $mkp_id . "' > /dev/null 2>&1 & echo $!",$output);
          $re['status']='success';
          echo json_encode($re);
          //notif code here
        }else{
          $re['status']='failed';
          echo json_encode($re);
          //notif code here
        }




    }

    public function removeMarketplace(Request $request)
    {
      $this->mkpassign_model->deleteRecord($request->all());
    }



    static function routes()
    {
       Route::group(array('prefix' => 'marketplace'), function() {
          Route::get('/', 'Trendle\Marketplace\MarketplaceController@index');

          Route::post('saveMarketplace', 'Trendle\Marketplace\MarketplaceController@saveMarketplace');
          Route::post('removeMarketplace', 'Trendle\Marketplace\MarketplaceController@removeMarketplace');
          Route::get('z', 'Trendle\Marketplace\MarketplaceController@getTry');
          Route::get('auth_amz_account', 'Trendle\Marketplace\MarketplaceController@auth_amz_account');
       });

    }

    public function auth_amz_account(){
      $seller_id = Auth::user()->seller_id;
      //requesting for access token and refresh token
      $amz = new MWSCurlAdvertisingClass;
      if(isset($_REQUEST['code'])){
        $d = $amz->get_access_token($_REQUEST['code']);
        $data = array();
        //check if exist in the return for refresh tokens and access tokens
        if(isset($d->refresh_token) AND isset($d->access_token)){
          $refresh_token = $d->refresh_token;
          $access_token = $d->access_token;
          $token_type = $d->token_type;
          //getting seller profiles using access token
          $amz = new MWSCurlAdvertisingClass;
          $data = $amz->get_seller_profiles($access_token);
          //saving seller info
          $na_exist = AmazonSellerDetail::where('seller_id', $seller_id)->where('mkp_id', 1)->where('is_active', 1)->get();
          $eu_exist = AmazonSellerDetail::where('seller_id', $seller_id)->where('mkp_id', 2)->where('is_active', 1)->get();
          $this->save_seller_info($data, $access_token, $refresh_token, $token_type);
        }else{
          return redirect('marketplace/')->with('status', "The credentials you entered could not be verified. Please make sure the log in details and correct, valid and active in your seller central account. Then try again. If the error persists, contact us and we'll take a look.");
        }
        $eu = false;
        $na = false;
        if( isset($data['na']) AND count($data['na']) > 0 ){
          if( count($na_exist) == 0 ) 
            exec("curl '" . config('app.url') . "/UpdateAdvertCampaigns?seller_id=" . $seller_id . "&mkp=1' > /dev/null 2>&1 & echo $!",$output);
          $na = true;
        }
        if( isset($data['eu']) AND count($data['eu']) > 0 ){
          if( count($eu_exist) == 0 )
            exec("curl '" . config('app.url') . "/UpdateAdvertCampaigns?seller_id=" . $seller_id . "&mkp=2' > /dev/null 2>&1 & echo $!",$output);
          $eu = true;
        }
        if( $eu AND $na ){ 
          return redirect('marketplace/')->with('status', 'Connected to Amazon NA and EU regions!');
        }else if( !$eu AND $na ){
          if( count($eu_exist) > 0 )
            return redirect('marketplace/')->with('status', 'Connected to Amazon NA and EU regions!');
          else
            return redirect('marketplace/')->with('status', 'Connected to Amazon NA region! You can login again to connect your EU region.');
        }else if( !$na AND $eu ){
          if( count($na_exist) > 0 )
            return redirect('marketplace/')->with('status', 'Connected to Amazon NA and EU regions!');
          else
            return redirect('marketplace/')->with('status', 'Connected to Amazon EU region! You can login again to connect your NA region.');
        }else{
          return redirect('marketplace/')->with('status', 'The email is not connected to any region!');
        }
      }else{
        return redirect('marketplace/')->with('status', "The credentials you entered could not be verified. Please make sure the log in details and correct, valid and active in your seller central account. Then try again. If the error persists, contact us and we'll take a look.");
      }
    }

    //saving seller info with access and refresh tokens
    public function save_seller_info($data, $access_token, $refresh_token, $token_type){
      $eu = $data['eu'];
      $na = $data['na'];

      //saving profiles in EU
      if(count($eu) > 0){
        foreach ($eu as $key => $d) {
          $amz = new AmazonSellerDetail;
          $amz->seller_id = Auth::user()->seller_id;
          $amz->mkp_id = 2;
          $amz->amz_profile_id = $d->profileId;
          $amz->amz_country_code = $d->countryCode;
          $amz->amz_access_token = $access_token;
          $amz->amz_refresh_token = $refresh_token;
          $amz->amz_token_type = $token_type;
          $amz->amz_expires_in = Carbon::now()->addHour();
          $amz->is_active = 1;
          $amz->save();
        }
      }

      //saving profiles in NA
      if(count($na) > 0){
        foreach ($na as $key => $d) {
          $amz = new AmazonSellerDetail;
          $amz->seller_id = Auth::user()->seller_id;
          $amz->mkp_id = 1;
          $amz->amz_profile_id = $d->profileId;
          $amz->amz_country_code = $d->countryCode;
          $amz->amz_access_token = $access_token;
          $amz->amz_refresh_token = $refresh_token;
          $amz->amz_token_type = $token_type;
          $amz->amz_expires_in = Carbon::now()->addHour();
          $amz->is_active = 1;
          $amz->save();
        }
      }
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
}