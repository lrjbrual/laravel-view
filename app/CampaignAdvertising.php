<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth;

class CampaignAdvertising extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql2';

    protected $fillable = ['seller_id', 'country', 'keyword_id', 'customer_search_term', 'impressions', 'clicks', 'total_spend', 'attributedconversions1dsamesku', 'attributedconversions1d', 'attributedsales1dsamesku', 'attributedsales1d', 'attributedconversions7dsamesku', 'attributedconversions7d', 'attributedsales7dsamesku', 'attributedsales7d', 'attributedconversions30dsamesku', 'attributedconversions30d', 'attributedsales30dsamesku', 'attributedsales30d', 'posted_date', 'ctr', 'acos', 'average_cpc', 'campaignid', 'adgroupid', 'campaign_name', 'ad_group_name', 'type', 'keyword', 'currency', 'match_type', 'created_at', 'bid'];


    public function getFilteredData($filters, $count=false, $forgraph=false){
        // DB::connection('mysql2')->enableQueryLog();
        if($count) $fields = ['ads_campaigns.campaignid'];
        else $fields = [
                'ads_campaigns.country as country',
                'ads_campaigns.targetingtype as type', 
                'ads_campaigns.name as campaign_name',
                'ads_campaigns.campaignid as campaignid',
                //'ad_group_name', 
                //'keyword', 
                //'customer_search_term', 
                'campaign_advertisings.match_type as match_type', 
                DB::raw('SUM(campaign_advertisings.impressions) as impressions'), 
                DB::raw('SUM(campaign_advertisings.clicks) as clicks'), 
                //DB::raw('SUM(ctr) as ctr'), 
                DB::raw('SUM(campaign_advertisings.attributedsales30d) as attributedsales30d'), 
                DB::raw('SUM(campaign_advertisings.attributedconversions30dsamesku) as attributedconversions30dsamesku'), 
                // DB::raw('SUM(IF(campaign_advertisings.clicks > 0,((campaign_advertisings.attributedconversions30dsamesku/campaign_advertisings.clicks)),0)) as cr'), 
                DB::raw('SUM(campaign_advertisings.total_spend) as total_spend'), 
                // DB::raw('SUM(campaign_advertisings.average_cpc) as average_cpc'), 
                //DB::raw('SUM(acos) as acos'),
                //DB::raw('SUM(bid) as bid'), 
                //DB::raw('SUM(max_bid_recommendation) as max_bid_recommendation'), 
                //'comment', 
                //'recommendation', 
                //'attributedconversions30d', 
                'campaign_advertisings.id as id', 
                'campaign_advertisings.posted_date as posted_date', 
                'campaign_advertisings.attributedsales7d as attributedsales7d'
            ];

        $skip =  (!isset($filters['skip'])) ? " " : $filters['skip'];
        $take = (!isset($filters['take'])) ? " " : $filters['take'];
        if($count){
            $sort_column = " ";
            $sort_direction = " ";
        }else{
            if(isset($filters['sort_column'])){
                if($filters['sort_column'] == 3){ 
                    $filters['sort_column'] = $filters['sort_column']-1;
                }
            }
            $sort_column = (!isset($filters['sort_column'])) ? " " : $fields[$filters['sort_column']];
            //if(trim($sort_column) != "" AND $filters['sort_column'] == 12) $sort_column = 'cr';
            $sort_direction = (!isset($filters['sort_direction'])) ? " " : $filters['sort_direction'];
        }
        // $query = DB::connection('mysql2')->table('campaign_advertisings')->select($fields);
        // $query->where('seller_id', $filters['seller_id']);

        $query = DB::connection('mysql2')->table('ads_campaigns')->select($fields);
        $join = 'leftJoin';
        if($forgraph) $join = "rightJoin";
        $query = $query->$join('campaign_advertisings', function ($leftJoin) use($filters, $forgraph) {
            $leftJoin->on('ads_campaigns.campaignid', '=', 'campaign_advertisings.campaignid')
            ->where(function($query) use ($filters){
                if($filters['filter_date_start'] != "" AND $filters['filter_date_start']!=" "){
                    $query->where('campaign_advertisings.posted_date', '>=', $filters['filter_date_start']);
                }
                if($filters['filter_date_end'] != "" AND $filters['filter_date_end']!=" "){
                    $query->where('campaign_advertisings.posted_date', '<=', $filters['filter_date_end']);
                }
            });
            if($filters['filter_imp'] != "" AND $filters['filter_imp']!=" "){
                $arr = explode('-', trim($filters['filter_imp']));
                if(trim($arr[0]) != "" AND trim($arr[1]) != "")
                    $leftJoin->havingRaw('(sum(campaign_advertisings.impressions) >= '. $arr[0].' AND sum(campaign_advertisings.impressions) <= '. $arr[1].')');
                else if(trim($arr[0]) != "") $leftJoin->havingRaw('(sum(campaign_advertisings.impressions) >= '. $arr[0].')');
                else if(trim($arr[1]) != "") $leftJoin->havingRaw('(sum(campaign_advertisings.impressions) <= '. $arr[1].')');
            }
            if($filters['filter_clicks'] != "" AND $filters['filter_clicks']!=" "){
                $arr = explode('-', trim($filters['filter_clicks']));
                if(trim($arr[0]) != "" AND trim($arr[1]) != "")
                    $leftJoin->havingRaw('(sum(campaign_advertisings.clicks) >= '. $arr[0].' AND sum(campaign_advertisings.clicks) <= '. $arr[1].')');
                else if($arr[0] != "") $leftJoin->havingRaw('(sum(campaign_advertisings.clicks) >='. $arr[0].')');
                else if($arr[1] != "") $leftJoin->havingRaw('(sum(campaign_advertisings.clicks) <='. $arr[1].')');
            }
            if($filters['filter_ctr'] != "" AND $filters['filter_ctr']!=" "){
                $arr = explode('-', trim($filters['filter_ctr']));
                if(trim($arr[0]) != "" AND trim($arr[1]) != "")
                    $leftJoin->havingRaw('(if(sum(campaign_advertisings.impressions) > 0, (sum(campaign_advertisings.clicks)/sum(campaign_advertisings.impressions))*100,0) >='. $arr[0].' AND if(sum(campaign_advertisings.impressions) > 0, sum(campaign_advertisings.clicks)/sum(campaign_advertisings.impressions),0) <='.$arr[1].')');
                else if($arr[0] != "") $leftJoin->havingRaw('(if(sum(campaign_advertisings.impressions) > 0, (sum(campaign_advertisings.clicks)/sum(campaign_advertisings.impressions))*100,0) >='. $arr[0].')');
                else if($arr[1] != "") $leftJoin->havingRaw('(if(sum(campaign_advertisings.impressions) > 0, (sum(campaign_advertisings.clicks)/sum(campaign_advertisings.impressions))*100,0) <='. $arr[1].')');
            }
            if($filters['filter_total_spend'] != "" AND $filters['filter_total_spend']!=" "){
                $arr = explode('-', trim($filters['filter_total_spend']));
                if(trim($arr[0]) != "" AND trim($arr[1]) != "")
                    $leftJoin->havingRaw('(sum(campaign_advertisings.total_spend) >= '. $arr[0].' AND sum(campaign_advertisings.total_spend) <= '. $arr[1].')');
                else if($arr[0] != "") $leftJoin->havingRaw('(sum(campaign_advertisings.total_spend) >='. $arr[0].')');
                else if($arr[1] != "") $leftJoin->havingRaw('(sum(campaign_advertisings.total_spend) <='. $arr[1].')');
            }
            if($filters['filter_avg_cpc'] != "" AND $filters['filter_avg_cpc']!=" "){
                $arr = explode('-', trim($filters['filter_avg_cpc']));
                if(trim($arr[0]) != "" AND trim($arr[1]) != "")
                    $leftJoin->havingRaw('(if(sum(campaign_advertisings.clicks) > 0, sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.clicks),0) >='. $arr[0].' AND if(sum(campaign_advertisings.clicks) > 0, sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.clicks),0) <='.$arr[1].')');
                else if($arr[0] != "") $leftJoin->havingRaw('(if(sum(campaign_advertisings.clicks) > 0, sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.clicks),0) >='. $arr[0].')');
                else if($arr[1] != "") $leftJoin->havingRaw('(if(sum(campaign_advertisings.clicks) > 0, sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.clicks),0) <='. $arr[1].')');
            }
            if($filters['filter_revenue'] != "" AND $filters['filter_revenue']!=" "){
                $arr = explode('-', trim($filters['filter_revenue']));
                if(trim($arr[0]) != "" AND trim($arr[1]) != "")
                    $leftJoin->havingRaw('(sum(campaign_advertisings.attributedsales30d) >= '. $arr[0].' AND sum(campaign_advertisings.attributedsales30d) <= '. $arr[1].')');
                else if(trim($arr[0]) != "") $leftJoin->havingRaw('(sum(campaign_advertisings.attributedsales30d) >= '. $arr[0].')');
                else if(trim($arr[1]) != "") $leftJoin->havingRaw('(sum(campaign_advertisings.attributedsales30d) <= '. $arr[1].')');
            }
            if($filters['filter_conv_rate'] != "" AND $filters['filter_conv_rate']!=" "){
                $arr = explode('-', trim($filters['filter_conv_rate']));
                if(trim($arr[0]) != "" AND trim($arr[1]) != "")
                    $leftJoin->havingRaw('(if(sum(campaign_advertisings.clicks) > 0, sum(campaign_advertisings.attributedconversions30dsamesku)/sum(campaign_advertisings.clicks),0) >='. $arr[0].' AND if(sum(campaign_advertisings.clicks) > 0, sum(campaign_advertisings.attributedconversions30dsamesku)/sum(campaign_advertisings.clicks),0) <='.$arr[1].')');
                else if($arr[0] != "") $leftJoin->havingRaw('(if(sum(campaign_advertisings.clicks) > 0, sum(campaign_advertisings.attributedconversions30dsamesku)/sum(campaign_advertisings.clicks),0) >='. $arr[0].')');
                else if($arr[1] != "") $leftJoin->havingRaw('(if(sum(campaign_advertisings.clicks) > 0, sum(campaign_advertisings.attributedconversions30dsamesku)/sum(campaign_advertisings.clicks),0) <='. $arr[1].')');
            }
            if($filters['filter_acos'] != "" AND $filters['filter_acos']!=" "){
                $arr = explode('-', trim($filters['filter_acos']));
                if(trim($arr[0]) != "" AND trim($arr[1]) != "")
                    $leftJoin->havingRaw('(if(sum(campaign_advertisings.attributedsales30d) > 0, (sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.attributedsales30d))*100,0) >='. $arr[0].' AND if(sum(campaign_advertisings.attributedsales30d) > 0, (sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.attributedsales30d))*100,0) <='.$arr[1].')');
                else if($arr[0] != "") $leftJoin->havingRaw('(if(sum(campaign_advertisings.attributedsales30d) > 0, (sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.attributedsales30d))*100,0) >='. $arr[0].')');
                else if($arr[1] != "") $leftJoin->havingRaw('(if(sum(campaign_advertisings.attributedsales30d) > 0, (sum(campaign_advertisings.total_spend)/sum(campaign_advertisings.attributedsales30d))*100,0) <='. $arr[1].')');
            }
            if(trim($filters['filter_recommendation']) != ""){
                $filters['filter_recommendation'] = trim($filters['filter_recommendation'],',');
                $arr = explode(',', trim($filters['filter_recommendation']));
                foreach ($arr as $key => $value) {
                    $leftJoin->where('campaign_advertisings.recommendation', 'like', '%'.trim($value).'%');
                }  
            }
        });
        if(trim($filters['filter_ad_group']) != ""){
            $query = $query->join('ads_campaign_ad_groups', function ($join) use($filters) {
                $join->on('ads_campaigns.campaignid', '=', 'ads_campaign_ad_groups.campaignid');
                if(trim($filters['filter_ad_group']) != ""){
                    $arr = explode(',', trim($filters['filter_ad_group']));
                    $join->whereIn('ads_campaign_ad_groups.name', $arr);
                }
            });
        }
        $query->where('ads_campaigns.seller_id', $filters['seller_id']);

        // ---->
        // $query->where(function($query) use ($filters){
        //     if($filters['filter_date_start'] != "" AND $filters['filter_date_start']!=" "){
        //         $query->where('posted_date', '>=', $filters['filter_date_start']);
        //     }
        //     if($filters['filter_date_end'] != "" AND $filters['filter_date_end']!=" "){
        //         $query->where('posted_date', '<=', $filters['filter_date_end']);
        //     }
        // });


        // if($filters['filter_imp'] != "" AND $filters['filter_imp']!=" "){
        //     $arr = explode('-', trim($filters['filter_imp']));
        //     if(trim($arr[0]) != "" AND trim($arr[1]) != "")
        //         $query->havingRaw('(sum(impressions) >= '. $arr[0].' AND sum(impressions) <= '. $arr[1].')');
        //     else if(trim($arr[0]) != "") $query->havingRaw('(sum(impressions) >= '. $arr[0].')');
        //     else if(trim($arr[1]) != "") $query->havingRaw('(sum(impressions) <= '. $arr[1].')');
        // }
        // --->

        // if($filters['filter_imp'] != "" AND $filters['filter_imp']!=" "){
        //     $arr = explode('-', trim($filters['filter_imp']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('impressions', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('impressions', '<=', $arr[1]);
        //     });
        // }

        // --->
        // if($filters['filter_clicks'] != "" AND $filters['filter_clicks']!=" "){
        //     $arr = explode('-', trim($filters['filter_clicks']));
        //     if(trim($arr[0]) != "" AND trim($arr[1]) != "")
        //         $query->havingRaw('(sum(clicks) >= '. $arr[0].' AND sum(clicks) <= '. $arr[1].')');
        //     else if($arr[0] != "") $query->havingRaw('(sum(clicks) >='. $arr[0].')');
        //     else if($arr[1] != "") $query->havingRaw('(sum(clicks) <='. $arr[1].')');
        // }
        //-->

        // if($filters['filter_clicks'] != "" AND $filters['filter_clicks']!=" "){
        //     $arr = explode('-', trim($filters['filter_clicks']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('clicks', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('clicks', '<=', $arr[1]);
        //     });
        // }

        // ---->
        // if($filters['filter_ctr'] != "" AND $filters['filter_ctr']!=" "){
        //     $arr = explode('-', trim($filters['filter_ctr']));
        //     if(trim($arr[0]) != "" AND trim($arr[1]) != "")
        //         $query->havingRaw('(if(sum(impressions) > 0, (sum(clicks)/sum(impressions))*100,0) >='. $arr[0].' AND if(sum(impressions) > 0, sum(clicks)/sum(impressions),0) <='.$arr[1].')');
        //     else if($arr[0] != "") $query->havingRaw('(if(sum(impressions) > 0, (sum(clicks)/sum(impressions))*100,0) >='. $arr[0].')');
        //     else if($arr[1] != "") $query->havingRaw('(if(sum(impressions) > 0, (sum(clicks)/sum(impressions))*100,0) <='. $arr[1].')');
        // }
        // ---->

        // if($filters['filter_ctr'] != "" AND $filters['filter_ctr']!=" "){
        //     $arr = explode('-', trim($filters['filter_ctr']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('ctr', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('ctr', '<=', $arr[1]);
        //     });
        // }

        // ---->
        // if($filters['filter_total_spend'] != "" AND $filters['filter_total_spend']!=" "){
        //     $arr = explode('-', trim($filters['filter_total_spend']));
        //     if(trim($arr[0]) != "" AND trim($arr[1]) != "")
        //         $query->havingRaw('(sum(total_spend) >= '. $arr[0].' AND sum(total_spend) <= '. $arr[1].')');
        //     else if($arr[0] != "") $query->havingRaw('(sum(total_spend) >='. $arr[0].')');
        //     else if($arr[1] != "") $query->havingRaw('(sum(total_spend) <='. $arr[1].')');
        // }
        // ---->

        // if($filters['filter_total_spend'] != "" AND $filters['filter_total_spend']!=" "){
        //     $arr = explode('-', trim($filters['filter_total_spend']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('total_spend', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('total_spend', '<=', $arr[1]);
        //     });
        // }

        // ---->
        // if($filters['filter_avg_cpc'] != "" AND $filters['filter_avg_cpc']!=" "){
        //     $arr = explode('-', trim($filters['filter_avg_cpc']));
        //     if(trim($arr[0]) != "" AND trim($arr[1]) != "")
        //         $query->havingRaw('(if(sum(clicks) > 0, sum(total_spend)/sum(clicks),0) >='. $arr[0].' AND if(sum(clicks) > 0, sum(total_spend)/sum(clicks),0) <='.$arr[1].')');
        //     else if($arr[0] != "") $query->havingRaw('(if(sum(clicks) > 0, sum(total_spend)/sum(clicks),0) >='. $arr[0].')');
        //     else if($arr[1] != "") $query->havingRaw('(if(sum(clicks) > 0, sum(total_spend)/sum(clicks),0) <='. $arr[1].')');
        // }
        // --->

        // if($filters['filter_avg_cpc'] != "" AND $filters['filter_avg_cpc']!=" "){
        //     $arr = explode('-', trim($filters['filter_avg_cpc']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('average_cpc', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('average_cpc', '<=', $arr[1]);
        //     });
        // }

        // ------>
        // if($filters['filter_acos'] != "" AND $filters['filter_acos']!=" "){
        //     $arr = explode('-', trim($filters['filter_acos']));
        //     if(trim($arr[0]) != "" AND trim($arr[1]) != "")
        //         $query->havingRaw('(if(sum(attributedsales30d) > 0, (sum(total_spend)/sum(attributedsales30d))*100,0) >='. $arr[0].' AND if(sum(attributedsales30d) > 0, (sum(total_spend)/sum(attributedsales30d))*100,0) <='.$arr[1].')');
        //     else if($arr[0] != "") $query->havingRaw('(if(sum(attributedsales30d) > 0, (sum(total_spend)/sum(attributedsales30d))*100,0) >='. $arr[0].')');
        //     else if($arr[1] != "") $query->havingRaw('(if(sum(attributedsales30d) > 0, (sum(total_spend)/sum(attributedsales30d))*100,0) <='. $arr[1].')');
        // }
        // --->

        // if($filters['filter_acos'] != "" AND $filters['filter_acos']!=" "){
        //     $arr = explode('-', trim($filters['filter_acos']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('acos', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('acos', '<=', $arr[1]);
        //     });
        // }

                //DB::raw('SUM(IF(clicks > 0,((attributedconversions30dsamesku/clicks)),0)) as cr'), 
        // --->
        // if($filters['filter_conv_rate'] != "" AND $filters['filter_conv_rate']!=" "){
        //     $arr = explode('-', trim($filters['filter_conv_rate']));
        //     if(trim($arr[0]) != "" AND trim($arr[1]) != "")
        //         $query->havingRaw('(if(sum(clicks) > 0, sum(attributedconversions30dsamesku)/sum(clicks),0) >='. $arr[0].' AND if(sum(clicks) > 0, sum(attributedconversions30dsamesku)/sum(clicks),0) <='.$arr[1].')');
        //     else if($arr[0] != "") $query->havingRaw('(if(sum(clicks) > 0, sum(attributedconversions30dsamesku)/sum(clicks),0) >='. $arr[0].')');
        //     else if($arr[1] != "") $query->havingRaw('(if(sum(clicks) > 0, sum(attributedconversions30dsamesku)/sum(clicks),0) <='. $arr[1].')');
        // }
        // --->

        // if($filters['filter_conv_rate'] != "" AND $filters['filter_conv_rate']!=" "){
        //     $arr = explode('-', trim($filters['filter_conv_rate']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('cr', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('cr', '<=', $arr[1]);
        //     });
        // }

        // ----->
        // if($filters['filter_revenue'] != "" AND $filters['filter_revenue']!=" "){
        //     $arr = explode('-', trim($filters['filter_revenue']));
        //     if(trim($arr[0]) != "" AND trim($arr[1]) != "")
        //         $query->havingRaw('(sum(attributedsales30d) >= '. $arr[0].' AND sum(attributedsales30d) <= '. $arr[1].')');
        //     else if(trim($arr[0]) != "") $query->havingRaw('(sum(attributedsales30d) >= '. $arr[0].')');
        //     else if(trim($arr[1]) != "") $query->havingRaw('(sum(attributedsales30d) <= '. $arr[1].')');
        // }
        // ---->

        // if($filters['filter_revenue'] != "" AND $filters['filter_revenue']!=" "){
        //     $arr = explode('-', trim($filters['filter_revenue']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where(DB::raw("(other_sku_units_product_sales_within_1_week_of_click + same_sku_units_product_sales_within_1_week_of_click)"), '>=',  $arr[0]);
        //         if($arr[1] != "") $query->where(DB::raw("(other_sku_units_product_sales_within_1_week_of_click + same_sku_units_product_sales_within_1_week_of_click)"), '<=', $arr[1]);
        //      });
        // }

        if(trim($filters['filter_country']) != ""){
            $arr = explode(',', $filters['filter_country']);
            $c_arr = array();
            for($x = 0; $x<count($arr); $x++){
                $a = explode('|', $arr[$x]);
                $c_arr[] = strtolower($a[0]);
            }
            // $arr = explode('-', trim($filters['filter_country']));
            // if(count($arr)>0) $query->whereIn('country', $arr);
            $query->whereIn('ads_campaigns.country', $c_arr);
        }
        if(trim($filters['filter_camp_name']) != ""){
            $arr = explode(',', trim($filters['filter_camp_name']));
            $query->whereIn('ads_campaigns.name', $arr);
        }
        // ---->
        // if(trim($filters['filter_ad_group']) != ""){
        //     $arr = explode(',', trim($filters['filter_ad_group']));
        //     $query->whereIn('ad_group_name', $arr);
        // }
        // --->
        if(trim($filters['filter_camp_type']) != ""){
            $arr = explode(',', trim($filters['filter_camp_type']));
            $query->whereIn('ads_campaigns.targetingtype', $arr);
        }

        if(trim($filters['filter_show_enabled']) != ""){
            $enabled = $filters['filter_show_enabled'];
        }else{
            //show all
            $enabled = 0;
        }
        if($enabled == 1)
            $query->where('ads_campaigns.state', 'enabled');

        // --->
        // if(trim($filters['filter_recommendation']) != ""){
        //     $arr = explode(',', trim($filters['filter_recommendation']));
        //     $query->where(function($query) use ($arr){
        //         foreach ($arr as $key => $value) {
        //             $query->where('recommendation', 'like', '%'.trim($value).'%');
        //         }
        //     });    
        // }
        // --->

        // if(trim($filters['filter_keyword']) != ""){
        //     $query->where('keyword', 'like', '%' . trim($filters['filter_keyword']) . '%');
        // }
        if($count){
            $q = $query->groupBy('campaignid')->get()->count();
            // $q = $query->get()->count();
        }else{
            if($forgraph){
                $q = $query->groupBy('posted_date')->get();
                // $q = $query->get();
            }else{
                if(trim($sort_column) != "" AND trim($sort_direction) != "")
                    $sort_column = explode('as', $sort_column);
                    $query->orderBy(DB::raw(trim($sort_column[0])), $sort_direction);
                    $query->orderBy(DB::raw('ads_campaigns.name'), 'asc');
                if(trim($skip) != '' AND trim($take) != '')
                    $query->skip($skip)->take($take);
                $q = $query->groupBy('ads_campaigns.campaignid')->get();
            }
        }
        //print_r(DB::connection('mysql2')->getQueryLog());
        return $q;

    }

    public function getGroupByadgroup($filters,$req){
        $id = $req->id;

        if (isset($req->forQuery)) {
            switch ($req->forQuery) {
                case 'keyword':
                    $fields = [
                            DB::raw('campaign_advertisings.type as type'),
                            DB::raw('campaign_advertisings.id as row_id'),
                            DB::raw('ads_campaign_keywords.keywordid as id'),
                            DB::raw('ads_campaign_keywords.matchtype as match_type'),
                            DB::raw('ads_campaign_keywords.keywordtext as rowtitle'), 
                            DB::raw('SUM(campaign_advertisings.impressions) as impressions'), 
                            DB::raw('SUM(campaign_advertisings.clicks) as clicks'), 
                            // DB::raw('SUM(campaign_advertisings.ctr) as ctr'), 
                            DB::raw('SUM(campaign_advertisings.attributedsales30d) as attributedsales30d'), 
                            DB::raw('SUM(campaign_advertisings.attributedconversions30dsamesku) as attributedconversions30dsamesku'), 
                            // DB::raw('SUM(IF(campaign_advertisings.clicks > 0,((campaign_advertisings.attributedconversions30dsamesku/campaign_advertisings.clicks)),0)) as cr'), 
                            DB::raw('SUM(campaign_advertisings.total_spend) as total_spend'), 
                            // DB::raw('SUM(campaign_advertisings.average_cpc) as average_cpc'), 
                            // DB::raw('SUM(campaign_advertisings.acos) as acos'),
                            DB::raw('ads_campaign_keywords.bid as bid'),
                            DB::raw('ads_campaign_keywords.max_bid_recommendation as max_bid_recommendation'),
                            DB::raw('ads_campaign_keywords.recommendation as recommendation')
                            //DB::raw('SUM(bid) as bid'), 
                            //DB::raw('SUM(max_bid_recommendation) as max_bid_recommendation')
                        ];
                    break;

                case 'search_term':
                    $fields = [
                            'type',
                            'id as row_id',
                            DB::raw('CONCAT("id_",REPLACE(customer_search_term," ","_"),keyword_id) as id'),
                            DB::raw('"" as match_type'),
                            DB::raw('customer_search_term as rowtitle'), 
                            DB::raw('SUM(impressions) as impressions'), 
                            DB::raw('SUM(clicks) as clicks'), 
                            // DB::raw('SUM(ctr) as ctr'), 
                            DB::raw('SUM(attributedsales30d) as attributedsales30d'), 
                            DB::raw('SUM(attributedconversions30dsamesku) as attributedconversions30dsamesku'), 
                            // DB::raw('SUM(IF(clicks > 0,((attributedconversions30dsamesku/clicks)),0)) as cr'), 
                            DB::raw('SUM(total_spend) as total_spend'), 
                            // DB::raw('SUM(average_cpc) as average_cpc'), 
                            // DB::raw('SUM(acos) as acos'),
                            'bid',
                            'max_bid_recommendation',
                            'recommendation'
                            //DB::raw('SUM(bid) as bid'), 
                            //DB::raw('SUM(max_bid_recommendation) as max_bid_recommendation')
                        ];
                break;

                default:
                    # code...
                    break;
            }
        }else{

            $fields = [
                    'campaign_advertisings.type as type',
                    'campaign_advertisings.id as row_id',
                    DB::raw('ads_campaign_ad_groups.adgroupid as id'),
                    DB::raw('"" as match_type'),
                    DB::raw('ads_campaign_ad_groups.name as rowtitle'),
                    DB::raw('SUM(campaign_advertisings.impressions) as impressions'), 
                    DB::raw('SUM(campaign_advertisings.clicks) as clicks'), 
                    // DB::raw('SUM(campaign_advertisings.ctr) as ctr'), 
                    DB::raw('SUM(campaign_advertisings.attributedsales30d) as attributedsales30d'), 
                    DB::raw('SUM(campaign_advertisings.attributedconversions30dsamesku) as attributedconversions30dsamesku'), 
                    // DB::raw('SUM(IF(campaign_advertisings.clicks > 0,((campaign_advertisings.attributedconversions30dsamesku/campaign_advertisings.clicks)),0)) as cr'), 
                    DB::raw('SUM(campaign_advertisings.total_spend) as total_spend'), 
                    // DB::raw('SUM(campaign_advertisings.average_cpc) as average_cpc'), 
                    // DB::raw('SUM(campaign_advertisings.acos) as acos'),
                    'ads_campaign_ad_groups.defaultbid as bid',
                    'ads_campaign_ad_groups.max_bid_recommendation as max_bid_recommendation',
                    'ads_campaign_ad_groups.recommendation as recommendation'
                    //DB::raw('SUM(bid) as bid'), 
                    //DB::raw('SUM(max_bid_recommendation) as max_bid_recommendation')
                ];

        }

        if(isset($req->forQuery)){
            if($req->forQuery == 'keyword'){
                $query = DB::connection('mysql2')->table('ads_campaign_keywords')->select($fields);
                $query = $query->leftJoin('campaign_advertisings', function ($leftJoin) use($filters) {
                    $leftJoin->on('ads_campaign_keywords.keywordid', '=', 'campaign_advertisings.keyword_id')
                    ->where(function($query) use ($filters){
                        if($filters['filter_date_start'] != "" AND $filters['filter_date_start']!=" "){
                            $query->where('campaign_advertisings.posted_date', '>=', $filters['filter_date_start']);
                        }
                        if($filters['filter_date_end'] != "" AND $filters['filter_date_end']!=" "){
                            $query->where('campaign_advertisings.posted_date', '<=', $filters['filter_date_end']);
                        }
                    });
                });

                // if(trim($filters['filter_recommendation']) != ""){
                //     $arr = explode(',', trim($filters['filter_recommendation']));
                //     $query->where(function($query) use ($arr){
                //         foreach ($arr as $key => $value) {
                //             $query->where('ads_campaign_keywords.recommendation', 'like', '%'.trim($value).'%');
                //         }
                //     });    
                // }

                if(trim($filters['filter_show_enabled']) != ""){
                    $enabled = $filters['filter_show_enabled'];
                }else{
                    //show all
                    $enabled = 0;
                }
                if($enabled == 1)
                    $query->where('ads_campaign_keywords.state', 'enabled');

                $query->where('ads_campaign_keywords.seller_id', $filters['seller_id']);
            }else{
                $query = DB::connection('mysql2')->table('campaign_advertisings')->select($fields);
                $query->where('campaign_advertisings.seller_id', $filters['seller_id']);
                $query = $query->where(function($query) use ($filters){
                    if($filters['filter_date_start'] != "" AND $filters['filter_date_start']!=" "){
                        $query->where('campaign_advertisings.posted_date', '>=', $filters['filter_date_start']);
                    }
                    if($filters['filter_date_end'] != "" AND $filters['filter_date_end']!=" "){
                        $query->where('campaign_advertisings.posted_date', '<=', $filters['filter_date_end']);
                    }
                });
            }
        }else{
            $query = DB::connection('mysql2')->table('ads_campaign_ad_groups')->select($fields);
            $query = $query->leftJoin('campaign_advertisings', function ($leftJoin) use($filters) {
                $leftJoin->on('ads_campaign_ad_groups.adgroupid', '=', 'campaign_advertisings.adgroupid')
                ->where(function($query) use ($filters){
                    if($filters['filter_date_start'] != "" AND $filters['filter_date_start']!=" "){
                        $query->where('campaign_advertisings.posted_date', '>=', $filters['filter_date_start']);
                    }
                    if($filters['filter_date_end'] != "" AND $filters['filter_date_end']!=" "){
                        $query->where('campaign_advertisings.posted_date', '<=', $filters['filter_date_end']);
                    }
                });
            });
            // if(trim($filters['filter_recommendation']) != ""){
            //     $query = $query->leftJoin('ads_campaign_keywords', function ($leftJoin) use($filters) {
            //         $leftJoin->on('ads_campaign_ad_groups.adgroupid', '=', 'ads_campaign_keywords.adgroupid');
            //         if(trim($filters['filter_recommendation']) != ""){
            //             $arr = explode(',', trim($filters['filter_recommendation']));
            //             $leftJoin->where(function($query) use ($arr){
            //                 foreach ($arr as $key => $value) {
            //                     $query->where('ads_campaign_keywords.recommendation', 'like', '%'.trim($value).'%');
            //                 }
            //             });    
            //         }
            //     });
            // }

            //$query = DB::connection('mysql2')->table('campaign_advertisings')->select($fields);
            $query->where('ads_campaign_ad_groups.seller_id', $filters['seller_id']);
            if(trim($filters['filter_ad_group']) != ""){
                $arr = explode(',', trim($filters['filter_ad_group']));
                $query->whereIn('ads_campaign_ad_groups.name', $arr);
            }

            if(trim($filters['filter_show_enabled']) != ""){
                $enabled = $filters['filter_show_enabled'];
            }else{
                //show all
                $enabled = 0;
            }
            if($enabled == 1)
                $query->where('ads_campaign_ad_groups.state', 'enabled');

               
        }
        
        

        if(isset($req->forQuery)){
            switch ($req->forQuery) {
                case 'keyword':
                    $query->where('ads_campaign_keywords.adgroupid', $req->overRideId);
                    break;

                case 'search_term':
                    $query->where('keyword_id', $req->overRideId);
                    break;
                
                default:
                    # code...
                    break;
            }
            
        }else{
            $query->where('ads_campaign_ad_groups.campaignid', $id);
        }
        if(isset($req->forQuery)){
            if($req->forQuery != 'keyword'){
                $query->where(function($query) use ($filters){
                    if($filters['filter_date_start'] != "" AND $filters['filter_date_start']!=" "){
                        $query->where('campaign_advertisings.posted_date', '>=', $filters['filter_date_start']);
                    }
                    if($filters['filter_date_end'] != "" AND $filters['filter_date_end']!=" "){
                        $query->where('campaign_advertisings.posted_date', '<=', $filters['filter_date_end']);
                    }
                });
            }
        }
        
        // if($filters['filter_imp'] != "" AND $filters['filter_imp']!=" "){
        //     $arr = explode('-', trim($filters['filter_imp']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('impressions', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('impressions', '<=', $arr[1]);
        //     });
        // }
        // if($filters['filter_clicks'] != "" AND $filters['filter_clicks']!=" "){
        //     $arr = explode('-', trim($filters['filter_clicks']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('clicks', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('clicks', '<=', $arr[1]);
        //     });
        // }
        // if($filters['filter_ctr'] != "" AND $filters['filter_ctr']!=" "){
        //     $arr = explode('-', trim($filters['filter_ctr']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('ctr', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('ctr', '<=', $arr[1]);
        //     });
        // }
        // if($filters['filter_total_spend'] != "" AND $filters['filter_total_spend']!=" "){
        //     $arr = explode('-', trim($filters['filter_total_spend']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('total_spend', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('total_spend', '<=', $arr[1]);
        //     });
        // }
        // if($filters['filter_avg_cpc'] != "" AND $filters['filter_avg_cpc']!=" "){
        //     $arr = explode('-', trim($filters['filter_avg_cpc']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('average_cpc', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('average_cpc', '<=', $arr[1]);
        //     });
        // }
        // if($filters['filter_acos'] != "" AND $filters['filter_acos']!=" "){
        //     $arr = explode('-', trim($filters['filter_acos']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('acos', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('acos', '<=', $arr[1]);
        //     });
        // }
        // if($filters['filter_conv_rate'] != "" AND $filters['filter_conv_rate']!=" "){
        //     $arr = explode('-', trim($filters['filter_conv_rate']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where('cr', '>=', $arr[0]);
        //         if($arr[1] != "") $query->where('cr', '<=', $arr[1]);
        //     });
        // }
        // if($filters['filter_revenue'] != "" AND $filters['filter_revenue']!=" "){
        //     $arr = explode('-', trim($filters['filter_revenue']));
        //     $query->where(function($query) use ($arr){
        //         if($arr[0] != "") $query->where(DB::raw("(other_sku_units_product_sales_within_1_week_of_click + same_sku_units_product_sales_within_1_week_of_click)"), '>=',  $arr[0]);
        //         if($arr[1] != "") $query->where(DB::raw("(other_sku_units_product_sales_within_1_week_of_click + same_sku_units_product_sales_within_1_week_of_click)"), '<=', $arr[1]);
        //      });
        // }
        // if(trim($filters['filter_country']) != ""){
        //     $arr = explode(',', $filters['filter_country']);
        //     $c_arr = array();
        //     for($x = 0; $x<count($arr); $x++){
        //         $a = explode('|', $arr[$x]);
        //         $c_arr[] = strtolower($a[0]);
        //     }
        //     // $arr = explode('-', trim($filters['filter_country']));
        //     // if(count($arr)>0) $query->whereIn('country', $arr);
        //     $query->whereIn('country', $c_arr);
        // }
        // if(trim($filters['filter_camp_name']) != ""){
        //     $arr = explode(',', trim($filters['filter_camp_name']));
        //     $query->whereIn('campaign_name', $arr);
        // }
        
        // if(trim($filters['filter_ad_group']) != ""){
        //     $arr = explode(',', trim($filters['filter_ad_group']));
        //     $query->whereIn('campaign_advertisings.ad_group_name', $arr);
        // }
        // if(trim($filters['filter_camp_type']) != ""){
        //     $arr = explode(',', trim($filters['filter_camp_type']));
        //     $query->whereIn('campaign_advertisings.type', $arr);
        // }
        // if(trim($filters['filter_keyword']) != ""){
        //     $query->where('keyword', 'like', '%' . trim($filters['filter_keyword']) . '%');
        // }
        
        //$q = $query->groupBy('adgroupid','ad_group_name')->get();

        if(isset($req->forQuery)){
            switch ($req->forQuery) {
                case 'keyword':
                    $query = $query->orderBy('ads_campaign_keywords.keywordtext', 'asc');
                    $q = $query->groupBy('ads_campaign_keywords.keywordid')->orderBy('impressions', 'desc')->get();
                    break;

                case 'search_term':
                    $q = $query->groupBy('customer_search_term')->get();
                    break;
                
                default:
                    # code...
                    break;
            }
            
        }else{
            $query = $query->orderBy('ads_campaign_ad_groups.name', 'asc');
            $q = $query->groupBy('ads_campaign_ad_groups.adgroupid')->orderBy('impressions', 'desc')->get();

        }
        
        // $q = DB::connection('mysql2')->table('campaign_advertisings')->select($fields)->where('seller_id', $seller_id)->where('campaignid', $campaignid)->groupBy()->get();

        return $q;
    }
}
