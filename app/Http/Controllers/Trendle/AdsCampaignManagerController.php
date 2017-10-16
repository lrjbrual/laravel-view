<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Trendle\PnLController;
use App\Http\Controllers\Crons\UpdateAdvertCampaignsController as AdsCampaignCron;

use Auth;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;
use Illuminate\Support\Facades\DB;
use App\AmazonSellerDetail as Amz;
use App\MWSCustomClasses\MWSCurlAdvertisingClass as MWSCurl;
use App\Product;
use Carbon\Carbon;
use App\AdsCampaign;
use App\AdsCampaignAdGroup;
use App\AdsCampaignKeyword;
use App\AdsCampaignProduct;

class AdsCampaignManagerController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('checkStripe');
    }

    public function index(){
        $seller_id = Auth::user()->seller_id;
        $data = $this->callBaseSubscriptionName($seller_id);

        $pnlcont = new PnLController;
        $countries = $pnlcont->getCountryListForThisSeller();
        $country = array();
        $countryRec = array();

        $amz_c = Amz::where('seller_id', $seller_id)->get(['amz_country_code']);
        $a_c = array();
        foreach ($amz_c as $key => $value) {
            $a_c[] = strtolower($value->amz_country_code);
        }

        foreach ($countries as $key => $value) {
            $needle = strtolower($value->iso_3166_2);
            if($needle == 'gb') $needle = 'uk';
            if(in_array($needle, $a_c)){
                if (strtolower($value->iso_3166_2) == 'gb') {
                    $country_name = config('constant.country_list.'.'uk');
                    $country['uk'.'|'.$country_name] = $country_name;
                    $countryRec['UK'] = $country_name;
                } else {
                    $country_name = config('constant.country_list.'.strtolower($value->iso_3166_2));
                    $country[$value->iso_3166_2.'|'.$country_name] = $country_name;
                    $countryRec[$value->iso_3166_2] = $country_name;
                }
            }
        }
        
        ksort($country);
        $countries = array();
        $countries = $country;
        ksort($countryRec);
        $countriesRec = array();
        $countriesRec = $countryRec;

        $countries = array();
        $countries = $country;

        $camp = AdsCampaign::where('seller_id', $seller_id)->where('targetingtype', 'auto')->get();
        $camp_auto = array();
        foreach ($camp as $key => $value) {
            $camp_auto[$value->id] = $value->name;
        }

        $camp = AdsCampaign::where('seller_id', $seller_id)->where('targetingtype', 'manual')->get();
        $camp_manual = array();
        foreach ($camp as $key => $value) {
            $camp_manual[$value->id] = $value->name;
        }

        return view('trendle.campaignmanager.index')
                ->with('bs',$data->base_subscription)
                ->with('countries', $countries)
                ->with('countriesRec', $countriesRec)
                ->with('camp_auto', $camp_auto)
                ->with('camp_manual', $camp_manual);
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

    public function getCampaignData(Request $req){
    	$seller_id = Auth::user()->seller_id;
        $draw = $req->draw;
        $skip = $req->start;
        $take = $req->length;
        //for sorting
        $sort_column = $req->order[0]['column'];
        $sort_direction = $req->order[0]['dir'];
        $fields = ['ads_campaigns.country', 'ads_campaigns.name', 'ads_campaigns.dailybudget', 'ads_campaigns.state', 'ads_campaigns.id as id', 'ads_campaigns.targetingtype as type'];

    	// $camps = AdsCampaign::where('seller_id', $seller_id)
    	// 	->skip($skip)->take($take)->orderBy($fields[$sort_column], $sort_direction)->get();
        $camps = DB::connection('mysql2')->table('ads_campaigns')
            ->where('ads_campaigns.seller_id', $seller_id)
            ->select($fields)
            ->skip($skip)->take($take)->orderBy($fields[$sort_column], $sort_direction)->get();

    	$count = AdsCampaign::where('seller_id', $seller_id)->get()->count();

    	$res['draw'] = $draw;
    	$res['recordsTotal'] = $count;
    	$res['recordsFiltered'] = $count;
        $icon = '<span class="row-details row-details-close toggleCampaignDetails m-r-10" data-withdata="0"></span>';
    	$data = array();
    	foreach ($camps as $camp) {
            $default_camp_class = 'camp_auto';
            $camp_type_icon = 'A';

            if ($camp->type == 'manual') {
                $default_camp_class = 'camp_manual';
                $camp_type_icon = 'M';
            }

            $flag_icon = $camp->country;
            if ($camp->country == 'uk') {
                $flag_icon = 'gb';
            }

    		$arr = array();
            $arr['rowId'] = $camp->id;
            $arr['DT_RowId'] = $camp->id;
            //$arr[] = $icon;
            $arr[] = '<p class="text-center countryFlag" data-tipso-title="" data-tipso="Country: '.strtoupper($camp->country).'"><img src="'.url('assets/img/countries_flags').'/'.$flag_icon.'.png"></p>';
            $arr[] = $icon.' <span data-tipso-title="" data-tipso="Campaign Type: '.strtoupper($camp->type).' " class="campTypeIcon '.$default_camp_class.' m-r-10" style="margin-right:15px;">'.$camp_type_icon.'</span>';

            $arr[] = '<span class="camp_name color-blue">'.$camp->name.
                    ' <span style="display:none" class="camp_name_pencilIcon'.$camp->id.'"><i class="fa fa-pencil"></i></span></span>';

            $arr[] = '<span class="camp_dailybid">'.$camp->dailybudget.
                    ' <span style="display:none" class="camp_dailybid_pencilIcon'.$camp->id.'"><i class="fa fa-pencil"></i></span></span>';

            $arr[] = '<span class="camp_status">'.$camp->state.
                    ' <span style="display:none" class="camp_status_pencilIcon'.$camp->id.'"><i class="fa fa-pencil"></i></span></span>';'</spam>';
            $data[] = $arr;
    	}
    	$res['data'] = $data;
    	echo json_encode($res);
    }

    public function updateCampaignValue(Request $req){
        $q = AdsCampaign::where('id', $req->id);
        $data = array();
        switch ($req->forQuery) {
            case 'camp_name':
                $valid = $this->validateSpecialCharacters($req->new_name);
                if($valid !== false){
                    return  response()
                        ->json([
                            'status' => 'error',
                            'message' => "Some characters ".$valid." in the campaign name are not allowed!",
                            'new_name' => $req->new_name
                        ]);
                }
                $q = $q->update(['name'=>$req->new_name]);   
                $data = ['name'=>$req->new_name]; 
                break;
            
            case 'dailybid':
                $q = $q->update(['dailybudget'=>$req->new_name]);
                $data = ['dailyBudget'=>$req->new_name]; 
                break;

            case 'status':
                $q = $q->update(['state'=>$req->new_name]);
                $data = ['state'=>$req->new_name]; 
                break;
        }

        $c = AdsCampaign::where('id', $req->id)->get()->first();
        $data['campaignId'] = (int)$c->campaignid;
        $country = $c->country;
        $seller_id = Auth::user()->seller_id;
        $this->get_seller_token($seller_id);
        $prof = Amz::where('seller_id', $seller_id)->where('amz_country_code', strtoupper($country))->get()->first();
        $access_token = $prof->amz_access_token;
        $profile_id = $prof->amz_profile_id;
        $mkp = config('constant.country_mkp.'.strtolower($country));

        $amz = new MWSCurl;
        $ret = $amz->update_campaign($access_token, $profile_id, $mkp,  [$data]);

        return  response()
                ->json([
                    'status' => 'success',
                    'message' => 'success',
                    'new_name' => $req->new_name
                ]);
    }

    public  function updateAdgroupValue(Request $req){
        $q = AdsCampaignAdGroup::where('id', $req->id);
        $data = array();
        switch ($req->forQuery) {
            case 'adgroup_name':
                $valid = $this->validateSpecialCharacters($req->new_name);
                if($valid !== false){
                    $err['message'] = "";
                    return  response()
                        ->json([
                            'status' => 'error',
                            'message' => "Some characters ".$valid." in the adgroup name are not allowed!",
                            'new_name' => $req->new_name
                        ]);
                }
                $q = $q->update(['name'=>$req->new_name]); 
                $data = ['name'=>$req->new_name];   
                break;
            case 'defaultbid':
                $q = $q->update(['defaultbid'=>$req->new_name]); 
                $data = ['defaultBid'=>$req->new_name];   
                break;
            case 'state':
                $q = $q->update(['state'=>$req->new_name]);  
                $data = ['state'=>$req->new_name];  
                break;
        }

        $c = AdsCampaignAdGroup::where('id', $req->id)->get()->first();
        $data['adGroupId'] = (int)$c->adgroupid;
        $country = $c->country;
        $seller_id = Auth::user()->seller_id;
        $this->get_seller_token($seller_id);
        $prof = Amz::where('seller_id', $seller_id)->where('amz_country_code', strtoupper($country))->get()->first();
        $access_token = $prof->amz_access_token;
        $profile_id = $prof->amz_profile_id;
        $mkp = config('constant.country_mkp.'.strtolower($country));

        $amz = new MWSCurl;
        $ret = $amz->update_adGroup($access_token, $profile_id, $mkp,  [$data]);

        return  response()
                ->json([
                    'status' => 'success',
                    'message' => 'success',
                    'new_name' => $req->new_name
                ]);
    }

    public function getCampaignAdGroup(Request $req){
    	$seller_id = Auth::user()->seller_id;
    	$campaignid = $req->id;

        $campaignid = AdsCampaign::where('id', $req->id)->get()->first()->campaignid;

    	$adgroups = AdsCampaignAdGroup::where('seller_id', $seller_id)->where('campaignid', $campaignid)->get();

        $icon = '<span class="row-details row-details-close toggleAdgroupDetails m-r-10" onclick="toggleKeyword(this,'.$req->id.')" data-withdata="0"></span>';
    	$res = array();
    	foreach ($adgroups as $value) {

            $res[] = array(
                    'adgroup_id'    => $value->adgroupid,
                    'icon'          => $icon,
                    'name'          => '<span class="adgroup_name" data-adgroup-id="'.$value->id.'">'.$value->name.
                                        ' <span style="display:none" class="adgroup_pencilIcon'.$value->id.'"><i class="fa fa-pencil"></i></span></span>',

                    'default_bid'   =>  '<span class="adgroup_defaultbid" data-adgroup-id="'.$value->id.'">'.
                                        $value->defaultbid.
                                        ' <span style="display:none" class="adgroup_defaultbid_pencilIcon'.$value->id.'"><i class="fa fa-pencil"></i></span></span>',

                    'state'         => '<span class="adgroup_status" data-adgroup-id="'.$value->id.'">'.$value->state.
                                        ' <span style="display:none" class="adgroup_status_pencilIcon'.$value->id.'"><i class="fa fa-pencil"></i></span></span>',
                );
    	}


        return  response()
                ->json([
                    'data' => $res
                ]);

    }

    public function getSkuByadGroup(Request $req){
        $seller_id = Auth::user()->seller_id;
        $adgroupid = $req->id;

        $q = DB::connection('mysql2')->table('ads_campaign_products')
            ->where('seller_id', $seller_id)
            ->where('adgroupid', $adgroupid)
            ->select('id','sku','state')->get();

        $res = array();

        foreach ($q as $value) {

            $res[] = array(
                    'adgroup_id'  => $adgroupid,
                    'sku'         => $value->sku,
                    'state'       => $value->state,
                );
        }

        return  response()
                ->json([
                    'data' => $res
                ]);

    }

    public function sendCapaignId(Request $req){
        echo view('partials.campaignmanager._adgroup_sku')
                ->with('campaignid',$req->id);
    }

    public function sendAdgroupId(Request $req){
        echo view('partials.campaignmanager._sku')
                ->with('adgroupid',$req->id);
    }

    public function postCampaignData(Request $req){
        $err['error'] = "true";
        // checking for main parametters
        $country = "";
        if(isset($req->country))
            $country = $req->country;
        else{
           $err['message'] = "Country is required!";
            echo json_encode($err);
            die();
        }
        $mkp = config('constant.country_mkp.'.strtolower($country));

        // campaign settings
        // name, campaignType, targetingType, state, dailyBudget and startDate
        $camp['campaignType'] = 'sponsoredProducts';
        $std = isset($req->startDate) ? $req->startDate : "";
        if($std == ""){
            $camp['startDate'] = date('Ymd');
        }else{
            $std = explode('/', $std);
            $camp['startDate'] = $std[2]."".$std[1]."".$std[0];
        }

        $etd = isset($req->startDate) ? $req->startDate : "";
        if($etd == ""){
            // $camp['endDate'] = date('Ymd');
        }else{
            $etd = explode('/', $etd);
            $camp['endDate'] = $etd[2]."".$etd[1]."".$etd[0];
        }

        $camp['state'] = 'enabled';
        // check for name if unset
        if(isset($req->name)){
            $camp['name'] = $req->name;
            $valid = $this->validateSpecialCharacters($camp['name']);
            if( $valid != false ){
               $err['message'] = "Some characters ".$valid." in the campaign name are not allowed!";
                echo json_encode($err);
                die();
            }
        }else{
            $err['message'] = "Campaign Name is required!";
            echo json_encode($err);
            die();
        }
        // check for targetingtype is unset
        // "manual", "auto"
        if(isset($req->campaignType))
            $camp['targetingType'] = strtolower($req->campaignType);
        else{
            $err['message'] = "Campaign Targeting Type is required!";
            echo json_encode($err);
            die();
        }
        if($camp['targetingType'] == 'automatic') $camp['targetingType'] = 'auto';
        // check for dailyBudget if unset
        // "minimum": 1.00
        if(isset($req->dailyBudget)){
            $camp['dailyBudget'] = $req->dailyBudget;
            if( !is_numeric($camp['dailyBudget']) AND !($camp['dailyBudget'] >= 1.00) ){
                $err['message'] = "Campaign Daily Budget must be in minimum of 1.00!";
                echo json_encode($err);
                die();
            }
        }else{
            $err['message'] = "Campaign Daily Budget is required!";
            echo json_encode($err);
            die();
        }
        //Campaign has no empty courses/unset variables
        //adgroup
        if ($req->campaignType == 'Manual') {
            //echo $req->campaignType;
            $c = 0;
            foreach ($req->ad_groupName as $key => $value) {
                $valid = $this->validateSpecialCharacters($value);
                if( $valid != false ){
                   $err['message'] = "Some characters ".$valid." in the adgroup name are not allowed!";
                    echo json_encode($err);
                    die();
                }
                foreach ($req->keyword_datas[$c] as $k) {
                    $valid = $this->validateSpecialCharacters($k['keyword']);
                    if( $valid != false ){
                       $err['message'] = "Some characters ".$valid." in the keywords are not allowed!";
                        echo json_encode($err);
                        die();
                    }
                }
                $c++;
            }
        }

        $seller_id = Auth::user()->seller_id;
        $amz = new MWSCurl;
        $mkp = config('constant.country_mkp.'.strtolower($country));
        $profiles = $this->get_seller_token($seller_id);
        foreach ($profiles as $key => $value) {
            $profs[strtolower($value->amz_country_code)] = $value;
        }

        $country = explode(',', $country);
        $cnty = array();
        foreach ($country as $key => $value) {
            $arr = explode('|', trim($value));
            $cnty[] = trim($arr[0]);
        }
        $country = $cnty;
        foreach ($country as $key => $value) {
            $access_token = $profs[strtolower($value)]->amz_access_token;
            $profile_id = $profs[strtolower($value)]->amz_profile_id;
            $mkp = config('constant.country_mkp.'.strtolower($value));

            //campaign post
            $campaignids = $amz->add_campaign($access_token, $profile_id, $mkp, [$camp]);
            foreach ($campaignids as $camp_id) {
                //adgroup
                if($camp_id->code == "SUCCESS"){
                    $campaignid = $camp_id->campaignId;
                    // save campaign to database
                    $c = new AdsCampaign;
                    $c->name = $camp['name'];
                    $c->targetingtype = $camp['targetingType'];
                    $c->country = $value;
                    $c->seller_id = $seller_id;
                    $c->campaignid = $campaignid;
                    $c->startdate = $camp['startDate'];
                    $c->enddate = $camp['startDate'];
                    $c->creationdate = $camp['startDate'];
                    $c->lastupdateddate = $camp['startDate'];
                    $c->state = 'enabled';
                    $c->servingstatus = 'CAMPAIGN_STATUS_ENABLED';
                    $c->campaigntype = $camp['campaignType'];
                    $c->dailybudget = $camp['dailyBudget'];
                    $c->premiumBidAdjustment = 0;
                    $c->save();

                    $adGroups = $req->ad_groupName;
                    $adGroupDBid = $req->defaultBid;
                    $adGroup_arr = array();
                    for($x = 0; $x < count($adGroups); $x++){
                        $adgs = array();
                        $adgs['name'] = $adGroups[$x];
                        $adgs['defaultBid'] = $adGroupDBid[$x];
                        $adgs['campaignId'] = $campaignid;
                        $adgs['state'] = 'enabled';
                        $adGroup_arr[] = $adgs;
                    }
                    if(count($adGroup_arr) > 0)
                        $adg = $amz->add_adGroup($access_token, $profile_id, $mkp, $adGroup_arr);
                    else
                        $adg = array();

                    //save ads to database
                    for($x=0; $x < count($adg); $x++){
                        $adgroupid = $adg[$x]->adGroupId;
                        $a = new AdsCampaignAdGroup;
                        $a->seller_id = $seller_id;
                        $a->country = $value;
                        $a->campaignid = $campaignid;
                        $a->adgroupid = $adgroupid;
                        $a->name = $adGroup_arr[$x]['name'];
                        $a->defaultbid = $adGroup_arr[$x]['defaultBid'];
                        $a->state = $adGroup_arr[$x]['state'];
                        $a->suggestedBid = 0;
                        $a->rangeStart = 0;
                        $a->rangeEnd = 0;
                        $a->error = 0;
                        $a->creationdate = date('Y-m-d H:i:s');
                        $a->lastupdateddate = date('Y-m-d H:i:s');
                        $a->servingstatus = 'CAMPAIGN_STATUS_ENABLED';
                        $a->save();
                    }

                    //product ad
                    if(count($adg) > 0){
                        $skus = explode(',', $req->sku);
                        $asin = array();
                        $prod_ads = array();
                        $prods = array();
                        for($x = 0; $x < count($skus); $x++){
                            $flag_sku = Product::where('sku', $skus[$x])->where('country', strtolower($value))->get()->first();
                            if(count($flag_sku) > 0){
                                $asin[] = $flag_sku->asin;
                                for($x = 0; $x < count($adg); $x++){
                                    $arr = array();
                                    $arr['campaignId'] = $campaignid;
                                    $arr['adGroupId'] = $adg[$x]->adGroupId;
                                    $arr['state'] = 'enabled';
                                    $arr['sku'] = $skus[$x];
                                    $prod_ads[] = $arr;
                                }
                            }
                        }

                        if(count($prod_ads) > 0)
                            $prods = $amz->add_productAds($access_token, $profile_id, $mkp, $prod_ads);
                        
                        //saving product ads to database
                        for($x=0; $x < count($prods); $x++){
                            $adid = $prods[$x]->adId;
                            $p = new AdsCampaignProduct;
                            $p->seller_id = $seller_id;
                            $p->country = $value;
                            $p->campaignid = $campaignid;
                            $p->adgroupid = $prod_ads[$x]['adGroupId'];
                            $p->adid = $adid;
                            $p->sku = $prod_ads[$x]['sku'];
                            $p->asin = $asin[$x];
                            $p->state = 'enabled';
                            $p->creationdate = date('Y-m-d H:i:s');
                            $p->lastupdateddate = date('Y-m-d H:i:s');
                            $p->servingstatus = "CAMPAIGN_STATUS_ENABLED";
                            $p->save();
                        }
                    }

                    //keywords
                    if($camp['targetingType'] == 'manual'){
                        $keywords_arr = array();
                        $keywords_bid_arr = array();
                        if(count($adg) > 0){
                            for($x = 0; $x < count($adg); $x++){
                                foreach ($req->keyword_datas[$x] as $keys) {
                                    $arr = array();
                                    $arr['campaignId'] = $campaignid;
                                    $arr['adGroupId'] = $adg[$x]->adGroupId;
                                    $arr['state'] = 'enabled';
                                    $arr['keywordText'] = $keys['keyword'];
                                    $arr['matchType'] = strtolower($keys['match_type']);
                                    $keywords_arr[] = $arr;
                                    $keywords_bid_arr[] = $keys['default_bid'];
                                }
                            }
                        }
                        if(count($keywords_arr) > 0)
                            $ret_keywords = $amz->add_keywords($access_token, $profile_id, $mkp, $keywords_arr);
                        else
                            $ret_keywords = array();
                        if(count($ret_keywords) > 0){
                            $counter = 0;
                            $keyword_bids = array();
                            foreach ($ret_keywords as $keyss) {
                                $arr = array();
                                $arr['keywordId'] = $keyss->keywordId;
                                $arr['bid'] = $keywords_bid_arr[$counter];
                                $counter++;
                                $keyword_bids[] = $arr;
                            }
                            if(count($keyword_bids) > 0)
                                $amz->update_keywords($access_token, $profile_id, $mkp, $keyword_bids);

                            //saving keywords to database
                            for ($x = 0; $x< count($ret_keywords); $x++) {
                                $k = new AdsCampaignKeyword;
                                $k->seller_id = $seller_id;
                                $k->country = $value;
                                $k->campaignid = $campaignid;
                                $k->adgroupid = $keywords_arr[$x]['adGroupId'];
                                $k->keywordid = $ret_keywords[$x]->keywordId;
                                $k->state = 'enabled';
                                $k->keywordtext = $keywords_arr[$x]['keywordText'];
                                $k->matchtype = $keywords_arr[$x]['matchType'];
                                $k->creationdate = date('Y-m-d H:i:s');
                                $k->lastupdateddate = date('Y-m-d H:i:s');
                                $k->servingstatus = "CAMPAIGN_STATUS_ENABLED";
                                $k->bid = $keywords_bid_arr[$x];
                                $k->suggestedBid = 0;
                                $k->rangeStart = 0;
                                $k->rangeEnd = 0;
                                $k->error = 0;
                                $k->save();
                            }
                        }
                    }
                }

            }
        }

    }

    public function getCampaignLinkRecommendation(Request $req){
        $campaignId = $req->campaignid;
        $camp_adGroups = AdsCampaignAdGroup::where('campaignid', $campaignId)->get();
        $camp_keywords = AdsCampaignKeyword::where('campaignid', $campaignId)->orderBy('campaignid')->get();
        echo json_encode(['adgroups'=>$camp_adGroups, 'keywords'=>$camp_keywords]);
    }


    public function validateSpecialCharacters($string){
        $not_allowed = '£€¬ÃÅÐÕØÌÒÝãäåìòõøýĀāĂăĄąĆćĈĉĊċČčĎďĐđĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĹĺĻļĽĺĻļĽľĿŀŁłŃńŅņŇňŉŌōŎŏŐőŒŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŹźŻżŽžſƒƠơƯưǍǎǏǐǑǒǓǔǕǖǗǘǙǚǛǜǺǻǼǽǾǿΆάΈέΌόΏώΊίϊΐΎύϋΰΉή';

    	$ret = strpbrk($string, $not_allowed);
        return $ret;
    }

    public function test_add_campaign(){
        $seller_id = Auth::user()->seller_id;
        $amz = new MWSCurl;
        $country = 'uk';
        $mkp = config('constant.country_mkp.'.strtolower($country));
        $profile = $this->is_country_token_expired($country, $seller_id, $mkp);
        $access_token = $profile->amz_access_token;
        $profile_id = $profile->amz_profile_id;
        $t = 'ž ſ ƒ Ơ ơ Ư ư Ǎ ǎ Ǐ ǐ Ǒ ǒ Ǔ ǔ Ǖ ǖ Ǘ ǘ Ǚ ǚ Ǜ ǜ Ǻ ǻ Ǽ ǽ Ǿ ǿ Ά ά Έ έ Ό ό Ώ ώ Ί ί ϊ ΐ Ύ ύ ϋ ΰ Ή ή';
        $t = explode(' ', $t);

        //sample data for add new keyword
        // not allowed
        // £ € ¬ Ã Å Ð Õ Ø Ì Ò Ý ã ä å ì ò õ ø ý Ā ā Ă ă Ą ą Ć ć Ĉ ĉ Ċ ċ Č č Ď ď Đ đ ĕ Ė ė Ę ę Ě ě Ĝ ĝ Ğ ğ Ġ ġ Ģ ģ Ĥ ĥ Ħ ħ Ĩ ĩ Ī ī Ĭ ĭ Į į İ ı Ĳ ĳ Ĵ ĵ Ķ ķ Ĺ ĺ Ļ ļ Ľ ĺ Ļ ļ Ľ ľ Ŀ ŀ Ł ł Ń ń Ņ ņ Ň ň ŉ Ō ō Ŏ ŏ Ő ő Œ Ŕ ŕ Ŗ ŗ Ř ř Ś ś Ŝ ŝ Ş ş Š š Ţ ţ Ť ť Ŧ ŧ Ũ ũ Ū ū Ŭ ŭ Ů ů Ű ű Ų ų Ŵ ŵ Ŷ ŷ Ź ź Ż ż Ž ž ſ ƒ Ơ ơ Ư ư Ǎ ǎ Ǐ ǐ Ǒ ǒ Ǔ ǔ Ǖ ǖ Ǘ ǘ Ǚ ǚ Ǜ ǜ Ǻ ǻ Ǽ ǽ Ǿ ǿ Ά ά Έ έ Ό ό Ώ ώ Ί ί ϊ ΐ Ύ ύ ϋ ΰ Ή ή
        // allowed
        // #!-$/*+~><\|%^&()[]{}@':;"`?
        //$data[] = ['campaignId'=>83154349539909,'name'=>'Tool Test - Auto Manual', 'state'=>'paused'];
        $data[] = ['name'=>'Test Manual Campaign Ferz','campaignType'=>'sponsoredProducts', 'targetingType'=>'manual', 'state'=>'paused', 'dailyBudget'=>'1.00', 'startDate'=>'20170801'];
        $result = $amz->add_campaign($access_token, $profile_id, $mkp, $data);
        print_r($result);


    }

    private function is_country_token_expired($country, $seller_id, $mkp){
        $amz = new MWSCurl;
        if($mkp == 'eu') $mkp = 2;
        else $mkp = 1;
        $profile = Amz::where('seller_id', $seller_id)
            ->where('mkp_id', $mkp)->where('amz_country_code', strtoupper($country))
            ->get()->first();
        $current = date_create(Carbon::now());
        $expiry_date = date_create($profile->amz_expires_in);
        $refresh_token = $profile->amz_refresh_token;
        if($current >= $expiry_date){
            echo "Access token is expired. Requesting new access token...... ";
            $tokens = $amz->refresh_tokens($refresh_token);
            $data = ['amz_access_token'=>$tokens->access_token,
                  'amz_expires_in'=>Carbon::now()->addHour(),
                  'updated_at'=>Carbon::now(),
                  'amz_refresh_token'=>$tokens->refresh_token];
            $update = Amz::where('seller_id', $seller_id)->update($data);
            echo "<b>DONE!!</b><br>";
            $profile = Amz::where('seller_id', $seller_id)->where('mkp_id', $mkp)->where('amz_country_code', $country)->get()->first();
        }
        return $profile;
    }

    private function get_seller_token($seller_id){
        $amz = new MWSCurl;
        $profile = Amz::where('seller_id', $seller_id)->get()->first();
        $tokens = $amz->refresh_tokens($profile->amz_refresh_token);
        $data = ['amz_access_token'=>$tokens->access_token,
              'amz_expires_in'=>Carbon::now()->addHour(),
              'updated_at'=>Carbon::now(),
              'amz_refresh_token'=>$tokens->refresh_token];
        $update = Amz::where('seller_id', $seller_id)->update($data);
        $profiles = Amz::where('seller_id', $seller_id)->get();
        return $profiles;
    }

    public function postCampaignData2(Request $req){
        dd($req->all());
    }
    public function get_country_sku(Request $req){
        $seller_id = Auth::user()->seller_id;
        $country = array();
        $country = explode(',', $req->country);
        foreach($country as $nt) $country[] = strtolower($nt);

        $skus = Product::where('seller_id', $seller_id)
                ->where('price', '>', 0)
                ->whereIn('country', $country)->get();
        $data = array();
        foreach ($skus as $key => $value) {
            $data[] = $value->sku;
        }
        echo json_encode($data);
    }

    public function get_campaign_list_is_link(Request $req){
        // campaignType ni sya sa iyang e link.
        // kong mag link sya from auto to manual dapat manual ang sulod ana nga variable
        $campaignType = $req->campaignType;
        $seller_id = Auth::user()->seller_id;

        $camp = AdsCampaign::where('seller_id', $seller_id)->where('targetingtype', $campaignType)->get();
        $data = array();
        foreach ($camp as $key => $value) {
            $data[$value->id] = $value->name;
        }
        echo json_encode($data);
    }
}
