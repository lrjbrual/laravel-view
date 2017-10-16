<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Trendle\PnLController;
use Countries;
use App\CampaignAdvertisingFilter;
use App\CampaignAdvertising;
use App\UniversalModel;
use Auth;
use App\CampaignAdsRecommendation;
use App\CampaignAdsRecommendationCondition;
use Illuminate\Support\Facades\DB;
use File;
use App\CampaignAdsBid;
use App\CampaignAdEntityReport;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;
use App\AmazonSellerDetail as Amz;
use App\MWSCustomClasses\MWSCurlAdvertisingClass as MWSCurl;
use App\AdsCampaignKeyword;
use Carbon\Carbon;
use App\AmazonSellerDetail;
use App\AdsCampaignAdGroup;
use App\AdsCampaign;
use App\Http\Helpers\HelpersFacade;


class AdsPerformanceController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('checkStripe');
    }

    public function index(){
        ini_set('memory_limit', '1110M');
        ini_set("max_execution_time", 0);  // on
        $seller_id = Auth::user()->seller_id;

        $data = $this->callBaseSubscriptionName($seller_id);
        if ($data->base_subscription == '' && Auth::user()->seller->is_trial == 0) {
            return redirect('subscription');
        }
        $seller_id = Auth::user()->seller_id;
        /*  Added by jason 7/17/17
         *  Redirect if user is not logged in with amazon
        */
        $amz = new Amz;
        $amzChecker = 0;
        $amzChecker = $amz->where('seller_id', $seller_id)->first();
        $amzChecker = 1;
        if (!$amzChecker) {
            return view('trendle.adsperformance.index')
                ->with('bs',$data->base_subscription)
                ->with('amzChecker', $amzChecker);
        }
        $pnlcont = new PnLController;
        $countries = $pnlcont->getCountryListForThisSeller();
        $country = array();
        $countryRec = array();
        foreach ($countries as $key => $value) {
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
        ksort($country);
        $countries = array();
        $countries = $country;
        ksort($countryRec);
        $countriesRec = array();
        $countriesRec = $countryRec;

        // $query = new AdsCampaign;
        // $camp_name_q = $query->where('seller_id', $seller_id)
        //     ->where('state', 'enabled')
        //     ->select('name')
        //     ->orderBy('name')
        //     ->get();

        $c = array();
        // foreach ($camp_name_q as $key => $value) {
        //     $c[$value->name] = $value->name;
        // }
        $camp_name = $c;
        // $c1 = array();
        // foreach ($camp_name_q as $key => $value) {
        //     $c1[$value->campaign_name] = $value->campaign_name;
        // }
        // $camp_name1 = $c1;

        // $query = new AdsCampaignAdGroup;
        $a = array();
        // $ad_group_name = $query->where('seller_id', $seller_id)
        //     ->where('state', 'enabled')
        //     ->select('name')
        //     ->orderBy('name')
        //     ->get();

        // foreach ($ad_group_name as $key => $value) {
        //     $a[$value->name] = $value->name;
        // }
        $ad_group_name = $a;


        return view('trendle.adsperformance.index')
                ->with('countries', $countries)
                ->with('countriesRec', $countriesRec)
                ->with('camp_name', $camp_name)
                // ->with('camp_name1', $camp_name1)
                ->with('ad_group_name', $ad_group_name)
                ->with('bs',$data->base_subscription)
                ->with('amzChecker', $amzChecker);
    }

    public function getCampaignAdgroupData(){
        $seller_id = Auth::user()->seller_id;

        $response = new HelpersFacade;
        $response = $response->getCampaignAdgroupDataForFilter($seller_id);
        echo json_encode($response);
    }

    public function getAdData(Request $req){
        ini_set('memory_limit', '180M');
        ini_set("max_execution_time", 0);  // on
        $seller_id = Auth::user()->seller_id;
        $filters['seller_id'] = $seller_id;
        //echo $req->start;
        $draw = $req->draw;
        $filters['skip'] = $req->start;
        $filters['take'] = $req->length;
        $filters['sort_column'] = $req->order[0]['column'];
        $filters['sort_direction'] = $req->order[0]['dir'];
        $filters['filter_date_start'] = (!isset($req->filter_date_start)) ? " " : $req->filter_date_start;
        $filters['filter_date_end'] = (!isset($req->filter_date_end)) ? " " : $req->filter_date_end;
        $filters['filter_imp'] = (!isset($req->filter_imp)) ? " " : $req->filter_imp;
        $filters['filter_clicks'] = (!isset($req->filter_clicks)) ? " " : $req->filter_clicks;
        $filters['filter_ctr'] = (!isset($req->filter_ctr)) ? " " : $req->filter_ctr;
        $filters['filter_total_spend'] = (!isset($req->filter_total_spend)) ? " " : $req->filter_total_spend;
        $filters['filter_avg_cpc'] = (!isset($req->filter_avg_cpc)) ? " " : $req->filter_avg_cpc;
        $filters['filter_acos'] = (!isset($req->filter_acos)) ? " " : $req->filter_acos;
        $filters['filter_conv_rate'] = (!isset($req->filter_conv_rate)) ? " " : $req->filter_conv_rate;
        $filters['filter_revenue'] = (!isset($req->filter_revenue)) ? " " : $req->filter_revenue;
        $filters['filter_country'] = (!isset($req->filter_country)) ? " " : $req->filter_country;
        $filters['filter_camp_type'] = (!isset($req->filter_camp_type)) ? " " : $req->filter_camp_type;
        $filters['filter_camp_name'] = (!isset($req->filter_camp_name)) ? " " : $req->filter_camp_name;
        $filters['filter_ad_group'] = (!isset($req->filter_ad_group)) ? " " : $req->filter_ad_group;
        $filters['filter_keyword'] = (!isset($req->filter_keyword)) ? " " : $req->filter_keyword;
        $filters['filter_recommendation'] = (!isset($req->filter_recommendation)) ? " " : $req->filter_recommendation;
        $filters['filter_show_enabled'] = (!isset($req->filter_show_enabled)) ? " " : $req->filter_show_enabled;
        $rec_filter = "";
        $rec_filter = trim(trim($rec_filter), ",");
        $rec_filter = explode(',', $rec_filter);
        $total_number = (!isset($req->total_number)) ? "" : $req->total_number;

        if($filters['filter_date_end'] != " ")
            $filters['filter_date_end'] = date('Y-m-d',strtotime(str_replace('/', '-', $filters['filter_date_end'])));
        else
            $filters['filter_date_end'] = date('Y-m-d');

        if($filters['filter_date_start'] != " ")
            $filters['filter_date_start'] = date('Y-m-d',strtotime(str_replace('/', '-', $filters['filter_date_start'])));
        else{
            $filters['filter_date_start'] = date('Y-m-d', strtotime('-30 days'.$filters['filter_date_end']));
        }

        $campaigns = new CampaignAdvertising;
        if($total_number == "")
            $total_number = $campaigns->getFilteredData($filters, true);
        $campaigns = $campaigns->getFilteredData($filters);

        $data = array();
        // $car = CampaignAdsRecommendation::where('seller_id', $seller_id)
        //         ->where('is_active', 1)
        //         ->get();
        // $car_arr = array();
        // foreach ($car as $key => $value) {
        //     $car_arr[$value->id] = $value;
        // }

        //Altsi - for issue #342
        $cab = CampaignAdsBid::where('seller_id', $seller_id)
               ->get();

        $now = Carbon::now();
        $array_cab = array();
        if(isset($cab))
        {
            foreach($cab as $c)
            {
                if($c->is_uploaded == 1)
                {
                    $diff = ($now)->diffInDays($c->updated_at);
                    if($diff <= 10)
                    {
                        $array_cab[] = $c->campaign_ads_id;
                    }
                }
            }
        }
        //

        foreach ($campaigns as $camp) {

            $orig_matchType = $camp->match_type;
            $camp->match_type = $camp->match_type != '' ? $camp->match_type : 'Click to edit';

            // $recommendation = '';
            // if ($camp->recommendation != '' || $camp->recommendation != null) {
            //     $str = $camp->recommendation;
            //     $str = str_replace('-', ' ', $str);
            //     $str = explode(' ', trim($str));
            //     foreach ($str as $key => $value) {
            //         if(array_key_exists($value, $car_arr)){
            //             $recommendation .= $car_arr[$value]->recommendation.', ';
            //         }
            //         // $car = CampaignAdsRecommendation::find($value);
            //         // if (isset($car)) {
            //         //     if ($car->is_active == 1) {
            //         //         $recommendation .= $car->recommendation.', ';
            //         //     }
            //         // }
            //     }
            // }
            // $recommendation = rtrim($recommendation,", ");

            // $rec_arr = explode(', ', $recommendation);
            // $flag = $this->check_arr2_consist_val_arr1($rec_filter, $rec_arr);
            $icon = '<span class="row-details row-details-close toggleCampaignDetails m-r-10" data-withdata="0"></span>';
            // if($flag){
                $default_camp_class = 'camp_auto';
                $camp_type_icon = 'A';

                $flag_icon = $camp->country;
                if ($camp->country == 'uk') {
                    $flag_icon = 'gb';
                }

                $arr = array();
                $arr['rowId'] = $camp->campaignid;
                $arr['DT_RowId'] = $camp->campaignid;

                $arr[] = '<p class="text-center countryFlag" data-tipso-title="" data-tipso="Country: '.strtoupper($camp->country).'"><img src="'.url('assets/img/countries_flags').'/'.$camp->country.'.png"></p>';

                // $arr[] = $camp->type;

                if (strtoupper($camp->type) == 'MANUAL') {
                    $default_camp_class = 'camp_manual';
                    $camp_type_icon = 'M';
                }

                $arr[] = $icon.'<span data-tipso-title="" data-tipso="Campaign Type: '.strtoupper($camp->type).' " class="campTypeIcon '.$default_camp_class.' m-r-10" style="margin-right:10px;">'.$camp_type_icon.'</span>';
                        // '<i class="fa fa-flag color-blue" data-toggle="tooltip" title="'.$camp->type.'"></i>'.
                        //  '<input type="hidden" value="'.$camp->bid.'">';

                $arr[] = '<span class="color-blue">'. $camp->campaign_name.'</span>';
                // $arr[] = $camp->ad_group_name;
                // $arr[] = $camp->keyword;
                // $arr[] = $camp->customer_search_term;
                // $arr[] = '<span class="matchType">'.$camp->match_type.'</span>'.'<input type="hidden" class="matchTypeOrig" value="'.$orig_matchType.'">';
                $arr[] = ($camp->impressions == null) ? 0 : $camp->impressions;
                $arr[] = ($camp->clicks == null) ? 0 : $camp->clicks;
                $arr[] = $camp->impressions == 0 ? 0 : round(($camp->clicks/$camp->impressions)*100,2)."%";
                $arr[] = round($camp->attributedsales30d,2);
                $arr[] = ($camp->attributedconversions30dsamesku == null) ? 0 : $camp->attributedconversions30dsamesku;
                $arr[] = ($camp->clicks == 0) ? 0 : round((($camp->attributedconversions30dsamesku/$camp->clicks)*100),2).'%';
                //$arr[] = round($camp->cr,2)."%";
                $arr[] = round($camp->total_spend, 2);
                //$arr[] = round($camp->average_cpc, 2);
                $arr[] = ($camp->total_spend == 0 || $camp->clicks == 0) ? 0 : round(($camp->total_spend/$camp->clicks),2);
                $arr[] = ($camp->total_spend == 0 || $camp->attributedsales30d == 0) ? 0 : round(($camp->total_spend/$camp->attributedsales30d)*100,2)."%";
                $arr[] = '-'; // bid column
                $arr[] = '-'; // max bid recommendation column
                $arr[] = '-'; // recommendation column
                // $arr[] = round($camp->max_bid_recommendation,2);
                // $arr[] = $recommendation;
                // $arr[] = $camp->comment;
                $data[] = $arr;
            // }
        }
        $data2['draw'] = $draw;
        $data2['recordsTotal'] = $total_number;
        $data2['recordsFiltered'] = $total_number;
        $data2['data'] = $data;

        echo json_encode($data2);
    }

    public function sendCampaignId(Request $req){
        echo view('partials.adsperformance._adgroup')
                ->with('campaignid',$req->id)
                ->with('isFilter',$req->isFilter);
    }

    public function performance_adgroup(Request $req){
        $campaigns_model = new CampaignAdvertising;

        $seller_id = Auth::user()->seller_id;
        $filters['seller_id'] = $seller_id;
        $filters['filter_date_start'] = (!isset($req->filter_date_start)) ? " " : $req->filter_date_start;
        $filters['filter_date_end'] = (!isset($req->filter_date_end)) ? " " : $req->filter_date_end;
        $filters['filter_imp'] = (!isset($req->filter_imp)) ? " " : $req->filter_imp;
        $filters['filter_clicks'] = (!isset($req->filter_clicks)) ? " " : $req->filter_clicks;
        $filters['filter_ctr'] = (!isset($req->filter_ctr)) ? " " : $req->filter_ctr;
        $filters['filter_total_spend'] = (!isset($req->filter_total_spend)) ? " " : $req->filter_total_spend;
        $filters['filter_avg_cpc'] = (!isset($req->filter_avg_cpc)) ? " " : $req->filter_avg_cpc;
        $filters['filter_acos'] = (!isset($req->filter_acos)) ? " " : $req->filter_acos;
        $filters['filter_conv_rate'] = (!isset($req->filter_conv_rate)) ? " " : $req->filter_conv_rate;
        $filters['filter_revenue'] = (!isset($req->filter_revenue)) ? " " : $req->filter_revenue;
        $filters['filter_country'] = (!isset($req->filter_country)) ? " " : $req->filter_country;
        $filters['filter_camp_type'] = (!isset($req->filter_camp_type)) ? " " : $req->filter_camp_type;
        $filters['filter_camp_name'] = (!isset($req->filter_camp_name)) ? " " : $req->filter_camp_name;
        $filters['filter_ad_group'] = (!isset($req->filter_ad_group)) ? " " : $req->filter_ad_group;
        $filters['filter_keyword'] = (!isset($req->filter_keyword)) ? " " : $req->filter_keyword;
        $filters['filter_show_enabled'] = (!isset($req->filter_show_enabled)) ? " " : $req->filter_show_enabled;
        $filters['filter_recommendation'] = (!isset($req->filter_recommendation)) ? " " : $req->filter_recommendation;

        if($filters['filter_date_end'] != " ")
            $filters['filter_date_end'] = date('Y-m-d',strtotime(str_replace('/', '-', $filters['filter_date_end'])));
        else
            $filters['filter_date_end'] = date('Y-m-d');

        if($filters['filter_date_start'] != " ")
            $filters['filter_date_start'] = date('Y-m-d',strtotime(str_replace('/', '-', $filters['filter_date_start'])));
        else{
            $filters['filter_date_start'] = date('Y-m-d', strtotime('-30 days'.$filters['filter_date_end']));
        }

        $adgroups = $campaigns_model->getGroupByadgroup($filters,$req);

        $icon = '<span class="row-details row-details-close toggleKeywordDetails m-r-10" onclick="toggleKeyword(this,\'keyword\')"></span>';

        $match_type = '';
        if(!isset($req->forQuery)){
            $req->forQuery = '';
            $match_type = AdsCampaign::where('campaignid', $req->id)->get()->first()->targetingtype;
        }else{
            if($req->forQuery == 'keyword'){
                // $match_type = DB::connection('mysql2')->table('ads_campaign_ad_groups')
                // ->select(DB::raw('ads_campaigns.targetingtype as targetingtype'))
                // ->join('ads_campaigns', 'ads_campaigns.campaignid', '=', 'ads_campaign_ad_groups.campaignid')
                // ->where('ads_campaign_ad_groups.adgroupid', $req->overRideId)
                // ->get()->first();
                // $match_type = !(isset($match_type)) ? "" : $match_type->targetingtype;
                $match_type = AdsCampaign::where('campaignid', $req->id)->get()->first()->targetingtype;
            }
        }
        $res = ['data'=>array(),
            'forQuery' => $req->forQuery,
            'parentid' => $req->id];

        $now = Carbon::now();
        $minus10days = Carbon::today()->subDays(10);
        $cab = CampaignAdsBid::where('seller_id', $seller_id)->where('is_uploaded', 1)
            ->where('updated_at', '>=', $minus10days)->orderBy('updated_at', 'asc')
            ->get();

        $array_cab = array();
        $array_cab['adgroupid'] = array();
        $array_cab['keywordid'] = array();
        $array_cab['k'] = array();
        $array_cab['adg'] = array();
        if(isset($cab))
        {
            foreach($cab as $c)
            {
                if($c->is_uploaded == 1)
                {
                    $diff = ($now)->diffInDays($c->updated_at);
                    if($diff <= 10)
                    {
                        $array_cab[] = [ 'id'=>$c->id, 'keywordid'=>$c->keywordid, 'adgroupid'=>$c->adgroupid ];
                        $array_cab['adgroupid'][] = $c->adgroupid;
                        $array_cab['keywordid'][] = $c->keywordid;
                        $array_cab['k'][$c->keywordid] = $c->id;
                        $array_cab['adg'][$c->adgroupid] = $c->id;
                    }
                }
            }
        }

        foreach ($adgroups as $value) {
            $bid = '-';
            $max_bid = '-';
            $recommendation = '-';
            $warningIcon = '';
            if($req->forQuery==''){
                $recommendation = $value->recommendation == '' ? 'No Recommendation' : $value->recommendation;
                if(strtoupper($match_type) == 'AUTO'){
                    $bid = '<span onclick="updateAdsBidV2(this)" data-key-id="" data-adg-id="'.$value->id.'" data-row_id="'.$value->row_id.'">'.round($value->bid,2).'</span>';
                    $max_bid = round($value->max_bid_recommendation,2);

                    if(in_array($value->id,$array_cab['adgroupid']))
                    {
                        $warningIcon = '<i class="fa fa-warning orange_color hand warningChangesPopUp" data-id="'.$array_cab['adg'][$value->id].'" style="font-size:14px"></i>';
                    }
                }
            }else{
                if($req->forQuery == 'keyword'){
                    if(strtoupper($match_type) == 'MANUAL'){
                        if($value->bid <= 0) $value->bid = $req->data_default_bid;
                        $bid = '<span onclick="updateAdsBidV2(this)" data-adg-id="" data-key-id="'.$value->id.'" data-row_id="'.$value->row_id.'">'.round($value->bid,2).'</span>';
                        $max_bid = round($value->max_bid_recommendation,2);
                        $recommendation = $value->recommendation == '' ? 'No Recommendation' : $value->recommendation;
                    }

                    if(in_array($value->id,$array_cab['keywordid']))
                    {
                        $warningIcon = '<i class="fa fa-warning orange_color hand warningChangesPopUp" data-id="'.$array_cab['k'][$value->id].'" style="font-size:14px"></i>';
                    }
                }

            }

            $recommendation = trim($recommendation,',');
            $recommendation = explode(',', $recommendation);
            $recommendation = array_unique($recommendation);
            sort($recommendation);
            $recommendation = implode(", ", $recommendation);

            $res['data'][] = array(
                    'warningIcon' => $warningIcon,
                    'forQuery' => $req->forQuery,
                    'parentid' =>$req->id,
                    'id' => $value->id,
                    'match_type' => strtoupper($value->match_type),
                    'icon' => $icon,
                    'rowtitle' => $value->rowtitle,
                    'imp' => ($value->impressions == null) ? 0 : $value->impressions,
                    'clicks' => ($value->clicks == null) ? 0 : $value->clicks,
                    'ctr' => $value->impressions == 0 || $value->clicks == 0 ? 0 : round(($value->clicks/$value->impressions)*100,2)."%",
                    'rev' => round($value->attributedsales30d,2),
                    'orders' => ($value->attributedconversions30dsamesku == null) ? 0 : $value->attributedconversions30dsamesku,
                    //'cr' => round($value->cr,2)."%",
                    'cr' => $value->attributedconversions30dsamesku == 0 || $value->clicks == 0 ? 0 : round(($value->attributedconversions30dsamesku/$value->clicks)*100,2)."%",
                    'total_spend' => round($value->total_spend, 2),
                    'average_cpc' => ($value->total_spend == 0 || $value->clicks == 0) ? 0 : round(($value->total_spend/$value->clicks),2),
                    //'average_cpc' => round($value->average_cpc, 2),
                    'acos' => $value->total_spend == 0 || $value->attributedsales30d == 0 ? 0 : round(($value->total_spend/$value->attributedsales30d)*100,2)."%",
                    'bid' => $bid,
                    'defaultbid' => $value->bid,
                    'max_bid' => $max_bid,
                    'recommendation'=>$recommendation
                );
        }

        echo json_encode($res);
    }

    public function getChangeBid(Request $req){
        $seller_id = Auth::user()->seller_id;

        $c_bid = CampaignAdsBid::where('id', $req->id)->get()->first();
        $campaignid = $c_bid->campaignid;
        $adgroupid = $c_bid->adgroupid;
        $keywordid = $c_bid->keywordid;
        $date_updated = date_create($c_bid->updated_at);

        $c = AdsCampaign::where('id', $req->id)->get()->first();
        $targetingtype = $c->targetingtype;

        $fields = [
            DB::raw('sum(clicks) as clicks'),
            DB::raw('sum(impressions) as impressions'),
            DB::raw('if(sum(campaign_advertisings.attributedsales30d) > 0, (sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.attributedsales30d))*100,0) as acos')
        ];
        if(strtolower($targetingtype) == 'auto'){
            $c_ads = CampaignAdsBid::where('adgroupid', $adgroupid)->where('seller_id', $seller_id)
                ->orderBy('id', 'desc')->skip(0)->take(2)->get();
            if(count($c_ads) > 1){
                $before = CampaignAdvertising::where('adgroupid', $adgroupid)
                    ->where( function($query) use($c_ads){
                        $query->where('posted_date', '>=', $c_ads[1]->updated_at)
                            ->where('posted_date', '<=', $c_ads[0]->updated_at);
                    })
                    ->where('seller_id', $seller_id)
                    ->select($fields)->get()->first();
                $after = CampaignAdvertising::where('adgroupid', $adgroupid)
                    ->where('posted_date', '>=', $c_ads[0]->updated_at)
                    ->where('seller_id', $seller_id)
                    ->select($fields)->get()->first();
            }else{
                $before = CampaignAdvertising::where('adgroupid', $adgroupid)
                    ->where('posted_date', '<', $c_ads[0]->updated_at)
                    ->where('seller_id', $seller_id)
                    ->select($fields)->get()->first();
                $after = CampaignAdvertising::where('adgroupid', $adgroupid)
                    ->where('posted_date', '>=', $c_ads[0]->updated_at)
                    ->where('seller_id', $seller_id)
                    ->select($fields)->get()->first();
            }
        }else{
            $c_ads = CampaignAdsBid::where('keywordid', $keywordid)->where('seller_id', $seller_id)
                ->orderBy('id', 'desc')->skip(0)->take(2)->get();
            if(count($c_ads) > 1){
                $before = CampaignAdvertising::where('keyword_id', $keywordid)
                    ->where( function($query) use($c_ads){
                        $query->where('posted_date', '>=', $c_ads[1]->updated_at)
                            ->where('posted_date', '<=', $c_ads[0]->updated_at);
                    })
                    ->where('seller_id', $seller_id)
                    ->select($fields)->get()->first();
                $after = CampaignAdvertising::where('keyword_id', $keywordid)
                    ->where('posted_date', '>=', $c_ads[0]->updated_at)
                    ->where('seller_id', $seller_id)
                    ->select($fields)->get()->first();
            }else{
                $before = CampaignAdvertising::where('keyword_id', $keywordid)
                    ->where('posted_date', '<', $c_ads[0]->updated_at)
                    ->where('seller_id', $seller_id)
                    ->select($fields)->get()->first();
                $after = CampaignAdvertising::where('keyword_id', $keywordid)
                    ->where('posted_date', '>=', $c_ads[0]->updated_at)
                    ->where('seller_id', $seller_id)
                    ->select($fields)->get()->first();
            }
        }
        return  response()
                ->json([
                    'id' => $req->id,
                    'date'  => date_format($date_updated, 'd/m/Y'),
                    'imp' =>[
                        'before'=>(isset($before->impressions)) ? $before->impressions : 0, 
                        'after'=>(isset($after->impressions)) ? $after->impressions : 0
                        ],
                    'click' =>[
                        'before'=>(isset($before->clicks)) ? $before->clicks : 0, 
                        'after'=>(isset($after->clicks)) ? $after->clicks : 0
                        ],
                    'acos' =>[
                        'before'=>(isset($before->acos)) ? $before->acos : 0, 
                        'after'=>(isset($after->acos)) ? $after->acos : 0
                        ]
                ]);
    }

    public function check_arr2_consist_val_arr1($arr1, $arr2){
        $flag = false;
        for($x = 0; $x < count($arr1); $x++){
            if(in_array($arr1[$x], $arr2)){
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    public function getAdFilters(Request $request){
        $seller_id = Auth::user()->seller_id;
        $adsF = CampaignAdvertisingFilter::where('seller_id', $seller_id)
                ->select('id','filter_name')
                ->orderBy('filter_name','asc')
                ->get();
        $res = array();
        foreach ($adsF as $val) {
            $data = array();
            $data['id'] = $val->id;
            $data['filter_name'] = $val->filter_name;
            //$data[$val->id] = $val->filter_name;
            $res[] = $data;
        }
        echo json_encode($res);
    }

    public function addAdFilter(Request $req){
        $seller_id = Auth::user()->seller_id;
        $adsF = new CampaignAdvertisingFilter;
        $adsF->seller_id = $seller_id;
        $adsF->filter_name = (!isset($req->filter_name)) ? " " : $req->filter_name;
        $adsF->filter_columns = (!isset($req->filter_columns)) ? " " : $req->filter_columns;
        $date_start = (!isset($req->filter_date_start)) ? " " : $req->filter_date_start;
        if($date_start != " "){
            $date_start = str_replace('/', '-', $date_start);
            $date_start = date('Y-m-d', strtotime($date_start));
        }else{
            $date_start = null;
        }
        $adsF->filter_date_start = $date_start;

        $date_end = (!isset($req->filter_date_end)) ? " " : $req->filter_date_end;
        if($date_end != " "){
            $date_end = str_replace('/', '-', $date_end);
            $date_end = date('Y-m-d', strtotime($date_end));
        }else{
            $date_end = null;
        }
        $adsF->filter_date_end = $date_end;
        $adsF->filter_imp = (!isset($req->filter_imp)) ? " " : $req->filter_imp;
        $adsF->filter_clicks = (!isset($req->filter_clicks)) ? " " : $req->filter_clicks;
        $adsF->filter_ctr = (!isset($req->filter_ctr)) ? " " : $req->filter_ctr;
        $adsF->filter_total_spend = (!isset($req->filter_total_spend)) ? " " : $req->filter_total_spend;
        $adsF->filter_avg_cpc = (!isset($req->filter_avg_cpc)) ? " " : $req->filter_avg_cpc;
        $adsF->filter_acos = (!isset($req->filter_acos)) ? " " : $req->filter_acos;
        $adsF->filter_conv_rate = (!isset($req->filter_conv_rate)) ? " " : $req->filter_conv_rate;
        $adsF->filter_revenue = (!isset($req->filter_revenue)) ? " " : $req->filter_revenue;
        $adsF->filter_country = (!isset($req->filter_country)) ? " " : $req->filter_country;
        $adsF->filter_camp_type = (!isset($req->filter_camp_type)) ? " " : $req->filter_camp_type;
        $adsF->filter_camp_name = (!isset($req->filter_camp_name)) ? " " : $req->filter_camp_name;
        $adsF->filter_ad_group = (!isset($req->filter_ad_group)) ? " " : $req->filter_ad_group;
        $adsF->filter_keyword = (!isset($req->filter_keyword)) ? " " : $req->filter_keyword;
        $adsF->filter_recommendation = (!isset($req->filter_recommendation)) ? " " : $req->filter_recommendation;
        $adsF->created_at = date('Y-m-d H:i:s');
        $adsF->save();
    }

    public function updateAdFilter(Request $req){
        $adsF = CampaignAdvertisingFilter::find($req->id);
        $adsF->filter_name = (!isset($req->filter_name)) ? " " : $req->filter_name;
        $adsF->filter_columns = (!isset($req->filter_columns)) ? " " : $req->filter_columns;
        $date_start = (!isset($req->filter_date_start)) ? " " : $req->filter_date_start;
        if($date_start != " "){
            $date_start = str_replace('/', '-', $date_start);
            $date_start = date('Y-m-d', strtotime($date_start));
        }else{
            $date_start = null;
        }
        $adsF->filter_date_start = $date_start;

        $date_end = (!isset($req->filter_date_end)) ? " " : $req->filter_date_end;
        if($date_end != " "){
            $date_end = str_replace('/', '-', $date_end);
            $date_end = date('Y-m-d', strtotime($date_end));
        }else{
            $date_end = null;
        }
        $adsF->filter_date_end = $date_end;
        $adsF->filter_imp = (!isset($req->filter_imp)) ? " " : $req->filter_imp;
        $adsF->filter_clicks = (!isset($req->filter_clicks)) ? " " : $req->filter_clicks;
        $adsF->filter_ctr = (!isset($req->filter_ctr)) ? " " : $req->filter_ctr;
        $adsF->filter_total_spend = (!isset($req->filter_total_spend)) ? " " : $req->filter_total_spend;
        $adsF->filter_avg_cpc = (!isset($req->filter_avg_cpc)) ? " " : $req->filter_avg_cpc;
        $adsF->filter_acos = (!isset($req->filter_acos)) ? " " : $req->filter_acos;
        $adsF->filter_conv_rate = (!isset($req->filter_conv_rate)) ? " " : $req->filter_conv_rate;
        $adsF->filter_revenue = (!isset($req->filter_revenue)) ? " " : $req->filter_revenue;
        $adsF->filter_country = (!isset($req->filter_country)) ? " " : $req->filter_country;
        $adsF->filter_camp_type = (!isset($req->filter_camp_type)) ? " " : $req->filter_camp_type;
        $adsF->filter_camp_name = (!isset($req->filter_camp_name)) ? " " : $req->filter_camp_name;
        $adsF->filter_ad_group = (!isset($req->filter_ad_group)) ? " " : $req->filter_ad_group;
        $adsF->filter_keyword = (!isset($req->filter_keyword)) ? " " : $req->filter_keyword;
        $adsF->filter_recommendation = (!isset($req->filter_recommendation)) ? " " : $req->filter_recommendation;
        $adsF->updated_at = date('Y-m-d H:i:s');
        $adsF->save();
    }

    public function getAdFilterData(Request $req){
        $adsF = CampaignAdvertisingFilter::where('id', $req->id)->get();
        $data = array();
        foreach ($adsF as $val) {
            $data['filter_name'] = $val->filter_name;
            $data['filter_columns'] = $val->filter_columns;
            if($val->filter_date_start != null AND $val->filter_date_start != "")
              $data['filter_date_start'] = date('d/m/Y', strtotime($val->filter_date_start));
            if($val->filter_date_end != null AND $val->filter_date_end != "")
              $data['filter_date_end'] = date('d/m/Y', strtotime($val->filter_date_end));
            $data['filter_imp'] = $val->filter_imp;
            $data['filter_clicks'] = $val->filter_clicks;
            $data['filter_ctr'] = $val->filter_ctr;
            $data['filter_total_spend'] = $val->filter_total_spend;
            $data['filter_avg_cpc'] = $val->filter_avg_cpc;
            $data['filter_acos'] = $val->filter_acos;
            $data['filter_conv_rate'] = $val->filter_conv_rate;
            $data['filter_revenue'] = $val->filter_revenue;
            $data['filter_country'] = $val->filter_country;
            $data['filter_camp_type'] = $val->filter_camp_type;
            $data['filter_camp_name'] = $val->filter_camp_name;
            $data['filter_ad_group'] = $val->filter_ad_group;
            $data['filter_keyword'] = $val->filter_keyword;
            $data['filter_recommendation'] = $val->filter_recommendation;
            }
        echo json_encode($data);
    }

    public function deleteAdFilter(Request $req){
        $adsF = CampaignAdvertisingFilter::find($req->id);
        $adsF->delete();
    }

    public function getAdGraph(Request $req){
        ini_set('memory_limit', '512M');
        ini_set("max_execution_time", 0);  // on
        $seller_id = Auth::user()->seller_id;
        $s = (!isset($req->filter_date_start)) ? " " : $req->filter_date_start;
        $e = (!isset($req->filter_date_end)) ? " " : $req->filter_date_end;
        $dates = $this->getStartEndDate($s, $e);

        $filters['seller_id'] = $seller_id;
        $filters['filter_date_start'] = $dates['date_start'];
        $filters['filter_date_end'] = $dates['date_end'];
        $filters['filter_imp'] = (!isset($req->filter_imp)) ? " " : $req->filter_imp;
        $filters['filter_clicks'] = (!isset($req->filter_clicks)) ? " " : $req->filter_clicks;
        $filters['filter_ctr'] = (!isset($req->filter_ctr)) ? " " : $req->filter_ctr;
        $filters['filter_total_spend'] = (!isset($req->filter_total_spend)) ? " " : $req->filter_total_spend;
        $filters['filter_avg_cpc'] = (!isset($req->filter_avg_cpc)) ? " " : $req->filter_avg_cpc;
        $filters['filter_acos'] = (!isset($req->filter_acos)) ? " " : $req->filter_acos;
        $filters['filter_conv_rate'] = (!isset($req->filter_conv_rate)) ? " " : $req->filter_conv_rate;
        $filters['filter_revenue'] = (!isset($req->filter_revenue)) ? " " : $req->filter_revenue;
        $filters['filter_country'] = (!isset($req->filter_country)) ? " " : $req->filter_country;
        $filters['filter_camp_type'] = (!isset($req->filter_camp_type)) ? " " : $req->filter_camp_type;
        $filters['filter_camp_name'] = (!isset($req->filter_camp_name)) ? " " : $req->filter_camp_name;
        $filters['filter_ad_group'] = (!isset($req->filter_ad_group)) ? " " : $req->filter_ad_group;
        $filters['filter_keyword'] = (!isset($req->filter_keyword)) ? " " : $req->filter_keyword;
        $filters['filter_recommendation'] = (!isset($req->filter_recommendation)) ? " " : $req->filter_recommendation;
        $filters['filter_show_enabled'] = (!isset($req->filter_show_enabled)) ? " " : $req->filter_show_enabled;
        $filters['skip'] = 0;
        $take = 50000;
        $filters['take'] = $take;

        //set up graphs date
        $graph_dates = $this->setGraphDates($dates);
        $graph_data = array(
            'revenue' => $graph_dates,
            'total_spend' => $graph_dates,
            'acos' => $graph_dates,
            'average_cpc' => $graph_dates,
            'clicks' => $graph_dates,
            'impressions' => $graph_dates,
            'ctr' => $graph_dates,
            'cr' => $graph_dates,
            'orders_placed' => $graph_dates,
            'attributedconversions30dsamesku' => $graph_dates,
            'product_sales' => $graph_dates
        );
        //set up for recommendation
        $car = CampaignAdsRecommendation::where('seller_id', $seller_id)
                ->where('is_active', 1)
                ->get();
        $car_arr = array();
        foreach ($car as $key => $value) {
            $car_arr[$value->id] = $value;
        }

        $campaigns = new CampaignAdvertising;
        $raw_data = array();
        while(true){
            //fetch data
            $raw_data[] = $campaigns->getFilteredData($filters,false,true);
            //process data
            //$graph_data = $this->setupGraphDataByDate($graph_data, $raw_data, $filters['filter_recommendation'], $car_arr);

            //check for the flag loop and end if less than 5000 which means end of data
            if(count($raw_data)<$take){
                break;
                $raw_data = array();
            }
            $filters['skip'] += $take;
            $filters['take'] += $take;
        }
        // $graph_data = $this->setupGraphDataByDate($graph_data, $raw_data, $filters['filter_recommendation'], $car_arr);
        // $graph = $this->setupGraphData($graph_data);
        $rec_filter = $filters['filter_recommendation'];
        $rec_filter = trim(trim($rec_filter), ",");
        $rec_filter = explode(',', $rec_filter);

        foreach ($raw_data as $raw) {
            foreach ($raw as $value) {
                // $recommendation = '';
                // if ($value->recommendation != '' || $value->recommendation != null) {
                //     $str = $value->recommendation;
                //     $str = str_replace('-', ' ', $str);
                //     $str = explode(' ', trim($str));
                //     foreach ($str as $key => $value2) {
                //         if(array_key_exists($value2, $car_arr)){
                //             $recommendation .= $car_arr[$value2]->recommendation.', ';
                //         }
                //     }
                // }
                // $recommendation = rtrim($recommendation,", ");
                // $rec_arr = explode(', ', $recommendation);
                // $flag = $this->check_arr2_consist_val_arr1($rec_filter, $rec_arr);
                // if($flag){
                    //($camp->clicks == 0) ? 0 : ($camp->attributedconversions30d/$camp->clicks)*100;
                    $graph_data['clicks'][date('Y-m-d',strtotime($value->posted_date))] += $value->clicks;
                    $graph_data['total_spend'][date('Y-m-d',strtotime($value->posted_date))] += round($value->total_spend,2);
                    $graph_data['impressions'][date('Y-m-d',strtotime($value->posted_date))] += $value->impressions;
                    $graph_data['revenue'][date('Y-m-d',strtotime($value->posted_date))] += round($value->attributedconversions30dsamesku,2);
                    //$graph_data['orders_placed'][date('Y-m-d',strtotime($value->posted_date))] += 0;
                    $graph_data['attributedconversions30dsamesku'][date('Y-m-d',strtotime($value->posted_date))] += round($value->attributedconversions30dsamesku,2);
                    $graph_data['product_sales'][date('Y-m-d',strtotime($value->posted_date))] += round($value->attributedconversions30dsamesku,2);
                //}
            }
        }

        foreach ($graph_data['clicks'] as $key => $value) {
            if($graph_data['impressions'][$key] > 0)
                $graph_data['ctr'][$key] = round((($value/$graph_data['impressions'][$key])*100), 2);
            else
                $graph_data['ctr'][$key] = 0;

            if($value > 0)
                $graph_data['cr'][$key] = round((($graph_data['attributedconversions30dsamesku'][$key]/$value)*100), 2);
            else
                $graph_data['cr'][$key] = 0;
        }
        foreach ($graph_data['total_spend'] as $key => $value) {
            if($graph_data['product_sales'][$key] > 0)
                $graph_data['acos'][$key] = round((($value/$graph_data['attributedconversions30dsamesku'][$key])*100), 2);
            else
                $graph_data['acos'][$key] = 0;

            if($graph_data['clicks'][$key] > 0)
                $graph_data['average_cpc'][$key] = round(($value/$graph_data['clicks'][$key]), 2);
            else
                $graph_data['average_cpc'][$key] = 0;
        }
        //removing date keys
        foreach ($graph_data as $key => $value) {
            $graph_data[$key] = array_values($value);
        }
        $graph_data['clicks_t'] = array_sum($graph_data['clicks']);
        $graph_data['total_spend_t'] = round( array_sum($graph_data['total_spend']), 2);

        if(array_sum($graph_data['clicks']) > 0){
            $graph_data['average_cpc_t'] = round( array_sum($graph_data['total_spend'])/array_sum($graph_data['clicks']), 2 );
            $graph_data['cr_t'] = round( ((array_sum($graph_data['attributedconversions30dsamesku'])/array_sum($graph_data['clicks']))), 2 );
        }else{
            $graph_data['average_cpc_t'] = 0;
            $graph_data['cr_t'] = 0;
        }

        $graph_data['impressions_t'] = round( array_sum($graph_data['impressions']) );
        $graph_data['revenue_t'] = round( array_sum($graph_data['attributedconversions30dsamesku']), 2);

        if(array_sum($graph_data['product_sales']) > 0){
            $graph_data['acos_t'] = round( ((array_sum($graph_data['total_spend'])/array_sum($graph_data['product_sales']))*100), 2 );
        }else{
            $graph_data['acos_t'] = 0;
        }

        if(array_sum($graph_data['impressions']) > 0)
            $graph_data['ctr_t'] = round( ((array_sum($graph_data['clicks'])/array_sum($graph_data['impressions']))*100), 2 );
        else
            $graph_data['ctr_t'] = 0;

        $graph = $graph_data;
        $graph['date_start'] = date('d/m/Y', strtotime($dates['date_start']));
        $graph['date_end'] = date('d/m/Y', strtotime($dates['date_end']));
        //print_r($dates);
        echo json_encode($graph);
    }

    public function getStartEndDate($s, $e){
        if($e != " " AND $e != null)
            $e = date('Y-m-d',strtotime(str_replace('/', '-', $e)));
        else
            $e = date('Y-m-d');

        if($s != " " AND $s != null)
            $s = date('Y-m-d',strtotime(str_replace('/', '-', $s)));
        else
            $s = date('Y-m-d', strtotime('-30 days'.$e));

        return ['date_start' => $s, 'date_end'=>$e];
    }

    public function setGraphDates($dates){
        $graph_date = array();
        $ds = $dates['date_start'];
        while (strtotime($ds) <= strtotime($dates['date_end'])) {
            $graph_date[$ds] = 0;
            $ds = date ("Y-m-d", strtotime("+1 day", strtotime($ds)));
        }
        return $graph_date;
    }

    public function calculateCR($data, $total_clicks){
        $cr = array();
        foreach ($data as $key => $value) {
            if(!is_int($value))
                $cr[$key] = 0;
            else if($value > 0)
                $cr[$key] = round(($value/$total_clicks)*100 , 2);
            else
                $cr[$key] = 0;
        }
        return $cr;
    }

    public function updateAdComment(Request $req){
        $ad_id = trim($req->row_id);
        $ads = CampaignAdvertising::find($ad_id);
        $ads->comment = (!isset($req->comment)) ? " " : trim($req->comment);
        $ads->updated_at = date('Y-m-d H:i:s');
        $ads->save();
        echo $req->comment;
    }


    public function updateAdsBid(Request $req){
        $seller_id = Auth::user()->seller_id;

        if(trim($req->row_id) != ""){
            $ads = CampaignAdvertising::where('id', $req->row_id)->get()->first();
            $targetingtype = $ads->type;
            $keywordid = is_null($ads->keyword_id) ? 0 : $ads->keyword_id;
        }else if (trim($req->adgroupid) != "") {
            $ads = AdsCampaignAdGroup::where('adgroupid', $req->adgroupid)->get()->first();
            $targetingtype = AdsCampaign::where('campaignid', $ads->campaignid)->get()->first()->targetingtype;
            $keywordid = 0;
        }else if(trim($req->keywordid) != ""){
            $ads = AdsCampaignKeyword::where('keywordid', $req->keywordid)->get()->first();
            $targetingtype = AdsCampaign::where('campaignid', $ads->campaignid)->get()->first()->targetingtype;
            $keywordid = is_null($ads->keywordid) ? 0 : $ads->keywordid;
        }else{
            echo $req->bid_from;
            die();
        }

        $campaignid = $ads->campaignid;
        $adgroupid = $ads->adgroupid;
        


        $cabs = CampaignAdsBid::where('seller_id',$seller_id)
              ->where('campaignid',$campaignid)
              ->where('adgroupid',$adgroupid);

        if($ads->type == 'MANUAL')
            $cabs = $cabs->where('keywordid',$keywordid);

        $cabs = $cabs->where('is_uploaded', 0)
              ->get()
              ->first();

        if(isset($cabs))
        {
            $cabs->bid_from = $req->bid_from;
            $cabs->bid_to = $req->bid_to;
            $cabs->updated_at = date('Y-m-d H:i:s');
            $cabs->save();
        }
        else
        {
            $new = new CampaignAdsBid;

            $new->seller_id = $seller_id;
            $new->campaign_ads_id = is_null($req->row_id) ? 0 : $req->row_id;
            $new->bid_from = $req->bid_from;
            $new->bid_to = $req->bid_to;
            $new->campaignid = $campaignid;
            $new->adgroupid = $adgroupid;
            $new->keywordid = $keywordid;
            $new->targetingtype = $targetingtype;
            $new->is_uploaded = 0;
            $new->save();
        }

        echo $req->bid_to;
    }

    public function deleteAdsBid(Request $req){
        // echo json_encode($req->row_id);
        $cab = CampaignAdsBid::where('campaign_ads_id',$req->id);
        $cab->delete();
    }

    public function getAdsBid(){
        $seller_id = Auth::user()->seller_id;

        $cabs = CampaignAdsBid::where('seller_id',$seller_id)->where('is_uploaded', 0)
                                ->get();

        $arr = array();

        foreach($cabs as $cab)
        {
            $cab->match_type_to = $cab->match_type_to != '' ? $cab->match_type_to : 'Click to edit';

            $data = array();
            //$camp = CampaignAdvertising::find($cab->campaign_ads_id);
            $camp = AdsCampaign::where('campaignid', $cab->campaignid)->get()->first();
            $data['rowId'] = $cab->campaign_ads_id;
            $data['DT_RowId'] = $cab->campaign_ads_id;
            //$data[] = $camp->campaign_name;
            $data[] = $camp->name;
            if($cab->adgroupid != 0)
                $data[] = AdsCampaignAdGroup::where('adgroupid', $cab->adgroupid)->get()->first()->name;
            else
                $data[] = AdsCampaignKeyword::where('keywordid', $cab->keywordid)->get()->first()->keywordtext;
            // $data[] = $camp->ad_group_name;
            // $data[] = $camp->keyword;
            $data[] = strtolower($cab->targetingtype);
            $data[] = $cab->bid_from.'<input type="hidden" value="'.$cab->bid_to.'">';
            $data[] = $cab->bid_to;
            $data[] = $cab->match_type;
            $data[] = '<span class="matchType">'.$cab->match_type_to.'</span>';
            $data[] = '<div class="text-center"><button class="btn btn-danger btn-sm deleteBidBtn"><i class="fa fa-trash"></a></button></div>';
            $arr[] = $data;

        }

        echo json_encode($arr);

    }

    public function updateMatchType(Request $req){

        $seller_id = Auth::user()->seller_id;

        $ads = CampaignAdvertising::where('id', $req->id)->get()->first();
        $campaignid = $ads->campaignid;
        $adgroupid = $ads->adgroupid;
        $keywordid = $ads->keyword_id;
        $targetingtype = $ads->type;


        $cabs = CampaignAdsBid::where('seller_id',$seller_id)
              ->where('campaignid',$campaignid)
              ->where('adgroupid',$adgroupid);

        if($ads->type == 'MANUAL')
            $cabs = $cabs->where('keywordid',$keywordid);

        $cabs = $cabs->where('is_uploaded', 0)
              ->get()
              ->first();

        if(isset($cabs))
        {
            $cabs->match_type = $req->matchTypeFrom;
            $cabs->match_type_to = $req->matchTypeTo;
            $cabs->updated_at = date('Y-m-d H:i:s');
            $cabs->save();
        }
        else
        {
            $new = new CampaignAdsBid;

            $new->seller_id = $seller_id;
            $new->campaign_ads_id = $req->id;
            $new->match_type = $req->matchTypeFrom;
            $new->match_type_to = $req->matchTypeTo;
            $new->campaignid = $campaignid;
            $new->adgroupid = $adgroupid;
            $new->keywordid = $keywordid;
            $new->targetingtype = $targetingtype;
            $new->is_uploaded = 0;
            $new->save();
        }

        echo $req->matchTypeTo;

    }

    public function countChanges(){
        $count = CampaignAdsBid::where('seller_id', Auth::user()->seller_id)
            ->where('is_uploaded', 0)->get()->count();
        echo $count;
    }


    // START CAMPAIGN ADS RECOMMENDATION FUNCTIONS
    private function implementRule($req, $camp_ads_rec_cond, $id) {
        ini_set('memory_limit', $memory_limit);
        ini_set("zlib.output_compression", 0);  // off
        ini_set("implicit_flush", 1);  // on
        ini_set("max_execution_time", 0);  // on

        $ca = DB::connection('mysql2')->table('campaign_advertisings')
                ->where(function($query) use ($req,$camp_ads_rec_cond) {

                    $campaign_name_list_f = "";
                    $campaign_name_list = $req->campaignName;
                    $campaign_name_list = explode(',', trim($campaign_name_list));
                    foreach ($campaign_name_list as $key => $value) {
                        $campaign_name_list_f .= "'".$value."',";
                    }
                    $campaign_name_list_f = rtrim($campaign_name_list_f,",");

                    $country_list_f = "";
                    $country_list = $req->country;
                    $country_list = explode(',', trim($country_list));
                    foreach ($country_list as $key => $value) {
                        $country_list_f .= "'".strtolower($value)."',";
                    }
                    $country_list_f = rtrim($country_list_f,",");

                    $query->whereRaw("seller_id = ".Auth::user()->seller_id);
                    $query->whereRaw("country IN (".$country_list_f.")");
                    $query->whereRaw("campaign_name IN (".$campaign_name_list_f.")");

                    $today = date('Y-m-d');
                    $interval = '';
                    if ($req->timePeriod == 7) {
                        $interval = ' + INTERVAL 6 DAY';
                    } else if ($req->timePeriod == 14) {
                        $interval = ' + INTERVAL 13 DAY';
                    } else if ($req->timePeriod == 30) {
                        $interval = ' + INTERVAL 29 DAY';
                    } else if ($req->timePeriod == 60) {
                        $interval = ' + INTERVAL 59 DAY';
                    } else if ($req->timePeriod == 90) {
                        $interval = ' + INTERVAL 89 DAY';
                    }
                    $query->whereRaw("DATE(posted_date".$interval.") >= '".$today."'");

                    $q_str = '';
                    foreach ($camp_ads_rec_cond as $val) {
                        $column = '';
                        if ($val['matrix']== 'revenue') {
                            $column = 'other_sku_units_product_sales_within_1_week_of_click + same_sku_units_product_sales_within_1_week_of_click';
                        } else {
                            $column = $val['matrix'];
                        }

                        $operator = '';
                        if ($val['metric']== '≥') {
                            $operator = '>=';
                        } else if ($val['metric']== '≤') {
                            $operator = '<=';
                        } else {
                            $operator = '=';
                        }

                        if ($val['operation'] == 'default') {
                            $q_str .= " ".$column." ".$operator." ".$val['value'];
                        } else if ($val['operation'] == 'and') {
                            $q_str .= " AND ".$column." ".$operator." ".$val['value'];
                        } else {
                            $q_str .= " OR (".$column." ".$operator." ".$val['value']."
                                    AND seller_id = ".Auth::user()->seller_id."
                                    AND country IN (".$country_list_f.")
                                    AND campaign_name IN (".$campaign_name_list_f.")
                                    AND DATE(posted_date".$interval.") >= '".$today."')";
                        }
                    }
                    $query->whereRaw($q_str);

                   })
                ->get();

        foreach ($ca as $value) {
            $new_ca = CampaignAdvertising::find($value->id);
            $old_r = $new_ca->recommendation;
            if ($old_r == '' || $old_r == null) {
                $new_ca->recommendation = '-'.$id.'-';
            } else {
                $new_ca->recommendation = $old_r.$id.'-';
            }
            $new_ca->save();
        }
        return true;

    }

    private function removeRule($id) {
        ini_set('memory_limit', $memory_limit);
        ini_set("zlib.output_compression", 0);  // off
        ini_set("implicit_flush", 1);  // on
        ini_set("max_execution_time", 0);  // on

        $ca = DB::connection('mysql2')->table('campaign_advertisings')
                    ->where('recommendation', 'like', '-'.$id.'-')
                    ->orWhere('recommendation', 'like', '-'.$id.'-%')
                    ->orWhere('recommendation', 'like', '%-'.$id.'-')
                    ->orWhere('recommendation', 'like', '%-'.$id.'-%')
                    ->get();

        foreach ($ca as $value) {
            $new_ca = CampaignAdvertising::find($value->id);
            $old_r = $new_ca->recommendation;
            if ($old_r == '-'.$id.'-') {
                $new_ca->recommendation = '';
            } else {
                $str = str_replace('-'.$id.'-', '-', $old_r);
                $new_ca->recommendation = $str;
            }
            $new_ca->save();
        }

        return true;
    }

    public function saveAdRule(Request $req){

        $response = [];
        $car = new CampaignAdsRecommendation;
        $car->seller_id = Auth::user()->seller_id;
        $car->campaign_name = $req->campaignName;
        $car->country = strtolower($req->country);
        $car->recommendation = $req->recommendation;
        $car->recommendation_name = $req->recommendationName;
        $car->time_period = $req->timePeriod;
        if ($car->save()) {
            $camp_ads_rec_id = $car->id;

            $camp_ads_rec_cond = array();

            foreach ($req->operation as $key => $value) {
                $camp_ads_rec_cond[$key]['camp_ads_rec_id'] = $camp_ads_rec_id;
                $camp_ads_rec_cond[$key]['operation'] = $value;
            }
            foreach ($req->matrix as $key => $value) {
                $camp_ads_rec_cond[$key]['matrix'] = $value;
            }
            foreach ($req->metric as $key => $value) {
                $camp_ads_rec_cond[$key]['metric'] = $value;
            }
            foreach ($req->value as $key => $value) {
                $camp_ads_rec_cond[$key]['value'] = $value;
                $camp_ads_rec_cond[$key]['created_at'] = date('Y-m-d H:i:s');
                $camp_ads_rec_cond[$key]['updated_at'] = date('Y-m-d H:i:s');
            }
            if (CampaignAdsRecommendationCondition::insert($camp_ads_rec_cond)) {

                if ($this->implementRule($req, $camp_ads_rec_cond, $camp_ads_rec_id)) {
                    $response[0]['success'] = 1;
                    $response[0]['id'] = $car->id;
                    $response[0]['recommendation_name'] = $car->recommendation_name;
                    $response[0]['country'] = $car->country;
                    $response[0]['campaign_name'] = $car->campaign_name;
                    $response[0]['recommendation'] = $car->recommendation;
                    $response[0]['time_period'] = $car->time_period;
                    $response[0]['created_at'] = $car->created_at;
                    $response[0]['updated_at'] = $car->updated_at;
                    // data from Campaign Ads Recommendation Conditions
                    $carc = CampaignAdsRecommendationCondition::where('camp_ads_rec_id', '=', $camp_ads_rec_id)
                                                ->where('is_active', '=', 1)
                                                ->get();
                    $j = 0;
                    foreach ($carc as $v) {
                        $condition[$j]['id'] = $v->id;
                        $condition[$j]['operation'] = $v->operation;
                        $condition[$j]['matrix'] = $v->matrix;
                        $condition[$j]['metric'] = $v->metric;
                        $condition[$j]['value'] = $v->value;
                        $condition[$j]['created_at'] = $v->created_at;
                        $condition[$j]['updated_at'] = $v->updated_at;
                        $j++;
                    }
                    $response[0]['condition'] = $condition;
                } else {
                    CampaignAdsRecommendation::find($req->id)->delete();
                    CampaignAdsRecommendationCondition::where('camp_ads_rec_id', $req->id)->delete();
                    $response[]['success'] = 0;
                }
            }
        } else {
            $response[]['success'] = 0;
        }

        echo json_encode($response);
    }

    public function showAdRule() {
        $car = CampaignAdsRecommendation::where('seller_id', '=', Auth::user()->seller_id)
                                    ->where('is_active', '=', 1)
                                    ->orderBy('id', 'desc')
                                    ->get();
        $data = array();
        $i = 0;
        foreach ($car as $val) {
            $condition = array();
            // data from Campaign Ads Recommendations
            $data[$i]['id'] = $val->id;
            $data[$i]['country'] = $val->country;
            $data[$i]['campaign_name'] = $val->campaign_name;
            $data[$i]['recommendation'] = $val->recommendation;
            $data[$i]['recommendation_name'] = $val->recommendation_name;
            $data[$i]['time_period'] = $val->time_period;
            $data[$i]['created_at'] = $val->created_at;
            $data[$i]['updated_at'] = $val->updated_at;

            // data from Campaign Ads Recommendation Conditions
            $carc = CampaignAdsRecommendationCondition::where('camp_ads_rec_id', '=', $val->id)
                                        ->where('is_active', '=', 1)
                                        ->get();

            $j = 0;
            foreach ($carc as $v) {
                $condition[$j]['id'] = $v->id;
                $condition[$j]['operation'] = $v->operation;
                $condition[$j]['matrix'] = $v->matrix;
                $condition[$j]['metric'] = $v->metric;
                $condition[$j]['value'] = $v->value;
                $condition[$j]['created_at'] = $v->created_at;
                $condition[$j]['updated_at'] = $v->updated_at;
                $j++;
            }

            $data[$i]['condition'] = $condition;

            $i++;
        }

        echo json_encode($data);
    }


    public function saveChanges(Request $req){

        $response = [];
        $car = CampaignAdsRecommendation::find($req->id);
        $car->campaign_name = $req->campaignName;
        $car->country = strtolower($req->country);
        $car->recommendation = $req->recommendation;
        $car->recommendation_name = $req->recommendationName;
        $car->time_period = $req->timePeriod;
        if ($car->save()) {

            CampaignAdsRecommendationCondition::where('camp_ads_rec_id', $req->id)->delete();
            $camp_ads_rec_id = $car->id;

            $camp_ads_rec_cond = array();

            foreach ($req->operation as $key => $value) {
                $camp_ads_rec_cond[$key]['camp_ads_rec_id'] = $camp_ads_rec_id;
                $camp_ads_rec_cond[$key]['operation'] = $value;
            }
            foreach ($req->matrix as $key => $value) {
                $camp_ads_rec_cond[$key]['matrix'] = $value;
            }
            foreach ($req->metric as $key => $value) {
                $camp_ads_rec_cond[$key]['metric'] = $value;
            }
            foreach ($req->value as $key => $value) {
                $camp_ads_rec_cond[$key]['value'] = $value;
                $camp_ads_rec_cond[$key]['created_at'] = date('Y-m-d H:i:s');
                $camp_ads_rec_cond[$key]['updated_at'] = date('Y-m-d H:i:s');
            }
            if (CampaignAdsRecommendationCondition::insert($camp_ads_rec_cond)) {

                if ($this->removeRule($camp_ads_rec_id)) {
                    if ($this->implementRule($req, $camp_ads_rec_cond, $camp_ads_rec_id)) {
                        $response[]['success'] = 1;
                        $response[]['id'] = $car->id;
                    } else {
                        CampaignAdsRecommendation::find($req->id)->delete();
                        CampaignAdsRecommendationCondition::where('camp_ads_rec_id', $req->id)->delete();
                        $response[]['success'] = 0;
                    }
                } else {
                    CampaignAdsRecommendation::find($req->id)->delete();
                    CampaignAdsRecommendationCondition::where('camp_ads_rec_id', $req->id)->delete();
                    $response[]['success'] = 0;
                }

            }
        } else {
            $response[]['success'] = 0;
        }

        echo json_encode($response);
    }

    public function deleteRule(Request $req){

        $response = [];
        $car = CampaignAdsRecommendation::find($req->id);
        $car->is_active = 0;
        if ($car->save()) {

            $carc = CampaignAdsRecommendationCondition::where('camp_ads_rec_id', $req->id)->get();
            foreach ($carc as $v) {
                $new_carc = CampaignAdsRecommendationCondition::find($v->id);
                $new_carc->is_active = 0;
                $new_carc->save();
            }

            $response[]['success'] = 1;
            $response[]['id'] = $car->id;

        }else{
            $response[]['success'] = 0;
        }

        echo json_encode($response);
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

    public function submit_bid_to_amazon(Request $req){
        $seller_id = Auth::user()->seller_id;
        $bids = CampaignAdsBid::where('seller_id', $seller_id)
            ->where('is_uploaded', 0)
            ->get();
        if(count($bids) <= 0){
            echo json_encode(["message"=>"Bid change table is empty!", "status"=>"error"]);
            die();
        }
        $keyword_list = array();
        $ad_group_list = array();
        $ids = array();
        $amz = new MWSCurl;
        foreach($bids as $bid){
            if($bid->bid_to <= 0.01){
                echo json_encode(["message"=>"One of your bid is to low. Bid shoud be greater than 0.01", "status"=>"error"]);
                die();
            }
            $ids[] = $bid->id;
            $campaign = AdsCampaign::where('campaignid', $bid->campaignid)->get()->first();
            if(strtoupper($bid->targetingtype) == 'MANUAL'){
                //manuall campaign by keywords
                $keyword_list[$campaign->country][] = ['keywordId'=>$bid->keywordid, 'bid'=>$bid->bid_to, 'match_type' => $bid->match_type_to, 'campaignId'=> $bid->campaignid, 'adgroupId'=>$bid->adgroupid, 'keywordText'=>""];
            }else{
                //auto campaign by adgroup
                $ad_group_list[$campaign->country][] = ['keywordId'=>$bid->keyword_id, 'bid'=>$bid->bid_to, 'match_type' => $bid->match_type_to, 'campaignId'=> $bid->campaignid, 'adgroupId'=>$bid->adgroupid, 'keywordText'=>""];
            }
        }

        // manual campaigns bid is set by keywords
        foreach($keyword_list as $key => $keyIds){
            $country = $key;
            $new_keywords = array();
            $new_negative_keywords = array();
            $new_keywords_bid = array();
            $update_keywords = array();
            $new_negative_keywords_bid = array();
            foreach($keyIds as $keyId){
                //check if the keyword and matchtype exist
                if($keyId['match_type'] != null AND trim($keyId['match_type']) != "" AND $keyId['match_type'] != "null" ){
                    $key_main = AdsCampaignKeyword::where('keywordid', $keyId['keywordId'])->get()->first();
                    
                    $key = AdsCampaignKeyword::where('campaignid', $key_main->campaignid)
                        ->where('adgroupid', $key_main->adgroupid)
                        ->where('matchtype', $key_main->matchtype)
                        ->where('keywordtext', $key_main->keywordtext )
                        ->get()->first();

                    $keyId['adgroupId'] = $key_main->adgroupid;
                    $keyId['keywordText'] = $key_main->keywordtext;

                    if(count($key) > 0){
                        $update_keywords[] = ['keywordId'=>(int)$keyId['keywordId'], 'bid'=>$keyId['bid'], 'state'=>'enabled'];
                    }else{
                        if(strpos(strtolower($keyId['match_type']), 'negative') === false){
                            $new_keywords_bid[] = $keyId['bid'];
                            $new_keywords[] = ['campaignId'=>(int)$keyId['campaignId'], 'adGroupId'=>(int)$keyId['adgroupId'], 'keywordText'=>$keyId['keywordText'], 'matchType'=>strtolower($keyId['match_type']), 'state'=>'enabled'];
                        }else{
                            $arr = explode(' ', strtolower($keyId['match_type']));
                            $arr[1] = ucfirst($arr[1]);
                            $keyId['match_type'] = implode('', $arr);

                            $new_negative_keywords_bid[] = $keyId['bid'];
                            $new_negative_keywords[] = ['campaignId'=>(int)$keyId['campaignId'], 'adGroupId'=>(int)$keyId['adgroupId'], 'keywordText'=>$keyId['keywordText'], 'matchType'=>$keyId['match_type'], 'state'=>'enabled'];
                        }
                    }
                }else{
                    $update_keywords[] = ['keywordId'=>(int)$keyId['keywordId'], 'bid'=>$keyId['bid'], 'state'=>'enabled'];
                }
            }

            $mkp = config('constant.country_mkp.'.strtolower($country));
            $profile = Amz::where('seller_id', $seller_id)->where('amz_country_code', $country)->get()->first();
            //$current = date_create(Carbon::now());
            //$expiry_date = date_create($profile->amz_expires_in);
            $refresh_token = $profile->amz_refresh_token;
            //if($current >= $expiry_date){
            //echo "Access token is expired. Requesting new access token...... ";
            $tokens = $amz->refresh_tokens($refresh_token);
            $data = ['amz_access_token'=>$tokens->access_token,
                'amz_expires_in'=>Carbon::now()->addHour(),
                'updated_at'=>Carbon::now(),
                'amz_refresh_token'=>$tokens->refresh_token];
            $update = Amz::where('seller_id', $seller_id)->update($data);
            //echo "<b>DONE!!</b><br>";
            $profile = Amz::where('seller_id', $seller_id)->where('amz_country_code', $country)->get()->first();
            //}
            $access_token = $profile->amz_access_token;
            $profile_id = $profile->amz_profile_id;
            //sample data for add new keyword
            //$data[] = ['campaignId'=>83154349539909, 'adGroupId'=>249406536299773, 'keywordText'=>"Ad Group 1 test keyword", 'matchType'=>'broad', 'state'=>'paused'];
            //$result = $amz->add_keywords($access_token, $profile_id, $mkp, $data);
            //sample data for update keyword
            //$data[] = ['keywordId'=>38703834200914, 'bid'=>0.05];
            //$result = $amz->update_keywords($access_token, $profile_id, $mkp, $data);
            if(count($new_keywords) > 0){
                $result = $amz->add_keywords($access_token, $profile_id, $mkp, $new_keywords);
                $cnt = 0;
                if( count($result) > 0 ){
                    foreach ($result as $value) {
                        if(stripos($value->code, 'success') !== false AND (isset($new_keywords_bid[$cnt]) AND $new_keywords_bid[$cnt] != null AND trim($new_keywords_bid[$cnt]) != "" AND $new_keywords_bid[$cnt] != "null") ){
                            $update_keywords[] = ['keywordId'=>(int)$value->keywordId, 'bid'=>$new_keywords_bid[$cnt], 'state'=>'enabled'];
                        }
                        $cnt++;
                    }
                }
            }
            if(count($new_negative_keywords) > 0 ){
                $result = $amz->add_adgroup_negativeKeywords($access_token, $profile_id, $mkp, $new_negative_keywords);
                $cnt = 0;
                if( count($result) > 0 ){
                    foreach ($result as $value) {
                        if(stripos($value->code, 'success') !== false AND (isset($new_negative_keywords_bid[$cnt]) AND $new_negative_keywords_bid[$cnt] != null AND trim($new_negative_keywords_bid[$cnt]) != "" AND $new_negative_keywords_bid[$cnt] != "null") ){
                            $update_keywords[] = ['keywordId'=>(int)$value->keywordId, 'bid'=>$new_negative_keywords_bid[$cnt], 'state'=>'enabled'];
                        }
                        $cnt++;
                    }
                }
            }
            if(count($update_keywords) > 0){
                $result = $amz->update_keywords($access_token, $profile_id, $mkp, $update_keywords);
                foreach ($update_keywords as $k) {
                    CampaignAdvertising::where('keyword_id', $k['keywordId'])->update(['bid'=>$k['bid']]);
                    AdsCampaignKeyword::where('keywordid', $k['keywordId'])->update(['bid'=>$k['bid']]);
                }
            }
            //print_r($result);
        }

        // auto campaigns where bid is set by adgroups
        foreach ($ad_group_list as $key => $adgroups) {
            $country = $key;
            $new_keywords = array();
            $new_keywords_bid = array();
            $new_negative_keywords = array();
            $update_keywords = array();
            $update_ad_groups = array();

            foreach ($adgroups as $adg) {
                if($adg['match_type'] != null AND trim($adg['match_type']) != "" AND $adg['match_type'] != "null" ){
                    // $key = AdsCampaignKeyword::where('keywordid', $adg->keywordId)
                    //     ->where('campaignid', $adg['campaignId'])
                    //     ->where('adgroupid', $adg['adgroupId'])
                    //     ->where('matchtype', $adg['match_type'])
                    //     ->where('keywordtext', $adg->keywordtext )
                    //     ->get()->first();
                    $key = AdsCampaignAdGroup::where('adgroupid', $adg->adgroupid)->get()->first();

                    if(count($key) > 0){
                        $k = AdsCampaignKeyword::where('adgroupid', $adgroupid)->get()->first();
                        if(isset($k)) 
                            $update_keywords[] = ['keywordId'=>(int)$k->keywordid, 'state'=>'enabled'];
                        $update_ad_groups[] = ['adGroupId'=>$adg['adgroupId'],'defaultBid'=>$adg['bid'], 'state'=>'enabled'];
                    }else{
                        // commented by ferdz 
                        // if(strpos(strtolower($adg['match_type']), 'negative') === false){
                        //     $new_keywords[] = ['campaignId'=>(int)$adg['campaignId'], 'adGroupId'=>(int)$adg['adgroupId'], 'keywordText'=>$adg['keywordtext'], 'matchType'=>strtolower($adg['match_type']), 'state'=>$key->state];
                        // }else{
                        //     $arr = explode(' ', strtolower($adg['match_type']));
                        //     $arr[1] = ucfirst($arr[1]);
                        //     $adg['match_type'] = implode('', $arr);

                        //     $new_negative_keywords[] = ['campaignId'=>(int)$adg['campaignId'], 'adGroupId'=>(int)$adg['adgroupId'], 'keywordText'=>$adg['keywordtext'], 'matchType'=>$adg['match_type'], 'state'=>$key->state];
                        // }
                        // if($adg['bid'] > 0 AND trim($adg['bid']) != '')
                        //     $update_ad_groups[] = ['adGroupId'=>$adg['adgroupId'],'defaultBid'=>$adg['bid']];
                    }
                }else{
                    if($adg['bid'] > 0 AND trim($adg['bid']) != '')
                        $update_ad_groups[] = ['adGroupId'=>(int)$adg['adgroupId'],'defaultBid'=>$adg['bid']];
                }
            }
            //request or refresh tokens
            $mkp = config('constant.country_mkp.'.strtolower($country));
            $profile = Amz::where('seller_id', $seller_id)->where('amz_country_code', $country)->get()->first();
            $refresh_token = $profile->amz_refresh_token;
            $tokens = $amz->refresh_tokens($refresh_token);
            $data = ['amz_access_token'=>$tokens->access_token,
                'amz_expires_in'=>Carbon::now()->addHour(),
                'updated_at'=>Carbon::now(),
                'amz_refresh_token'=>$tokens->refresh_token];
            $update = Amz::where('seller_id', $seller_id)->update($data);
            $profile = Amz::where('seller_id', $seller_id)->where('amz_country_code', $country)->get()->first();

            $access_token = $profile->amz_access_token;
            $profile_id = $profile->amz_profile_id;

            if(count($new_keywords) > 0)
                $result = $amz->add_keywords($access_token, $profile_id, $mkp, $new_keywords);

            if(count($new_negative_keywords) > 0)
                $result = $amz->add_adgroup_negativeKeywords($access_token, $profile_id, $mkp, $new_negative_keywords);

            if(count($update_keywords) > 0)
                $result = $amz->update_keywords($access_token, $profile_id, $mkp, $update_keywords);

            if(count($update_ad_groups) > 0){
                $result = $amz->update_adGroup($access_token, $profile_id, $mkp, $update_ad_groups);
                foreach ($update_ad_groups as $k) {
                    CampaignAdvertising::where('adgroupid', $k['adGroupId'])->update(['bid'=>$k['defaultBid']]);
                    AdsCampaignAdGroup::where('adgroupid', $k['adGroupId'])->update(['defaultbid'=>$k['defaultBid']]);
                }
            }
        }
        echo json_encode(["message"=>"Bid updates successfully submitted to Amazon!", "status"=>"success"]);
        CampaignAdsBid::whereIn('id', $ids)->update(['is_uploaded'=> 1, 'updated_at'=>date('Y-m-d H:i:s')]);
    }

    public function setBid()
    {
        ini_set('memory_limit', '360M');
        ini_set("max_execution_time", 0);
        $amz = new MWSCurl;
        $seller_id = Auth::user()->seller_id;
        $mkp = 2;
        $seller_profile = AmazonSellerDetail::where('seller_id', $seller_id)
                ->where('mkp_id', $mkp)
                ->get();


        $users = DB::connection('mysql2')->table('campaign_advertisings')
            ->join('ads_campaigns', 'campaign_advertisings.campaignid', '=', 'ads_campaigns.campaignid')
            ->where('ads_campaigns.targetingtype', 'auto')
            ->select('campaign_advertisings.*', 'ads_campaigns.name','ads_campaigns.targetingtype')
            ->take(15)
            ->get();
        foreach ($seller_profile as $profile)
        {
            $country = strtolower($profile->amz_country_code);
                //check if access token is expired
            $profile = $this->is_country_token_expired($country,$profile->seller_id,$profile->mkp_id);
            $refresh_token = $profile->amz_refresh_token;
            $access_token = $profile->amz_access_token;
            $profile_id = $profile->amz_profile_id;
            if($mkp == 1) $mkp_code = 'na';
            else $mkp_code = 'eu';
            $condition = ['startIndex' => 0];
            $campaigns = $amz->get_campaigns($access_token, $profile_id, $mkp_code, $condition);

            $users = DB::connection('mysql2')->table('campaign_advertisings')
            ->join('ads_campaigns', 'campaign_advertisings.campaignid', '=', 'ads_campaigns.campaignid')
            ->where('ads_campaigns.targetingtype', 'manual')
            ->select('campaign_advertisings.*', 'ads_campaigns.name','ads_campaigns.targetingtype')
            ->take(15)
            ->get();

            foreach($users as $user)
            {
                // $recommendations = $amz->get_bid_recommendation_by_adGroupId($access_token,$profile_id,$mkp_code,$user->adgroupid);

                // dd($recommendations);

                $keyRecommendation = $amz->get_bid_recommendation_by_keywordId($access_token,$profile_id,$mkp_code,$user->keyword_id);

                dd($keyRecommendation);


            }
        }
    }

    private function is_country_token_expired($country,$seller_id,$mkp){
        $amz = new MWSCurl;
        $profile = AmazonSellerDetail::where('seller_id', $seller_id)
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
            $update = AmazonSellerDetail::where('seller_id', $seller_id)->update($data);
            echo "<b>DONE!!</b><br>";
            $profile = AmazonSellerDetail::where('seller_id', $seller_id)->where('mkp_id', $mkp)->where('amz_country_code', $country)->get()->first();
        }
        return $profile;
    }
}
