<?php

namespace App\Http\Controllers\trendle;


use App\Http\Controllers\Crons\UpdateAdvertCampaignsController;

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
use App\MWSCustomClasses\MWSCurlAdvertisingClass;
use App\AmazonSellerDetail;
use Carbon\Carbon;
use App\AdsCampaignAdGroup;
use App\AdsCampaign;
use App\AdsCampaignKeyword;
use App\Http\Helpers\HelpersFacade;

class AdsRecommendationController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('checkStripe');
    }

    public function index(){

        $seller_id = Auth::user()->seller_id;
        $data = $this->callBaseSubscriptionName($seller_id);
        // if (($data->base_subscription == '' || $data->base_subscription == 'XS')&& Auth::user()->seller->is_trial == 0) {
        //     return redirect('subscription');
        // }

        /*  Added by jason 7/17/17
         *  Redirect if user is not logged in with amazon
        */
        $amz = new Amz;
        $amzChecker = 0;
        $amzChecker = $amz->where('seller_id', $seller_id)->first();
        $amzChecker = 1; // just comment or delete this line after testing
        if (!$amzChecker) {
            return view('trendle.adsrecommendation.index')
                ->with('bs',$data->base_subscription)
                ->with('amzChecker', $amzChecker);
        }
        $pnlcont = new PnLController;
        $countries = $pnlcont->getCountryListForThisSeller();
        $country = array();
        $countryRec = array();
        //$countryRec['select'] = 'Select All';
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
        //$countries = Countries::getListForSelect();
        //$data = compact('countries');
        //print_r($countries);

        // commented by Ferdz - move to getCampaignAdgroupData queried by ajax
        // $query = new AdsCampaign;
        // $camp_name_q = $query->where('seller_id', $seller_id)
        //     ->where('state', 'enabled')
        //     ->select(['name','country', 'targetingtype'])
        //     ->orderBy('name')
        //     ->get();
        $c = array();
        // $c['select'] = 'Select All';
        // $c_c_t = array();
        // $campaignid_name = array();
        // foreach ($camp_name_q as $key => $value) {
        //     $c[$value->name] = $value->name;
        //     $c_c_t[$value->country][$value->targetingtype] = $value->campaignid;
        //     $campaignid_name[$value->campaignid] = $value->name;
        // }
        $camp_name = $c;
        $camp_name1 = $c;

        $a = array();
        // $a['select'] = 'Select All';
        // $query = new AdsCampaignAdGroup;
        // $ad_group_name =$query->where('seller_id', $seller_id)
        //     ->where('state', 'enabled')
        //     ->select(['name','campaignid','adgroupid'])
        //     ->orderBy('name')
        //     ->get();
        // $c_adg = array();
        // $adgroupid_name = array();
        // foreach ($ad_group_name as $key => $value) {
        //     $a[$value->name] = $value->name;
        //     $c_adg[$value->campaignid] = $value->name;
        //     $adgroupid_name[$value->adgroupid] = $value->name;
        // }
        $ad_group_name = $a;

        return view('trendle.adsrecommendation.index')
                ->with('countries', $countries)
                ->with('countriesRec', $countriesRec)
                ->with('camp_name', $camp_name)
                ->with('camp_name1', $camp_name1)
                ->with('bs',$data->base_subscription)
                ->with('ad_group_name', $ad_group_name)
                ->with('amzChecker', $amzChecker);
                //->with('cct', $c_c_t)
                // ->with('campaign_list', $campaignid_name)
                // ->with('adgrouplist', $adgroupid_name)
                // ->with('cid_adg', $c_adg);
    }

    public function getCampaignAdgroupData(){
        $seller_id = Auth::user()->seller_id;

        $response = new HelpersFacade;
        $response = $response->getCampaignAdgroupDataForFilter($seller_id);
        echo json_encode($response);
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

    // START CAMPAIGN ADS RECOMMENDATION FUNCTIONS
    public function implementRule($req, $camp_ads_rec_cond, $seller_id) {
        ini_set("max_execution_time", 0);  // on

        $countryList =(isset($req->country)) ?  explode(',', $req->country) : array();
        $ttypeList = (isset($req->CampType)) ? explode(',', $req->CampType) : array();
        $campaignList = (isset($req->campaignName)) ? explode(',', $req->campaignName) : array();
        $adgList = isset($req->adGroupName) ? explode(',', $req->adGroupName) : array();
        $recommendation_setting = isset($req->recommendation) ? $req->recommendation : "";
        $date_range = isset($req->timePeriod) ? $req->timePeriod : 0;
        $date_range = ($date_range > 0) ? $date_range : 30;

        $fields = [
            'campaign_name',
            'ad_group_name',
            'type',
            'country',
            'bid',
            'adgroupid',
            'campaignid',
            'keyword_id'
        ];
        $condition_columns = [];
        foreach ($camp_ads_rec_cond as $val) {
            if ($val['matrix']== 'revenue') {
                $fields[] = DB::raw('SUM(attributedsales30d) as revenue');
                $condition_columns[] = $val['matrix'];
            }else if($val['matrix'] == 'acos'){
                $fields[] = DB::raw('if(sum(attributedsales30d) > 0, (sum(total_spend)/sum(attributedsales30d))*100,0) as acos');
                $condition_columns[] = $val['matrix'];
            }else if($val['matrix'] == 'impressions'){
                $fields[] = DB::raw('SUM(impressions) as impressions');
                $condition_columns[] = $val['matrix'];
            }else if($val['matrix'] == 'clicks'){
                $fields[] = DB::raw('SUM(clicks) as clicks');
                $condition_columns[] = $val['matrix'];
            }else if($val['matrix'] == 'ctr'){
                $fields[] = DB::raw('if(sum(impressions) > 0, (sum(clicks)/sum(impressions))*100,0) as ctr');
                $condition_columns[] = $val['matrix'];
            }else if($val['matrix'] == 'average_cpc'){
                $fields[] = DB::raw('if(sum(clicks) > 0, sum(total_spend)/sum(clicks),0) as average_cpc');
                $condition_columns[] = $val['matrix'];
            }else if($val['matrix'] == 'orders'){
                $fields[] = DB::raw('SUM(attributedconversions30dsamesku) as orders');
                $condition_columns[] = $val['matrix'];
            }

        }
        //DB::connection('mysql2')->enableQueryLog();
        $ca = CampaignAdvertising::where('seller_id', $seller_id);
        if(count($countryList) > 0){
            for($x = 0; $x < count($countryList); $x++){
                $countryList[$x] = strtolower($countryList[$x]);
            }
            $ca = $ca->whereIn('country', $countryList);
        }
        if(count($ttypeList) > 0){
            for($x=0; $x < count($ttypeList); $x++){
                if($ttypeList[$x] == 'Automatic') $ttypeList[$x] = 'AUTO';
                if($ttypeList[$x] == 'Manual') $ttypeList[$x] = 'MANUAL';
            }
            $ca = $ca->whereIn('type', $ttypeList);
        }
        if($date_range > 0){
            $ca = $ca->where('posted_date', '>=', Carbon::today()->subDays($date_range));
        }
        if(count($campaignList) > 0)
            $ca = $ca->whereIn('campaign_name', $campaignList);
        if(count($adgList) > 0)
            $ca = $ca->whereIn('ad_group_name', $adgList);

        $cnt = count($camp_ads_rec_cond);
        $condition = '';
        if(count($camp_ads_rec_cond) > 0){
            //$ca = $ca->where(function($query) use($camp_ads_rec_cond){
                foreach ($camp_ads_rec_cond as $val) {
                    $cnt--;
                    $column = $val['matrix'];
                    if ($val['matrix']== 'revenue') {
                        $column = 'SUM(attributedsales30d) ';
                    }else if($val['matrix'] == 'acos'){
                        $column = 'if(sum(attributedsales30d) > 0, (sum(total_spend)/sum(attributedsales30d))*100,0) ';
                    }else if($val['matrix'] == 'impressions'){
                        $column = 'SUM(impressions) ';
                    }else if($val['matrix'] == 'clicks'){
                        $column = 'SUM(clicks) ';
                    }else if($val['matrix'] == 'ctr'){
                        $column = 'if(sum(impressions) > 0, (sum(clicks)/sum(impressions))*100,0) ';
                    }else if($val['matrix'] == 'average_cpc'){
                        $column = 'if(sum(clicks) > 0, sum(total_spend)/sum(clicks),0) ';
                    }else if($val['matrix'] == 'orders'){
                        $column = 'SUM(attributedconversions30dsamesku) ';
                    }

                    $operator = '';
                    if ($val['metric']== '≥') {
                        $operator = '>=';
                    } else if ($val['metric']== '≤') {
                        $operator = '<=';
                    } else {
                        $operator = '=';
                    }

                    $operation = 'AND';
                    if (strtoupper($val['operation']) == 'OR') {
                        $operation = 'OR';
                    }

                    if($cnt <= 0) $operation = '';
                    if($column!='' AND $operator!='')
                        $condition .= ' ('.$column.' '.$operator.' '.$val['value'].' ) '. $operation.' ';
                        //$ca = $ca->havingRaw('('.$column.' '.$operator.' '.$val['value'].' ) '. $operation.' ');
                }
            //});
        }
        if($condition != '')
            $ca = $ca->havingRaw('('.$condition.')');

        if(count($adgList) > 0)
            $ca = $ca->select($fields)->groupBy('adgroupid');
        else
            $ca = $ca->select($fields)->groupBy('keyword_id');

        $data_set = [
            'adgroupid'=>collect(),
            'keyword_id'=>collect()
        ];
        $ca = $ca->chunk(500, function($campaign_advertisings) use ($condition_columns, $data_set, $adgList){
            foreach ($campaign_advertisings as $camp) {
                if(count($adgList) > 0){
                    $data_set['adgroupid']->push($camp->adgroupid);
                }else{
                    $data_set['keyword_id']->push($camp->keyword_id);
                }
            
            }
        });
        //print_r(DB::connection('mysql2')->getQueryLog());
        if(count($data_set['adgroupid']) > 0){
            CampaignAdvertising::whereIn('adgroupid', $data_set['adgroupid'])->update(['recommendation'=> DB::raw("concat(recommendation, '".$recommendation_setting."', ',')")]);
            AdsCampaignAdGroup::whereIn('adgroupid', $data_set['adgroupid'])
                ->update(['recommendation' => DB::raw("concat(recommendation, '".$recommendation_setting."', ',')")]);
        }
        
        if(count($data_set['keyword_id']) > 0){
            CampaignAdvertising::whereIn('keyword_id', $data_set['keyword_id'])->update(['recommendation' => DB::raw("concat(recommendation,'".$recommendation_setting."', ',')")]);
            AdsCampaignKeyword::whereIn('keywordid', $data_set['keyword_id'])
                ->update(['recommendation' => DB::raw("concat(recommendation,'".$recommendation_setting."', ',')")]);
        }


        // $ca = DB::connection('mysql2')->table('campaign_advertisings')
        //         ->where(function($query) use ($req,$camp_ads_rec_cond) {

        //             $caselect = 0;
        //             $campaign_name_list_f = "";
        //             $campaign_name_list = $req->campaignName;
        //             $campaign_name_list = explode(',', trim($campaign_name_list));
        //             foreach ($campaign_name_list as $key => $value) {
        //                 $campaign_name_list_f .= "'".$value."',";
        //                 if($value == "select")
        //                 {
        //                     $caselect = 1;
        //                 }
        //             }
        //             $campaign_name_list_f = rtrim($campaign_name_list_f,",");

        //             $cselect = 0;
        //             $country_list_f = "";
        //             $country_list = $req->country;
        //             $country_list = explode(',', trim($country_list));
        //             foreach ($country_list as $key => $value) {
        //                 $country_list_f .= "'".strtolower($value)."',";
        //                 if($value == "select")
        //                 {
        //                     $cselect = 1;
        //                 }
        //             }
        //             $country_list_f = rtrim($country_list_f,",");

        //             $aselect = 0;
        //             $ad_group_name_f = "";
        //             $ad_group_name = $req->adGroupName;
        //             $ad_group_name = explode(',', trim($ad_group_name));
        //             foreach ($ad_group_name as $key => $value) {
        //                 $ad_group_name_f .= "'".strtolower($value)."',";
        //                 if($value == "select")
        //                 {
        //                     $aselect = 1;
        //                 }
        //             }
        //             $ad_group_name_f = rtrim($ad_group_name_f,",");

        //             $camp_type_f = "";
        //             $camp_type = $req->CampType;
        //             $camp_type = explode(',', trim($camp_type));
        //             foreach ($camp_type as $key => $value) {
        //                 if($value == 'Automatic') $value = 'AUTO';
        //                 if($value == 'Manual') $value = 'MANUAL';
        //                 $camp_type_f .= "'".strtoupper($value)."',";
        //             }
        //             $camp_type_f = rtrim($camp_type_f,",");

        //             $query->whereRaw("seller_id = ".$seller_id);

        //             if($cselect != 1)
        //             {
        //                 $query->whereRaw("country IN (".$country_list_f.")");
        //             }

        //             if($caselect != 1)
        //             {
        //                 $query->whereRaw("campaign_name IN (".$campaign_name_list_f.")");
        //             }

        //             if($aselect != 1)
        //             {
        //                 $query->whereRaw("ad_group_name IN (".$ad_group_name_f.")");
        //             }

        //             $query->whereRaw("type IN (".$camp_type_f.")");

        //             $today = date('Y-m-d');
        //             $interval = '';
        //             if ($req->timePeriod == 7) {
        //                 $interval = ' + INTERVAL 6 DAY';
        //             } else if ($req->timePeriod == 14) {
        //                 $interval = ' + INTERVAL 13 DAY';
        //             } else if ($req->timePeriod == 30) {
        //                 $interval = ' + INTERVAL 29 DAY';
        //             } else if ($req->timePeriod == 60) {
        //                 $interval = ' + INTERVAL 59 DAY';
        //             } else if ($req->timePeriod == 90) {
        //                 $interval = ' + INTERVAL 89 DAY';
        //             }
        //             $query->whereRaw("DATE(posted_date".$interval.") >= '".$today."'");

        //             $q_str = '';
        //             foreach ($camp_ads_rec_cond as $val) {
        //                 $column = '';
        //                 if ($val['matrix']== 'revenue') {
        //                     $column = 'attributedsales30d';
        //                 } else {
        //                     $column = $val['matrix'];
        //                 }

        //                 $operator = '';
        //                 if ($val['metric']== '≥') {
        //                     $operator = '>=';
        //                 } else if ($val['metric']== '≤') {
        //                     $operator = '<=';
        //                 } else {
        //                     $operator = '=';
        //                 }

        //                 if ($val['operation'] == 'default') {
        //                     $q_str .= " ".$column." ".$operator." ".$val['value'];
        //                 } else if ($val['operation'] == 'and') {
        //                     $q_str .= " AND ".$column." ".$operator." ".$val['value'];
        //                 } else {
        //                     $q_str .= " OR (".$column." ".$operator." ".$val['value']."
        //                             AND seller_id = ".Auth::user()->seller_id."
        //                             AND country IN (".$country_list_f.")
        //                             AND campaign_name IN (".$campaign_name_list_f.")
        //                             AND DATE(posted_date".$interval.") >= '".$today."')";
        //                 }
        //             }
        //             $query->whereRaw($q_str);

        //            })
        //         ->get();

        // foreach ($ca as $value) {
        //     $new_ca = CampaignAdvertising::find($value->id);
        //     $old_r = $new_ca->recommendation;
        //     if ($old_r == '' || $old_r == null) {
        //         $new_ca->recommendation = '-'.$id.'-';
        //     } else {
        //         $new_ca->recommendation = $old_r.$id.'-';
        //     }
        //     $new_ca->save();
        // }

        return true;

    }

    private function removeRule($id) {
        ini_set('memory_limit', '1024M');
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
        $car->camp_type = $req->CampType;
        $car->ad_group_name = $req->adGroupName;
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
                if ($this->implementRule($req, $camp_ads_rec_cond, Auth::user()->seller_id)) {
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
                $response[]['success'] = 1;
                $response[]['id'] = $car->id;
            }
            $r = new UpdateAdvertCampaignsController;
            $r->updateRecommendation(Auth::user()->seller_id);
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

    public function test()
    {
        $amz = new MWSCurlAdvertisingClass;
        $seller_id = Auth::user()->seller_id;
        $profile = AmazonSellerDetail::where('seller_id', $seller_id)
            ->where('mkp_id', 2)->where('amz_country_code', 'UK')
            ->get()->first();
        $refresh_token = $profile->amz_refresh_token;
        $country = strtolower($profile->amz_country_code);
        $tokens = $amz->refresh_tokens($refresh_token);
        dd($tokens);
        $amz = $amz->get_bid_recommendation_by_adGroupId($refresh_token,$profile->amz_profile_id,'na','1');

        $profile = $this->is_country_token_expired($country,$seller_id);





        //public function get_bid_recommendation_by_adGroupId($access_token, $profile_id, $mkp, $adgroupid){
    }

    private function is_country_token_expired($country,$seller_id){
        $amz = new MWSCurlAdvertisingClass;
        $profile = AmazonSellerDetail::where('seller_id', $seller_id)
            ->where('mkp_id', 2)->where('amz_country_code', 'uk')
            ->get()->first();
        $current = date_create(Carbon::now());
        $expiry_date = date_create($profile->amz_expires_in);
        $refresh_token = $profile->amz_refresh_token;
        if($current >= $expiry_date){
            echo "Access token is expired. Requesting new access token...... ";
            $tokens = $amz->refresh_tokens($refresh_token);
            dd($tokens);
            $data = ['amz_access_token'=>$tokens->access_token,
                  'amz_expires_in'=>Carbon::now()->addHour(),
                  'updated_at'=>Carbon::now(),
                  'amz_refresh_token'=>$tokens->refresh_token];
            $update = AmazonSellerDetail::where('seller_id', $this->seller_id)->update($data);
            echo "<b>DONE!!</b><br>";
            $profile = AmazonSellerDetail::where('seller_id', $this->seller_id)->where('mkp_id', $this->mkp)->where('amz_country_code', $country)->get()->first();
        }
        return $profile;
    }
}
