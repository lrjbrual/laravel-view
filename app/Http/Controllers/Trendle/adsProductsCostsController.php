<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Product;
use App\ProductCost;
use App\UniversalModel;
use App\CampaignAdMegaReport;
use App\CampaignAdvertising;
use App\AdsCampaignProduct;
use Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;
use App\AmazonSellerDetail as Amz;
use App\AdsCampaignAdGroup;
use App\AdsCampaign;
use App\AdsCampaignKeyword;

class adsProductsCostsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('checkStripe');
    }

    public function index(){
        $seller_id = Auth::user()->seller_id;
        $data = $this->callBaseSubscriptionName($seller_id);
        /*  Added by jason 7/17/17
         *  Redirect if user is not logged in with amazon
        */
        $amz = new Amz;
        $amzChecker = 0;
        $amzChecker = $amz->where('seller_id', $seller_id)->first();

        if (($data->base_subscription == '' || $data->base_subscription == 'XS')&& Auth::user()->seller->is_trial == 0) {
            return redirect('subscription');
        }

        $amzChecker = 1; //trest
        if (!$amzChecker) {
            return view('trendle.adsproductscost.index')
                ->with('bs',$data->base_subscription)
                ->with('amzChecker', $amzChecker);
        }

    	return view('trendle.adsproductscost.index')
                    ->with('bs',$data->base_subscription)
                    ->with('amzChecker', $amzChecker);
    }

    public function getAdProdCostData(){
        $seller_id = Auth::user()->seller_id;
        $products = DB::connection('mysql2')->table('products')
                    ->where('products.seller_id', $seller_id)
                    ->where('products.sale_price', '>', '0')
                    ->leftJoin('product_costs', function ($leftJoin) {
                        $leftJoin->on('products.id', '=', 'product_costs.product_id')
                        ->where('product_costs.created_at', '=', DB::raw("(select max(`created_at`) from product_costs where product_costs.product_id = products.id)"));
                    })
                    ->get(['products.id','products.sku','products.asin','products.country','products.product_name','products.price','products.sale_price','products.advice_margin','products.time_period','products.estimate_fees','product_costs.unit_cost']);
        $data = array();
        foreach ($products as $p) {
            $arr = array();
            $arr['rowId'] = $p->id;
            $arr['DT_RowId'] = $p->id;
            //warning will show if there's changes
            //please don't delete this comment '<i class="fa fa-warning orange_color hand warningChangesPopUp"></i> '.
            $arr[] = strtoupper($p->country);
            $arr[] = $p->sku;
            $arr[] = $p->asin;
            $arr[] = $p->product_name;
            $arr[] = $p->price;
            $arr[] = $p->sale_price;
            $arr[] = $p->estimate_fees;
            $arr[] = (is_null($p->unit_cost)) ? 0 : $p->unit_cost;
            $arr[] = round(abs($p->sale_price - $p->estimate_fees - $p->unit_cost),2);
            $esp_per = round((abs($p->sale_price - $p->estimate_fees - $p->unit_cost) / $p->sale_price)*100, 2);
            $arr[] = $esp_per."%";
            $arr[] = $p->advice_margin."%";
            $select = '<select id="time_period'.$p->id.'" class="time_period" onchange="javascript:changeTimePeriod('.$p->id.')">';
            $select .= '<option value="0">-- Please Select Time Period --</option>';
            $select .= '<option value="14" ';
            $select .= ($p->time_period == 14) ? 'selected' : '';
            $select .= '> 14 days </option>';
            $select .= '<option value="30" ';
            $select .= ($p->time_period == 30) ? 'selected' : '';
            $select .='> 30 days </option>';
            $select .= '<option value="60" ';
            $select .= ($p->time_period == 60) ? 'selected' : '';
            $select .= '> 60 days </option>';
            $select .= '<option value="90" ';
            $select .= ($p->time_period == 90) ? 'selected' : '';
            $select .= '> 90 days </option>';
            if( !($p->time_period == 90) AND !($p->time_period == 60) AND !($p->time_period == 30) AND !($p->time_period == 14) AND !($p->time_period == 0) ){
                $select .= '<option value="'.$p->time_period.'" > Custom '.$p->time_period.' days </option>';
            }
            $select .= '</select>';
            $arr[] = $select;
            $arr[] = $p->advice_margin-$esp_per."%";
            $data[] = $arr;
        }

        echo json_encode($data);
    }

    public function updateUnitCost(Request $req){
        $seller_id = Auth::user()->seller_id;
        $this->update_unit_cost($req->row_id, $req->unitCost);
        $p = new Product;
        $p = $p->where('id', $req->row_id)
            ->get()->first();
        $est = round(($p->sale_price - $p->estimate_fees - $req->unitCost),2);
        $est_per = round((abs($p->sale_price - $p->estimate_fees - $req->unitCost) / $p->sale_price)*100, 2);
        $est_per = $est_per."%";

        echo $req->unitCost."|".$est."|".$est_per;
        $this->updateAdsPerfMaxRecommendation($req->row_id);

    }

    private function update_unit_cost($pid, $unit_cost){
        $univ = new UniversalModel;
        $d = date('Y-m-d ');
        $where = ['product_id'=>$pid,
                  'whereBetween' => ['created_at',[$d." 00:00:01", $d." 23:59:59"]]
                ];
        $data = $univ->getRecords('product_costs', ['*'], $where, array(), true);
        $id = 0;
        if(count($data)>0){
            foreach ($data as $value) {
                $id = $value->id;
            }
            $pc = ProductCost::find($id);
            $pc->unit_cost = $unit_cost;
            $pc->updated_at = date('Y-m-d H:i:s');
            $pc->save();
        }else{
            $pc = new ProductCost;
            $pc->product_id = $pid;
            $pc->unit_cost = $unit_cost;
            $pc->created_at = date('Y-m-d H:i:s');
            $pc->save();
        }
    }

    public function updateMinimumMargin(Request $req){
        $minProfMarg =  trim($req->minimumProfitMargin, '%');
        $id = $req->row_id;
        $this->updateProdMinMargin($id, $minProfMarg);
        echo $minProfMarg;
        $this->updateAdsPerfMaxRecommendation($req->row_id);
    }

    private function updateProdMinMargin($id, $minProfMarg){
        $p = Product::find($id);
        $p->advice_margin = $minProfMarg;
        $p->updated_at = date('Y-m-d H:i:s');
        $p->save();
    }

    public function updateConversionTimePeriod(Request $req){
        $this->updateConvTimePer($req->id, $req->time_period);
        $this->updateAdsPerfMaxRecommendation($req->id);
    }

    private function updateConvTimePer($id, $time_period){
        $p = Product::find($id);
        $p->time_period = $time_period;
        $p->updated_at = date('Y-m-d H:i:s');
        $p->save();
    }

    public function updateAdsPerfMaxRecommendation($product_id){
        $seller_id = Auth::user()->seller_id;
        $univ = new UniversalModel;
        $p = new Product;
        $p = $p->where('id', $product_id)->get()->first();
        $ma = new AdsCampaignProduct;
        // $mega_ads = DB::connection('mysql2')->table('ads_campaign_products')
        //         ->leftJoin('ads_campaigns', 'ads_campaigns.campaignid', '=', 'ads_campaign_products.campaignid')
        $mega_ads = $ma->where('asin', $p->asin)
                ->where('asin', $p->asin)
                ->where('seller_id', $seller_id)
                ->where('country', $p->country)
                ->get(['asin', 'campaignid', 'adgroupid', 'country']);

        foreach ($mega_ads as $value) {
            $ad = new CampaignAdvertising;
            $from_date = Carbon::today()->addMinutes(5)->subDays($p->time_period);
            $ads = $ad->where('seller_id', $seller_id)
                ->where('campaignid', $value->campaignid)
                ->where('adgroupid', $value->adgroupid)
                ->where('country', $value->country)
                ->where('posted_date', '>=', $from_date)
                ->select('id', 'average_cpc', 'acos', 'attributedsales30d', 'total_spend')
                ->get();
            $ads_ids = array();
            $cpc = 0;
            $acos = 0;
            $attributedsales30d = 0;
            $total_spend = 0;

            CampaignAdvertising::where('seller_id', $seller_id)
                ->where('campaignid', $value->campaignid)
                ->where('adgroupid', $value->campaignid)
                ->where('country', $value->country)
                ->update(['max_bid_recommendation'=> 0]);
            AdsCampaignAdGroup::where('seller_id', $seller_id)
                ->where('campaignid', $value->campaignid)
                ->where('adgroupid', $value->campaignid)
                ->where('country', $value->country)
                ->update(['max_bid_recommendation'=> 0]);
            AdsCampaignKeyword::where('seller_id', $seller_id)
                ->where('campaignid', $value->campaignid)
                ->where('adgroupid', $value->campaignid)
                ->where('country', $value->country)
                ->update(['max_bid_recommendation'=> 0]);

            if(count($ads) > 0){
                foreach ($ads as $value2) {
                    $ads_ids[] = $value2->id;
                    $cpc += $value2->average_cpc;
                    $attributedsales30d += $value2->attributedsales30d;
                    $total_spend += $value2->total_spend;
                }
                $acos  = ($attributedsales30d == 0 OR $total_spend == 0) ? 0 : (($total_spend/$attributedsales30d) * 100);
                if($acos == 0 OR $cpc == 0){
                    $max_bid = 0;
                }else{
                    $cpc = $cpc / count($ads_ids);
                    $acos = $acos / count($ads_ids);
                    $max_bid = abs($p->sale_price - $p->estimate_fees - $p->unit_cost);
                    $max_bid = ($max_bid == 0 OR $p->sale_price == 0) ? 0 : ($max_bid / $p->sale_price);
                    $max_bid = ($max_bid == 0 OR $cpc == 0) ? 0 : (($max_bid / $cpc) * $acos);
                    $max_bid = ($max_bid == 0) ? 0 : round($max_bid, 2);
                }

                //$ads = CampaignAdvertising::whereIn('id', $ads_ids)->update(['max_bid_recommendation'=> $max_bid]);
                CampaignAdvertising::where('seller_id', $seller_id)
                ->where('campaignid', $value->campaignid)
                ->where('adgroupid', $value->campaignid)
                ->where('country', $value->country)
                ->update(['max_bid_recommendation'=> $max_bid]);
            
                AdsCampaignKeyword::where('seller_id', $seller_id)
                    ->where('campaignid', $value->campaignid)
                    ->where('adgroupid', $value->adgroupid)
                    ->where('country', $value->country)
                    ->update(['max_bid_recommendation' => $max_bid]);
            
                AdsCampaignAdGroup::where('seller_id', $seller_id)
                    ->where('campaignid', $value->campaignid)
                    ->where('adgroupid', $value->adgroupid)
                    ->where('country', $value->country)
                    ->update(['max_bid_recommendation' => $max_bid]);
                
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

    public function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header){
                    for($i = 0; count($row) > $i; $i++){
                        $row[$i] = strtolower($row[$i]);
                        $row[$i] = str_replace(' ', '_', $row[$i]);
                        $row[$i] = str_replace('-', '_', $row[$i]);
                    }
                    $header = $row;
                }
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    public function importCsv(Request $request)
    {
        ini_set("max_execution_time", 0);  // on
        //check if there is a file
        if($request->hasFile('import_file')){
            //get the real path
            $path = $request->file('import_file')->getRealPath();
            $ext = $request->file('import_file')->getClientOriginalExtension();
            //check file format
            if( !($ext == 'xlsx') AND !($ext == 'csv') ){
                echo 'Invalid file format!';
                //return back()->with('error','Invalid file format.');
            }

            //convert csv/xlsx to array
            $data = $this->csvToArray($path);
            $flag = 0;
            //save to db
            if(count($data) >0 AND !empty($data)){
                for ($i = 0; $i < count($data); $i++)
                {
                    $is_update = 0;
                    $pid = !(isset($data[$i]['product_id'])) ? 0 : trim($data[$i]['product_id']);
                    if(is_numeric((int)($pid))){
                        if($pid == 0){
                            continue;
                        }
                    }else{
                        continue;
                    }
                    $unit_cost = !(isset($data[$i]['unit_cost'])) ? 0 : trim($data[$i]['unit_cost']);
                    $apmpm = !(isset($data[$i]['advertised_product_minimum_profit_margin'])) ? 0 : trim($data[$i]['advertised_product_minimum_profit_margin']);
                    $crctp = !(isset($data[$i]['conversion_rate_calculation_time_period'])) ? 0 : trim($data[$i]['conversion_rate_calculation_time_period']);
                    $crctp = trim($crctp, '%');
                    if(is_numeric((int)$apmpm)){
                        if($apmpm >= 0){
                            $this->updateProdMinMargin($pid, $apmpm);
                            $is_update++;
                        }
                    }
                    if(is_numeric((int)$crctp)){
                        if($crctp >= 0 ){
                            $this->updateConvTimePer($pid, $crctp);
                            $is_update++;
                        }
                    }
                    if(is_numeric((int)$unit_cost)){
                        if($unit_cost >= 0){
                            $this->update_unit_cost($pid, $unit_cost);
                            $is_update++;
                        }
                    }
                    if($is_update > 0){
                        $this->updateAdsPerfMaxRecommendation($pid);
                        $flag ++;
                    }
                }
                if($flag > 0)
                    echo $flag.' records successfully updated.';
                    //return back()->with('success',$flag.' records successfully updated.');
                else
                    echo 'Invalid file header.';
                    //return back()->with('error','Invalid file header.');
            }else{
                echo 'File is Empty.';
                //return back()->with('error','File is Empty.');
            }
        }else{
            echo 'Please Check your file, Something is wrong there.';
        //return back()->with('error','Please Check your file, Something is wrong there.');
        }
    }

    public function export_table()
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=".time().".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('Product ID', 'Country', 'SKU', 'ASIN', 'Product Name', 'Price on Amazon', 'Sale Price', 'Est. Amazon Fees', 'Unit Cost', 'Est. Profit', 'Est. Profit (%)', 'Advertised Product Minimum Profit Margin', 'Conversion Rate Calculation Time period');

        $seller_id = Auth::user()->seller_id;
        $products = DB::connection('mysql2')->table('products')
                    ->where('products.seller_id', $seller_id)
                    ->where('products.sale_price', '>', '0')
                    ->leftJoin('product_costs', function ($leftJoin) {
                        $leftJoin->on('products.id', '=', 'product_costs.product_id')
                        ->where('product_costs.created_at', '=', DB::raw("(select max(`created_at`) from product_costs where product_costs.product_id = products.id)"));
                    })
                    ->get(['products.id','products.sku','products.asin','products.country','products.product_name','products.price','products.sale_price','products.advice_margin','products.time_period','products.estimate_fees','product_costs.unit_cost']);

        $callback = function() use ($products, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($products as $p) {
                $arr = array();
                $arr[] = $p->id;
                $arr[] = strtoupper($p->country);
                $arr[] = $p->sku;
                $arr[] = $p->asin;
                $arr[] = $p->product_name;
                $arr[] = $p->price;
                $arr[] = $p->sale_price;
                $arr[] = $p->estimate_fees;
                $arr[] = (is_null($p->unit_cost)) ? 0 : $p->unit_cost;
                $arr[] = round(abs($p->sale_price - $p->estimate_fees - $p->unit_cost),2);
                $esp_per = round((abs($p->sale_price - $p->estimate_fees - $p->unit_cost) / $p->sale_price)*100, 2);
                $arr[] = $esp_per."%";
                $arr[] = $p->advice_margin."%";
                $arr[] = $p->time_period;

                fputcsv($file, $arr);
            }
            fclose($file);
        };
        return \Response::stream($callback, 200, $headers);
    }
}
