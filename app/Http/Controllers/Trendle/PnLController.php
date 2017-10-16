<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UniversalModel;
use App\FinancialEventsReport;
use App\MarketplaceAssign;
use App\Billing;
use Auth;
use Illuminate\Support\Facades\DB;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;

class PnLController extends Controller
{
    protected $FinancialEventsReportmodel = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkStripe');
    }
    public function index()
    {
      $seller_id = Auth::user()->seller_id;
      $preferred_currency = $this->getPreferedCurrencyForThisSeller();
      if(is_null($preferred_currency))
      {
        $preferred_currency = 'gbp';
      }
    	$mkp_c = $this->getCountryListForThisSeller();
      $data = $this->callBaseSubscriptionName($seller_id);
      // if($mkp_c==false) $mkp_c = array();
        return view('trendle.pnl.index')->with('mkp_c',$mkp_c)->with('pref_cur', strtoupper($preferred_currency))
              ->with('bs',$data->base_subscription);
    }
    public function getRevenueGraphData(Request $request){
      ini_set('memory_limit', '512M');
      $date_start = $request->get('date_from');
      $date_end = $request->get('date_to');
      $seller_id = Auth::user()->seller_id;
      $preferred_currency = $this->getPreferedCurrencyForThisSeller();

		if((!isset($date_start))||($date_start=='')){
			$date_start = date('Y-m-d',strtotime('-30 days'));
		}else{
			$date_start = date('Y-m-d', strtotime($date_start));
		}
		if((!isset($date_end))||($date_end=='')){
			$date_end = date('Y-m-d');
		}else{
			$date_end = date('Y-m-d', strtotime($date_end));
		}
    if(((!$request->get('date_from'))||($request->get('date_from')=='')) && ((isset($date_end))||($date_end!='')) ){
      $date_start = date_create($request->get('date_to'));
      $date_start = date_format($date_start,'Y-m-d');
      $date_start = date('Y-m-d',strtotime('-30 days',strtotime($date_start)));
      // $date_end = date('Y-m-d', strtotime($request->get('date_to')));
    }
		//setting date for graph
    	$pnl_graph = array();
    	$ds = $date_start;
    	while (strtotime($ds) <= strtotime($date_end)) {
    		$pnl_graph[$ds] = 0;
            $ds = date ("Y-m-d", strtotime("+1 day", strtotime($ds)));
		}

    	$q = new UniversalModel();
    	$fields = ['posted_date', 'type', 'currency', 'marketplace_name', 'price_amount', 'price_type', 'promotion_type', 'promotional_rebates', 'item_related_fee_type', 'item_related_fee_amount'];
    	//Order
    	$where = [
    		'whereBetween' => ['posted_date', [$date_start, $date_end]],
    		//'whereIn' => ['type', ['Order', 'Refund', 'Adjustment']], - to remove
    		'whereIn' => ['type', ['Order', 'Adjustment']],
    		'seller_id'=> $seller_id
    	];
    	$pnl_data = $q->getRecords('financial_events_report_raws', $fields, $where);
    	$pnl_graph = $this->calcGraphRevenue_from_raw($pnl_graph, $pnl_data, $preferred_currency);

    	//Loan
    	$where = [
    		'whereBetween' => ['posted_date', [$date_start, $date_end]],
    		'seller_id'=> $seller_id,
    		'sourcebusinesseventtype'=>'LoanAdvance'
    	];
    	$fields = ['posted_date', 'sourcebusinesseventtype', 'currency', 'amount'];
    	$pnl_data = $q->getRecords('financial_event_loan_servicings', $fields, $where);
    	$pnl_graph = $this->calcGraphRevenue_from_loan($pnl_graph, $pnl_data, $preferred_currency);

    	//round the values to 2 decimal places
    	foreach ($pnl_graph as $key => $value) {
    		$pnl_graph[$key] = round($value);
    	}

    	$data['graphrevenue'] = $pnl_graph;


      $pnl_graph = array();
      $this->setFinancialEventsReportModel(new FinancialEventsReport);
      $fmodel = $this->FinancialEventsReportmodel;
      $fields = 'price_amount';
      $where = [
        'date_from' => $date_start,
        'date_to' => $date_end,
        'seller_id'=> $seller_id,
        array(
          'type' => 'Refund',
          'price_type' => 'Principal',
        ),
        array(
          'type' => 'Adjustment',
          'price_type' => 'ReserveEvent',
        ),
        array(
          'type' => 'Adjustment',
          'price_type' => 'PostageBilling',
        ),
        array(
          'price_type' => 'CODItemCharge',
        ),
        array(
          'price_type' => 'CODOrderCharge',
        ),
        array(
          'price_type' => 'CODShippingCharge',
        ),
        array(
          'price_type' => 'RestockingFee',
        ),
        array(
          'item_related_fee_type' => 'FBAInboundTransportationFee',
        ),
        array(
          'item_related_fee_type' => 'FBADeliveryServicesFee',
        ),
        array(
          'item_related_fee_type' => 'CrossBorderFulfillmentFee',
        ),
        array(
          'item_related_fee_type' => 'FBAPlacementServiceFee',
        ),
        array(
          'item_related_fee_type' => 'FBAStorageFee',
        ),
        array(
          'item_related_fee_type' => 'FBALongTermStorageFee',
        ),
        array(
          'item_related_fee_type' => 'FBAOrderHandlingFulfillmentFee',
        ),
        array(
          'item_related_fee_type' => 'FBAPickPackFulfillmentFee',
        ),
        array(
          'item_related_fee_type' => 'FBAPremiumPlacementServiceFee',
        ),
        array(
          'item_related_fee_type' => 'BubblewrapFee',
        ),
        array(
          'item_related_fee_type' => 'LabelingFee',
        ),
        array(
          'item_related_fee_type' => 'OpaqueBaggingFee',
        ),
        array(
          'item_related_fee_type' => 'PolybaggingFee',
        ),
        array(
          'item_related_fee_type' => 'TapingFee',
        ),
        array(
          'item_related_fee_type' => 'FBAInventoryDisposalFee',
        ),
        array(
          'item_related_fee_type' => 'FBAInventoryReturnFee',
        ),
        array(
          'item_related_fee_type' => 'FBAUnplannedServiceFee',
        ),
        array(
          'item_related_fee_type' => 'FBAWeightHandlingFulfillmentFee',
        ),
        array(
          'item_related_fee_type' => 'GiftwrapChargeback',
        ),
        array(
          'item_related_fee_type' => 'JumpStartYourSalesFee',
        ),
        array(
          'item_related_fee_type' => 'JumpStartYourWebstoreFee',
        ),
        array(
          'item_related_fee_type' => 'FBAFulfillmentCODFee',
        ),
        array(
          'item_related_fee_type' => 'VariableClosingFee',
        ),
        array(
          'item_related_fee_type' => 'FBAUnplannedServiceFee_BarcodeUnscannable',
        ),
        array(
          'item_related_fee_type' => 'FBAUnplannedServiceFee_UnexpectedItemFound',
        ),
        array(
          'item_related_fee_type' => 'FBAUnplannedServiceFee_AdditionalQuantityShipped',
        ),
        array(
          'item_related_fee_type' => 'FBAUnplannedServiceFee_BarcodeLabelMissing',
        ),
        array(
          'item_related_fee_type' => 'FBAUnplannedServiceFee_LabelError',
        ),
        array(
          'item_related_fee_type' => 'FBAUnplannedServiceFee_PrepError',
        ),
        array(
          'item_related_fee_type' => 'FBAInventoryPlacementServiceFee',
        ),
        array(
          'item_related_fee_type' => 'AmazonProPageServiceFee',
        ),
        array(
          'item_related_fee_type' => 'CODChargeback',
        ),
        array(
          'item_related_fee_type' => 'CBATransactionFee',
        ),
        array(
          'item_related_fee_type' => 'BrandNeutralBoxFee',
        ),
        array(
          'item_related_fee_type' => 'PerItemFee',
        ),
        array(
          'item_related_fee_type' => 'PromotionsAndMerchandisingFee',
        ),
        array(
          'item_related_fee_type' => 'CatalogQualityServicesFee',
        ),
        array(
          'item_related_fee_type' => 'ReferralFeeGiftwrap',
        ),
        array(
          'item_related_fee_type' => 'ReferralFeeItemPrice',
        ),
        array(
          'item_related_fee_type' => 'ReferralFeeShippingPrice',
        ),
        array(
          'item_related_fee_type' => 'RefundAdminFee',
        ),
        array(
          'item_related_fee_type' => 'ReturnMerchandiseLabelFee',
        ),
        array(
          'item_related_fee_type' => 'SalesTaxServiceFee',
        ),
        array(
          'item_related_fee_type' => 'ShippingChargeback',
        ),
        array(
          'item_related_fee_type' => 'Subscription',
        ),
        array(
          'item_related_fee_type' => 'ListingTranslationFee',
        ),
        array(
          'item_related_fee_type' => 'RefundCommission',
        ),
        array(
          'item_related_fee_type' => 'Commission',
        ),
        array(
          'price_type' => 'Tax',
        ),
        array(
          'price_type' => 'TaxDiscount',
        ),
        array(
          'price_type' => 'CODItemTaxCharge',
        ),
        array(
          'price_type' => 'CODOrderTaxCharge',
        ),
        array(
          'price_type' => 'CODShippingTaxCharge',
        ),
        array(
          'price_type' => 'ShippingTax',
        ),
        array(
          'price_type' => 'GiftwrapTax',
        ),
        array(
          'price_type' => 'GenericDeduction',
        ),
        array(
          'price_type' => 'PaymentMethodFee',
        ),
        array(
          'price_type' => 'ExportCharge',
        ),
        array(
          'price_type' => 'Goodwill',
        ),

      ];

      $pnl_data = $fmodel->getFinancialEventsRawsSumByDateCurr($fields, $where);
        // dd($pnl_data);
      foreach($pnl_data as $pd){
        if(!isset($pnl_graph[$pd['ddate']])){
          $pnl_graph[$pd['ddate']]=0;
        }
        // dd($pd);
        $pnl_graph[$pd['ddate']]+=currency($pd['sum'], $pd['curr'], $preferred_currency, false);
      }


      $where = [
        'date_from' => $date_start,
        'date_to' => $date_end,
        'seller_id'=> $seller_id
      ];
      $pnl_data = $fmodel->getFinancialEventServiceFeeFeelistBySumByDateCurr($where);
        // dd($pnl_data);
      foreach($pnl_data as $pd){
        if(!isset($pnl_graph[$pd['ddate']])){
          $pnl_graph[$pd['ddate']]=0;
        }
        // dd($pd);
        $pnl_graph[$pd['ddate']]+=currency($pd['sum'], $pd['curr'], $preferred_currency, false);
      }

      $where = [
        'date_from' => $date_start,
        'date_to' => $date_end,
        'seller_id'=> $seller_id
      ];
      $pnl_data = $fmodel->getFinancialEventRentalTransactionFeeListBySumByDateCurr($where);
        // dd($pnl_data);
      foreach($pnl_data as $pd){
        if(!isset($pnl_graph[$pd['ddate']])){
          $pnl_graph[$pd['ddate']]=0;
        }
        // dd($pd);
        $pnl_graph[$pd['ddate']]+=currency($pd['sum'], $pd['curr'], $preferred_currency, false);
      }



      $where = [
        'date_from' => $date_start,
        'date_to' => $date_end,
        'seller_id'=> $seller_id
      ];
      $pnl_data = $fmodel->getFinancialEventRentalTransactionChargeListBySumByDateCurr($where);
        // dd($pnl_data);
      foreach($pnl_data as $pd){
        if(!isset($pnl_graph[$pd['ddate']])){
          $pnl_graph[$pd['ddate']]=0;
        }
        // dd($pd);
        $pnl_graph[$pd['ddate']]+=currency($pd['sum'], $pd['curr'], $preferred_currency, false);
      }


      $where = [
        'date_from' => $date_start,
        'date_to' => $date_end,
        'seller_id'=> $seller_id
      ];
      $pnl_data = $fmodel->getFinancialEventSAFETReimbursementItemListBySumByDateCurr($where);
        // dd($pnl_data);
      foreach($pnl_data as $pd){
        if(!isset($pnl_graph[$pd['ddate']])){
          $pnl_graph[$pd['ddate']]=0;
        }
        // dd($pd);
        $pnl_graph[$pd['ddate']]+=currency($pd['sum'], $pd['curr'], $preferred_currency, false);
      }

      $where = [
        'date_from' => $date_start,
        'date_to' => $date_end,
        'seller_id'=> $seller_id
      ];
      $pnl_data = $fmodel->getFinancialDebtRecoverySumByDateCurr($where);
      foreach($pnl_data as $pd){
        if(!isset($pnl_graph[$pd['ddate']])){
          $pnl_graph[$pd['ddate']]=0;
        }
        // dd($pd);
        $pnl_graph[$pd['ddate']]+=currency($pd['sum'], $pd['curr'], $preferred_currency, false);
      }


      $where = [
        'date_from' => $date_start,
        'date_to' => $date_end,
        'seller_id'=> $seller_id
      ];
      $pnl_data = $fmodel->getFinancialLoanServicingSumByDateCurr($where);
      foreach($pnl_data as $pd){
        if(!isset($pnl_graph[$pd['ddate']])){
          $pnl_graph[$pd['ddate']]=0;
        }
        // dd($pd);
        $pnl_graph[$pd['ddate']]+=currency($pd['sum'], $pd['curr'], $preferred_currency, false);
      }


      $where = [
        'date_from' => $date_start,
        'date_to' => $date_end,
        'seller_id'=> $seller_id
      ];
      $pnl_data = $fmodel->getFinancialRetroChargeSumByDateCurr($where);
      foreach($pnl_data as $pd){
        if(!isset($pnl_graph[$pd['ddate']])){
          $pnl_graph[$pd['ddate']]=0;
        }
        // dd($pd);
        $pnl_graph[$pd['ddate']]+=currency($pd['sum'], $pd['curr'], $preferred_currency, false);
      }

      $pnl_graph_profit = $pnl_graph;
      //fill missing dates compared to revgraph and calc profit
      foreach($data['graphrevenue'] as $key => $rev){
        if(!array_key_exists($key,$pnl_graph)){
          $pnl_graph[$key]=0;//for cost data..
          $pnl_graph_profit[$key]=0;
        }
        $pnl_graph_profit[$key] = $rev + $pnl_graph_profit[$key];
      }

      $data['graphprofit'] = $pnl_graph_profit;
      // $data['graphpcost'] = $pnl_graph; //uncomment this line to implement cost data.. plus add codes in init_graph() in js

    	echo json_encode($data);

    }
    //public function getRevenueGraph(){
    public function getRevenueTableData(Request $request){
      ini_set('memory_limit', '512M');
      $date_start = $request->get('date_from');
      $date_end = $request->get('date_to');
      $seller_id = Auth::user()->seller_id;
      $preferred_currency = $this->getPreferedCurrencyForThisSeller();
      $currency_symbol = $this->getPreferedCurrencySymbol($preferred_currency);

      if((!isset($date_start))||($date_start=='')){
        $date_start = date('Y-m-d',strtotime('-30 days'));
      }else{
        $date_start = date('Y-m-d', strtotime($date_start));
      }
      if((!isset($date_end))||($date_end=='')){
        $date_end = date('Y-m-d');
      }else{
        $date_end = date('Y-m-d', strtotime($date_end));
      }

    	$pnl_types = [
    		'Principal'=>0,
    		'Adjustments'=>0,
    		'FBAInventoryReimbursement'=>0,
    		'PostageRefund'=>0,
    		'Others'=>0,
    		'GiftWrap'=>0,
    		'ShippingCharge'=>0,
    		'ReturnShipping'=>0,
    		'FreeReplacementReturnShipping'=>0,
    		'LoanAdvance'=>0,
    		'ProviderCredit'=>0,
    		'Total'=>0
    	];
    	//setting the countries acquired by seller with total column
    	$mkps = $this->getCountryListForThisSeller();
    	if(count($mkps)==0){
    		echo "false";
    		die();
    	}
    	$pnl=array();
    	foreach ($mkps as $mkp) {
    		$pnl[$mkp->iso_3166_2] = $pnl_types;
    	}
    	$pnl['Total'] = $pnl_types;

    	$q = new UniversalModel();
    	$fields = ['posted_date', 'type', 'currency', 'marketplace_name', 'price_amount', 'price_type', 'promotion_type', 'promotional_rebates', 'item_related_fee_type', 'item_related_fee_amount'];
    	//Order
    	$where = [
    		'whereBetween' => ['posted_date', [$date_start, $date_end]],
    		'whereIn' => ['type', ['Order', 'Adjustment']],
    		'seller_id'=> $seller_id
    	];
    	$pnl_data = $q->getRecords('financial_events_report_raws', $fields, $where);
    	$pnl = $this->calcTableRevenue_from_raw($pnl, $pnl_data, $preferred_currency);

    	//Loan
    	$where = [
    		'whereBetween' => ['posted_date', [$date_start, $date_end]],
    		'seller_id'=> $seller_id,
    		'sourcebusinesseventtype'=>'LoanAdvance'
    	];
    	$fields = ['posted_date', 'sourcebusinesseventtype', 'currency', 'amount'];
    	$pnl_data = $q->getRecords('financial_event_loan_servicings', $fields, $where);
    	$pnl = $this->calcTableRevenue_from_loan($pnl, $pnl_data, $preferred_currency);

    	foreach ($mkps as $mkp) {
    		$pnl['Total']['Principal'] += $pnl[$mkp->iso_3166_2]['Principal'];
    		$pnl['Total']['Adjustments'] += $pnl[$mkp->iso_3166_2]['Adjustments'];
    		$pnl['Total']['FBAInventoryReimbursement'] += $pnl[$mkp->iso_3166_2]['FBAInventoryReimbursement'];
    		$pnl['Total']['PostageRefund'] += $pnl[$mkp->iso_3166_2]['PostageRefund'];
    		$pnl['Total']['Others'] += $pnl[$mkp->iso_3166_2]['Others'];
    		$pnl['Total']['GiftWrap'] += $pnl[$mkp->iso_3166_2]['GiftWrap'];
    		$pnl['Total']['ShippingCharge'] += $pnl[$mkp->iso_3166_2]['ShippingCharge'];
    		$pnl['Total']['ReturnShipping'] += $pnl[$mkp->iso_3166_2]['ReturnShipping'];
    		$pnl['Total']['FreeReplacementReturnShipping'] += $pnl[$mkp->iso_3166_2]['FreeReplacementReturnShipping'];
    		$pnl['Total']['LoanAdvance'] += $pnl[$mkp->iso_3166_2]['LoanAdvance'];
    		$pnl['Total']['ProviderCredit'] += $pnl[$mkp->iso_3166_2]['ProviderCredit'];
    		$pnl['Total']['Total'] += $pnl[$mkp->iso_3166_2]['Total'];
    	}
    	foreach ($pnl as $key => $pnl_v) {
    		foreach ($pnl_v as $key2 => $value) {
    			$pnl[$key][$key2] = round($value, 2);
    		}
    	}
    	$data['table'] = $pnl;
    	echo json_encode($data);
    }

    public function getCountryListForThisSeller(){
      $sid = Auth::user()->seller_id;
      $mkp_c = MarketplaceAssign::select('iso_3166_2')
      ->where('seller_id' , '=', $sid)
      ->join('marketplace_countries', 'marketplace_countries.marketplace_id', '=', 'marketplace_assigns.marketplace_id')
      ->join('countries', 'countries.id', '=', 'marketplace_countries.country_id')
      ->get();
      // if($mkp_c->count()>0)
      return $mkp_c;
      // else return false;
    }

	public function getPreferedCurrencyForThisSeller(){
		$sid = Auth::user()->seller_id;
		$q = Billing::select('*')
			->where('seller_id' , '=', $sid)
			->get()
      ->first();
    if(isset($q))
    {
		return $q->preferred_currency;
    }
	}

	public function getPreferedCurrencySymbol($preferred_currency){
		$q = DB::table('currencies')->select('symbol')->where('code', $preferred_currency)->get()->first()->symbol;
		return $q;
	}

    public function marketplacename_to_iso_3166_2($marketplace_name){
    	switch (strtolower($marketplace_name)) {
    		case 'amazon.co.uk': return 'GB'; break;
    		case 'amazon.fr': return 'FR'; break;
    		case 'amazon.de': return 'DE'; break;
    		case 'amazon.it': return 'IT'; break;
    		case 'amazon.es': return 'ES'; break;
    		case 'amazon.ca': return 'CA'; break;
    		case 'amazon.com': return 'US'; break;
    		default: return 'GB'; break;
    	}
    }

    public function calcGraphRevenue_from_raw($stack = array(), $hay, $preferred_currency){
    	foreach ($hay as $key => $value) {
    		switch ($value->price_type) {
    			case 'Principal':
    			case 'FBAInventoryReimbursement':
    			case 'PostageRefund':
    			case 'GiftWrap':
    			case 'ShippingCharge':
    			case 'ReturnShipping':
    			case 'FreeReplacementReturnShipping':
    				$date = date('Y-m-d', strtotime($value->posted_date));
    				if(array_key_exists($date, $stack)){
    					$stack[$date] += currency($value->price_amount, $value->currency, $preferred_currency, false);
    				}
    			break;
    		}
    	}
    	return $stack;
    }

    public function calcGraphRevenue_from_loan($stack=array(), $hay, $preferred_currency){
    	foreach ($hay as $key => $value) {
			$date = date('Y-m-d', strtotime($value->posted_date));
			if(array_key_exists($date, $stack)){
				$stack[$date] += currency($value->amount, $value->currency, $preferred_currency, false);
			}
    	}
    	return $stack;
    }

    public function calcTableRevenue_from_raw($stack, $hay, $preferred_currency){
    	foreach ($hay as $key => $value) {
    		switch ($value->price_type) {
    			case 'Principal':
    			case 'FBAInventoryReimbursement':
    			case 'PostageRefund':
    			case 'GiftWrap':
    			case 'ShippingCharge':
    			case 'ReturnShipping':
    			case 'FreeReplacementReturnShipping':
    			//case 'ProviderCredit':
    				$stack[$this->marketplacename_to_iso_3166_2($value->marketplace_name)][$value->price_type] += currency($value->price_amount, $value->currency, $preferred_currency, false);
    				$stack[$this->marketplacename_to_iso_3166_2($value->marketplace_name)]['Total'] += currency($value->price_amount, $value->currency, $preferred_currency, false);
    				if($value->price_type == 'FBAInventoryReimbursement' || $value->price_type=='PostageRefund'){
    					$stack[$this->marketplacename_to_iso_3166_2($value->marketplace_name)]['Adjustments'] += currency($value->price_amount, $value->currency, $preferred_currency, false);
    				}

    				if($value->price_type == 'Giftwrap' || $value->price_type=='ShippingCharge' ||
    					$value->price_type == 'ReturnShipping' || $value->price_type == 'FreeReplacementReturnShipping'){
    					$stack[$this->marketplacename_to_iso_3166_2($value->marketplace_name)]['Others'] += currency($value->price_amount, $value->currency, $preferred_currency, false);
    				}
    				break;
    		}
    	}
    	return $stack;
    }
    public function calcTableRevenue_from_loan($stack, $hay, $preferred_currency){
    	foreach ($hay as $key => $value) {
    		$stack[$this->currency_to_iso_3166_2($value->currency, $stack)][$value->sourcebusinesseventtype] += currency($value->amount, $value->currency, $preferred_currency, false);
    	}
    	return $stack;
    }
    public function currency_to_iso_3166_2($currency, $stack){
    	switch (strtoupper($currency)) {
    		case 'CAD':
    			return 'CA';
    			break;

    		case 'USD':
    			return 'US';
    			break;

    		case 'GBP':
    			return 'GB';
    			break;

    		case 'EUR':
    			if($stack['DE']['Principal']>0) return 'DE';
    			if($stack['FR']['Principal']>0) return 'FR';
    			else if($stack['IT']['Principal']>0) return 'IT';
    			else if($stack['ES']['Principal']>0) return 'ES';
    			else return 'DE';
    			break;

    		default:
    			return 'GB';
    			break;
    	}

    }

    public function iso_3166_2_to_marketplacename($mkpname){

      switch($mkpname){
        case 'GB':$r='Amazon.co.uk';break;
        case 'FR':$r='Amazon.fr';break;
        case 'DE':$r='Amazon.de';break;
        case 'IT':$r='Amazon.it';break;
        case 'ES':$r='Amazon.es';break;
        case 'US':$r='Amazon.com';break;
        case 'CA':$r='Amazon.ca';break;
        default:$r='Amazon.co.uk';break;
      }
      return $r;
    }


    public function makeRow_event($querycond=array(),$mkp_list=array(),$at,$hasDetails=true){
      $plusbutton='';
      if($hasDetails){
        $plusbutton='<span class="row-details row-details-close"></span>';
      }
      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '1';
      $data[0] = $plusbutton;
      $data[1] = '<b>'. (isset($querycond['event']) ? $querycond['event'] : '') .'</b>';
      $data[2] = '<b>Total</b>';
      $atc=3;
      $gtotal=0;
      foreach($mkp_list as $mkp_cc){
        $v_at=0;
        if(isset($at[$atc])){
          $v_at=$at[$atc];
        }

        $data[] = number_format($v_at, 2, '.', ',');
        $gtotal += $v_at;
        $atc++;
      }
      $data[] = number_format($gtotal, 2, '.', ',');
      return $data;

    }

    public function makeRow_total($mkp_list=array(),$datasrows){

      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '0';
      $data[0] = '';
      $data[1] = '<strong>Total Cost</strong>';
      $data[2] = '<b></b>';
      $atc=3;
      $gtotal=0;
      foreach($mkp_list as $mkp_cc){
        $v_at=0;
        foreach($datasrows as $dr){
          $v = str_replace(',', '', $dr[$atc]);
          $v_at+=$v;
        }
        $data[] = number_format($v_at, 2, '.', ',');
        $gtotal += $v_at;
        $atc++;
      }
      $data[]= number_format($gtotal, 2, '.', ',');
      return $data;

    }



    public function setFinancialEventsReportModel(FinancialEventsReport $m){
      $this->FinancialEventsReportmodel = $m;
    }

    public function manageRowDataByiso_3166_2($mkp_cc,$arraystack,$prefcurr){
      $marketplacename = $this->iso_3166_2_to_marketplacename($mkp_cc['iso_3166_2']);
      $v = $this->getSumFromArrayStack($arraystack,$marketplacename);
      $v = currency($v,$mkp_cc['currency_code'],$prefcurr,false);
      return $v;
    }

    public function manageRowDataBycurrency_code($mkp_cc,$arraystack,$prefcurr){
      $v = $this->getSumFromArrayStack($arraystack,$mkp_cc['currency_code']);
      $v = currency($v,$mkp_cc['currency_code'],$prefcurr,false);
      return $v;
    }




    public function makeRow_financialeventrawsgroupbymkp($querycond,$mkp_list,$prefcurr,$hasDetails=false){
      $plusbutton='';
      if($hasDetails){
        $plusbutton='<span class="row-details row-details-close"></span>';
      }
      $umodel = $this->FinancialEventsReportmodel;
      // $umodel = new FinancialEventsReport;
      $arraystack = $umodel->getFinancialEventsRawsSumByMKP('price_amount',$querycond);

      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '2';
      $data[0] = $plusbutton;
      $data[1] = '';
      $data[2] = (isset($querycond['price_type']) ? $querycond['price_type'] : '');
      $total = 0;
      foreach($mkp_list as $mkp_cc){
        $v = $this->manageRowDataByiso_3166_2($mkp_cc,$arraystack,$prefcurr);
        $data[]=number_format($v, 2, '.', ',');
        $total+=$v;
      }
      $data[]=number_format($total, 2, '.', ',');
      return $data;
    }

    public function makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_list,$prefcurr,$hasDetails=false){
      $plusbutton='';
      if($hasDetails){
        $plusbutton='<span class="row-details row-details-close"></span>';
      }
      $umodel = $this->FinancialEventsReportmodel;
      $arraystack = $umodel->getFinancialEventsRawsSumByMKP('price_amount',$querycond,'currency');
      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '2';
      $data[0] = $plusbutton;
      $data[1] = '';
      $data[2] = (isset($querycond['price_type']) ? $querycond['price_type'] : '');
      $total = 0;
      $currused=array();
      foreach($mkp_list as $mkp_cc){
        $v=0;
        if(!in_array($mkp_cc['currency_code'],$currused)){
          $v += $this->manageRowDataBycurrency_code($mkp_cc,$arraystack,$prefcurr);
          array_push($currused,$mkp_cc['currency_code']);
        }else{
          $v+=0;
        }
        $data[]=number_format($v, 2, '.', ',');
        $total+=$v;
      }
      $data[]=number_format($total, 2, '.', ',');
      return $data;
    }

    public function makeRow_financialeventdebtrecoverygroupbycurrency($querycond,$mkp_list,$prefcurr,$hasDetails=false){
      $plusbutton='';
      if($hasDetails){
        $plusbutton='<span class="row-details row-details-close"></span>';
      }
      $umodel = $this->FinancialEventsReportmodel;
      $arraystack = $umodel->getFinancialEventDebtRecoveryByPostedDateRange($querycond);
      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '2';
      $data[0] = $plusbutton;
      $data[1] = '';
      $data[2] = (isset($querycond['debtrecoverytype']) ? $querycond['debtrecoverytype'] : '');
      $total = 0;
      $currused=array();
      foreach($mkp_list as $mkp_cc){
        $v=0;
        if(!in_array($mkp_cc['currency_code'],$currused)){
          $v+= $this->manageRowDataBycurrency_code($mkp_cc,$arraystack,$prefcurr);
          array_push($currused,$mkp_cc['currency_code']);
        }else{
          $v+=0;
        }
        $data[]=number_format($v, 2, '.', ',');
        $total+=$v;
      }
      $data[]=number_format($total, 2, '.', ',');
      return $data;
    }

    public function makeRow_financialeventloanservicinggroupbycurrency($querycond,$mkp_list,$prefcurr,$hasDetails=false){
      $plusbutton='';
      if($hasDetails){
        $plusbutton='<span class="row-details row-details-close"></span>';
      }
      $umodel = $this->FinancialEventsReportmodel;
      $arraystack = $umodel->getFinancialEventLoanServicingByPostedDateRange($querycond);
      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '2';
      $data[0] = $plusbutton;
      $data[1] = '';
      $data[2] = (isset($querycond['sourcebusinesseventtype']) ? $querycond['sourcebusinesseventtype'] : '');
      $total = 0;
      $currused=array();
      foreach($mkp_list as $mkp_cc){
        $v=0;
        if(!in_array($mkp_cc['currency_code'],$currused)){
          $v+= $this->manageRowDataBycurrency_code($mkp_cc,$arraystack,$prefcurr);
          array_push($currused,$mkp_cc['currency_code']);
        }else{
          $v+=0;
        }
        $data[]=number_format($v, 2, '.', ',');
        $total+=$v;
      }
      $data[]=number_format($total, 2, '.', ',');
      return $data;
    }

    public function makeRow_financialeventretrochargegroupbymkp($querycond,$mkp_list,$prefcurr,$hasDetails=false){
      $plusbutton='';
      if($hasDetails){
        $plusbutton='<span class="row-details row-details-close"></span>';
      }
      $umodel = $this->FinancialEventsReportmodel;
      $arraystack = $umodel->getFinancialEventRetrochargeByPostedDateRange($querycond);
      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '2';
      $data[0] = $plusbutton;
      $data[1] = '';
      $data[2] = (isset($querycond['retrochargeeventtype']) ? $querycond['retrochargeeventtype'] : '');
      $total = 0;
      $currused=array();
      foreach($mkp_list as $mkp_cc){
        $v = $this->manageRowDataByiso_3166_2($mkp_cc,$arraystack,$prefcurr);
        $data[]=number_format($v, 2, '.', ',');
        $total+=$v;
      }
      $data[]=number_format($total, 2, '.', ',');
      return $data;
    }



    public function makeRow_feetype($querycond,$mkp_list,$prefcurr,$hasDetails=false){
      $plusbutton='';
      if($hasDetails){
        $plusbutton='<span class="row-details row-details-close"></span>';
      }

      $umodel = $this->FinancialEventsReportmodel;
      $arraystack = $umodel->getFinancialEventServiceFeeFeelistByPostedDateRange($querycond);
      $arraystack1 = $umodel->getFinancialEventRentalTransactionFeeListByPostedDateRange($querycond);
      $customquerycond['item_related_fee_type'] = (isset($querycond['feetype']) ? $querycond['feetype'] : '');
      $customquerycond['date_from'] = $querycond['date_from'];
      $customquerycond['date_to'] = $querycond['date_to'];
      $arraystack2 = $umodel->getFinancialEventsRawsSumByMKP('item_related_fee_amount',$customquerycond);

      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '2';
      $data[0] = $plusbutton;
      $data[1] = '';
      $data[2] = (isset($querycond['feetype']) ? $querycond['feetype'] : '');
      $total = 0;
      $currused=array();
      foreach($mkp_list as $mkp_cc){
        $v=0;
        $v += $this->manageRowDataByiso_3166_2($mkp_cc,$arraystack2,$prefcurr);
        if(!in_array($mkp_cc['currency_code'],$currused)){
          $v += $this->manageRowDataBycurrency_code($mkp_cc,$arraystack,$prefcurr,$currused);
          $v += $this->manageRowDataBycurrency_code($mkp_cc,$arraystack1,$prefcurr,$currused);
          array_push($currused,$mkp_cc['currency_code']);
        }else{
          $v+=0;
        }
        $data[]=number_format($v, 2, '.', ',');
        $total+=$v;
      }
      $data[]=number_format($total, 2, '.', ',');
      return $data;
    }



    public function makeRow_chargetype($querycond,$mkp_list,$prefcurr,$hasDetails=false){
      $plusbutton='';
      if($hasDetails){
        $plusbutton='<span class="row-details row-details-close"></span>';
      }

      $umodel = $this->FinancialEventsReportmodel;
      $arraystack = $umodel->getFinancialEventSAFETReimbursementItemListByPostedDateRange($querycond);
      $arraystack1 = $umodel->getFinancialEventRentalTransactionChargeListByPostedDateRange($querycond);
      $customquerycond['price_type'] = (isset($querycond['chargetype']) ? $querycond['chargetype'] : '');
      $customquerycond['date_from'] = $querycond['date_from'];
      $customquerycond['date_to'] = $querycond['date_to'];
      $arraystack2 = $umodel->getFinancialEventsRawsSumByMKP('price_amount',$customquerycond);

      $data["DT_RowId"] =  '';
      $data["rowtype"] =  '2';
      $data[0] = $plusbutton;
      $data[1] = '';
      $data[2] = (isset($querycond['chargetype']) ? $querycond['chargetype'] : '');
      $total = 0;
      $currused=array();
      foreach($mkp_list as $mkp_cc){
        $v=0;
        $v += $this->manageRowDataByiso_3166_2($mkp_cc,$arraystack2,$prefcurr);
        if(!in_array($mkp_cc['currency_code'],$currused)){
          $v += $this->manageRowDataBycurrency_code($mkp_cc,$arraystack,$prefcurr,$currused);
          $v += $this->manageRowDataBycurrency_code($mkp_cc,$arraystack1,$prefcurr,$currused);
          array_push($currused,$mkp_cc['currency_code']);
        }else{
          $v+=0;
        }
        $data[]=number_format($v, 2, '.', ',');
        $total+=$v;
      }
      $data[]=number_format($total, 2, '.', ',');
      return $data;
    }

    public function getSumFromArrayStack($arraystack,$needle){
      $v='0';
      foreach($arraystack as $ars){
        $b = array_search($needle,$ars,true);
        if($b!==false){
          $v=round($ars['sum'],2);
          break;
        }
      }
      return $v;
    }


    public function convertdatetoYmd($df,$dt){
      if((!isset($df))||($df=='')){
        $df = date('Y-m-d',strtotime('-30 days'));
      }else{
        $df = date('Y-m-d', strtotime($df));
      }
      if((!isset($dt))||($dt=='')){
        $dt = date('Y-m-d');
      }else{
        $dt = date('Y-m-d', strtotime($dt));
      }
      return array($df,$dt);
    }



    public function getPnLCostTable(Request $request){
      $this->setFinancialEventsReportModel(new FinancialEventsReport);
      $preferred_curr = $this->getPreferedCurrencyForThisSeller();//for prefered currency

      $date_from = $request->get('date_from');
      $date_to = $request->get('date_to');
      $dates = $this->convertdatetoYmd($date_from,$date_to);
      $date_from = $dates[0];
      $date_to = $dates[1];


      $mkp_c = $this->getCountryListForThisSeller();
      $row = array();
      $data = array();
      $rowd=array();
      $rowfortotal=array();


      // principal
      $querycond = array(
        'type'=>'Refund',
        'price_type'=>'Principal',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbymkp($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      //Refunds
      $querycond = array(
        'event'=>'Refunds',
      );
      $at=array();
      foreach($rowd as $rowe){
        $ic=3;
        foreach($mkp_c as $m){
          if(!isset($at[$ic])){$at[$ic]=0;}
          $v = str_replace(',', '', $rowe[$ic]);
          $at[$ic] += $v;
          $ic++;
        }
      }
      $data1 = $this->makeRow_event($querycond,$mkp_c,$at);
      $row[]=$data1;
      $rowfortotal[]=$data1;
      foreach($rowd as $rowe){
        $row[]=$rowe;
      }
      $rowd=array();

      // ReserveEvent
      $querycond = array(
        'type'=>'Adjustment',
        'price_type'=>'ReserveEvent',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbymkp($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // PostageBilling
      $querycond = array(
        'type'=>'Adjustment',
        'price_type'=>'PostageBilling',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbymkp($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;


      //positive value, so it is supposedly in revenue
      //Adjustment
      $querycond = array(
        'event'=>'Adjustment',
      );
      $at=array();
      foreach($rowd as $rowe){
        $ic=3;
        foreach($mkp_c as $m){
          if(!isset($at[$ic])){$at[$ic]=0;}
          $v = str_replace(',', '', $rowe[$ic]);
          $at[$ic] += $v;
          $ic++;
        }
      }
      $data1 = $this->makeRow_event($querycond,$mkp_c,$at);
      $row[]=$data1;
      $rowfortotal[]=$data1;
      foreach($rowd as $rowe){
        $row[]=$rowe;
      }
      $rowd=array();

      // CODItemCharge
      $querycond = array(
        // 'type'=>'Order',
        'chargetype'=>'CODItemCharge',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // CODOrderCharge
      $querycond = array(
        // 'type'=>'Order',
        'chargetype'=>'CODOrderCharge',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // CODShippingCharge
      $querycond = array(
        // 'type'=>'Order',
        'chargetype'=>'CODShippingCharge',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      //Promotions
      $querycond = array(
        'event'=>'Promotions',
      );
      $at=array();
      foreach($rowd as $rowe){
        $ic=3;
        foreach($mkp_c as $m){
          if(!isset($at[$ic])){$at[$ic]=0;}
          $v = str_replace(',', '', $rowe[$ic]);
          $at[$ic] += $v;
          $ic++;
        }
      }
      $data1 = $this->makeRow_event($querycond,$mkp_c,$at);
      $row[]=$data1;
      $rowfortotal[]=$data1;
      foreach($rowd as $rowe){
        $row[]=$rowe;
      }
      $rowd=array();

      // RestockingFee
      $querycond = array(
        'chargetype'=>'RestockingFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAInboundTransportationFee
      $querycond = array(
        'feetype'=>'FBAInboundTransportationFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBADeliveryServicesFee--cantsee
      $querycond = array(
        'feetype'=>'FBADeliveryServicesFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // CrossBorderFulfillmentFee--cantsee
      $querycond = array(
        'feetype'=>'CrossBorderFulfillmentFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAPlacementServiceFee
      $querycond = array(
        'feetype'=>'FBAPlacementServiceFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;


      // FBAStorageFee
      $querycond = array(
        'feetype'=>'FBAStorageFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBALongTermStorageFee
      $querycond = array(
        'feetype'=>'FBALongTermStorageFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAOrderHandlingFulfillmentFee
      $querycond = array(
        'feetype'=>'FBAOrderHandlingFulfillmentFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAPickPackFulfillmentFee
      $querycond = array(
        'feetype'=>'FBAPickPackFulfillmentFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;


      // FBAPremiumPlacementServiceFee
      $querycond = array(
        'feetype'=>'FBAPremiumPlacementServiceFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // BubblewrapFee
      $querycond = array(
        'feetype'=>'BubblewrapFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // LabelingFee
      $querycond = array(
        'feetype'=>'LabelingFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // OpaqueBaggingFee
      $querycond = array(
        'feetype'=>'OpaqueBaggingFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // PolybaggingFee
      $querycond = array(
        'feetype'=>'PolybaggingFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // TapingFee
      $querycond = array(
        'feetype'=>'TapingFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAInventoryDisposalFee
      $querycond = array(
        'feetype'=>'FBAInventoryDisposalFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAInventoryReturnFee
      $querycond = array(
        'feetype'=>'FBAInventoryReturnFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAUnplannedServiceFee
      $querycond = array(
        'feetype'=>'FBAUnplannedServiceFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAWeightHandlingFulfillmentFee
      $querycond = array(
        'feetype'=>'FBAWeightHandlingFulfillmentFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // GiftwrapChargeback
      $querycond = array(
        'feetype'=>'GiftwrapChargeback',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // JumpStartYourSalesFee
      $querycond = array(
        'feetype'=>'JumpStartYourSalesFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // JumpStartYourWebstoreFee
      $querycond = array(
        'feetype'=>'JumpStartYourWebstoreFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAFulfillmentCODFee
      $querycond = array(
        'feetype'=>'FBAFulfillmentCODFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // VariableClosingFee
      $querycond = array(
        'feetype'=>'VariableClosingFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAUnplannedServiceFee_BarcodeUnscannable
      $querycond = array(
        'feetype'=>'FBAUnplannedServiceFee_BarcodeUnscannable',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAUnplannedServiceFee_UnexpectedItemFound
      $querycond = array(
        'feetype'=>'FBAUnplannedServiceFee_UnexpectedItemFound',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAUnplannedServiceFee_AdditionalQuantityShipped
      $querycond = array(
        'feetype'=>'FBAUnplannedServiceFee_AdditionalQuantityShipped',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAUnplannedServiceFee_BarcodeLabelMissing
      $querycond = array(
        'feetype'=>'FBAUnplannedServiceFee_BarcodeLabelMissing',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAUnplannedServiceFee_LabelError
      $querycond = array(
        'feetype'=>'FBAUnplannedServiceFee_LabelError',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAUnplannedServiceFee_PrepError
      $querycond = array(
        'feetype'=>'FBAUnplannedServiceFee_PrepError',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAInventoryPlacementServiceFee
      $querycond = array(
        'feetype'=>'FBAInventoryPlacementServiceFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;


      //FBA Fees
      $querycond = array(
        'event'=>'FBA Fees',
      );
      $at=array();
      foreach($rowd as $rowe){
        $ic=3;
        foreach($mkp_c as $m){
          if(!isset($at[$ic])){$at[$ic]=0;}
          $v = str_replace(',', '', $rowe[$ic]);
          $at[$ic] += $v;
          $ic++;
        }
      }
      $data1 = $this->makeRow_event($querycond,$mkp_c,$at);
      $row[]=$data1;
      $rowfortotal[]=$data1;
      foreach($rowd as $rowe){
        $row[]=$rowe;
      }



      $rowd=array();
      // AmazonProPageServiceFee
      $querycond = array(
        'feetype'=>'AmazonProPageServiceFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // CODChargeback
      $querycond = array(
        'feetype'=>'CODChargeback',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // CBATransactionFee
      $querycond = array(
        'feetype'=>'CBATransactionFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // BrandNeutralBoxFee
      $querycond = array(
        'feetype'=>'BrandNeutralBoxFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // PerItemFee
      $querycond = array(
        'feetype'=>'PerItemFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // PromotionsAndMerchandisingFee
      $querycond = array(
        'feetype'=>'PromotionsAndMerchandisingFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // CatalogQualityServicesFee
      $querycond = array(
        'feetype'=>'CatalogQualityServicesFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ReferralFee
      $querycond = array(
        'feetype'=>'ReferralFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ReferralFeeGiftwrap
      $querycond = array(
        'feetype'=>'ReferralFeeGiftwrap',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ReferralFeeItemPrice
      $querycond = array(
        'feetype'=>'ReferralFeeGiftwrap',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ReferralFeeShippingPrice
      $querycond = array(
        'feetype'=>'ReferralFeeShippingPrice',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // RefundAdminFee
      $querycond = array(
        'feetype'=>'RefundAdminFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ReturnMerchandiseLabelFee
      $querycond = array(
        'feetype'=>'ReturnMerchandiseLabelFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // SalesTaxServiceFee
      $querycond = array(
        'feetype'=>'SalesTaxServiceFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ShippingChargeback
      $querycond = array(
        'feetype'=>'ShippingChargeback',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // Subscription
      $querycond = array(
        'feetype'=>'Subscription',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ListingTranslationFee
      $querycond = array(
        'feetype'=>'ListingTranslationFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // RefundCommission
      $querycond = array(
        'feetype'=>'RefundCommission',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // Commission
      $querycond = array(
        'feetype'=>'Commission',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      //Other Selling On Amazon Fees
      $querycond = array(
        'event'=>'Other Selling On Amazon Fees',
      );
      $at=array();
      foreach($rowd as $rowe){
        $ic=3;
        foreach($mkp_c as $m){
          if(!isset($at[$ic])){$at[$ic]=0;}
          $v = str_replace(',', '', $rowe[$ic]);
          $at[$ic] += $v;

          $ic++;
        }
      }
      $data1 = $this->makeRow_event($querycond,$mkp_c,$at);
      $row[]=$data1;
      $rowfortotal[]=$data1;
      foreach($rowd as $rowe){
        $row[]=$rowe;
      }

      $rowd=array();


      // Tax
      $querycond = array(
        'chargetype'=>'Tax',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // TaxDiscount
      $querycond = array(
        'chargetype'=>'TaxDiscount',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // CODItemTaxCharge
      $querycond = array(
        'chargetype'=>'CODItemTaxCharge',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // CODOrderTaxCharge
      $querycond = array(
        'chargetype'=>'CODOrderTaxCharge',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;


      // CODShippingTaxCharge
      $querycond = array(
        'chargetype'=>'CODShippingTaxCharge',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ShippingTax
      $querycond = array(
        'chargetype'=>'ShippingTax',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // GiftwrapTax
      $querycond = array(
        'chargetype'=>'GiftwrapTax',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;


      //Tax
      $querycond = array(
        'event'=>'Tax',
      );
      $at=array();
      foreach($rowd as $rowe){
        $ic=3;
        foreach($mkp_c as $m){
          if(!isset($at[$ic])){$at[$ic]=0;}
          $v = str_replace(',', '', $rowe[$ic]);
          $at[$ic] += $v;
          $ic++;
        }
      }
      $data1 = $this->makeRow_event($querycond,$mkp_c,$at);
      $row[]=$data1;
      $rowfortotal[]=$data1;
      foreach($rowd as $rowe){
        $row[]=$rowe;
      }
      $rowd=array();

      // GenericDeduction
      $querycond = array(
        'chargetype'=>'GenericDeduction',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // PaymentMethodFee
      $querycond = array(
        'chargetype'=>'PaymentMethodFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ExportCharge
      $querycond = array(
        'chargetype'=>'ExportCharge',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // DebtPayment
      //aaaaaaaaaaaaaaaa
      $querycond = array(
        'debtrecoverytype'=>'DebtPayment',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventdebtrecoverygroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // DebtPaymentFailure
      $querycond = array(
        'debtrecoverytype'=>'DebtPaymentFailure',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventdebtrecoverygroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // DebtAdjustment
      $querycond = array(
        'debtrecoverytype'=>'DebtAdjustment',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventdebtrecoverygroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // Goodwill
      $querycond = array(
        'chargetype'=>'Goodwill',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_chargetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // LoanPayment
      $querycond = array(
        'sourcebusinesseventtype'=>'LoanPayment',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventloanservicinggroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // LoanRefund
      $querycond = array(
        'sourcebusinesseventtype'=>'LoanRefund',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventloanservicinggroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // Retrocharge
      $querycond = array(
        'retrochargeeventtype'=>'Retrocharge',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventretrochargegroupbymkp($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // RetrochargeReversal
      $querycond = array(
        'retrochargeeventtype'=>'RetrochargeReversal',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventretrochargegroupbymkp($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      //Others
      $querycond = array(
        'event'=>'Others',
      );
      $at=array();
      foreach($rowd as $rowe){
        $ic=3;
        foreach($mkp_c as $m){
          if(!isset($at[$ic])){$at[$ic]=0;}
          $v = str_replace(',', '', $rowe[$ic]);
          $at[$ic] += $v;
          $ic++;
        }
      }
      $data1 = $this->makeRow_event($querycond,$mkp_c,$at);
      $row[]=$data1;
      $rowfortotal[]=$data1;
      foreach($rowd as $rowe){
        $row[]=$rowe;
      }
      $rowd=array();
















      // Commission
      $querycond = array(
        'feetype'=>'FBAPerOrderFulfillmentFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAPerUnitFulfillmentFee
      $querycond = array(
        'feetype'=>'FBAPerUnitFulfillmentFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FBAWeightBasedFee
      $querycond = array(
        'feetype'=>'FBAWeightBasedFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FixedClosingFee
      $querycond = array(
        'feetype'=>'FixedClosingFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // GetPaidFasterFee
      $querycond = array(
        'feetype'=>'GetPaidFasterFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // GiftwrapCommission
      $querycond = array(
        'feetype'=>'GiftwrapCommission',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // SalesTaxCollectionFee
      $querycond = array(
        'feetype'=>'SalesTaxCollectionFee',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ShippingHB
      $querycond = array(
        'feetype'=>'ShippingHB',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_feetype($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // ReturnShipping
      $querycond = array(
        'price_type'=>'ReturnShipping',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbymkp($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;



      // CS_ERROR_ITEMS
      $querycond = array(
        'price_type'=>'CS_ERROR_ITEMS',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // FREE_REPLACEMENT_REFUND_ITEMS
      $querycond = array(
        'price_type'=>'FREE_REPLACEMENT_REFUND_ITEMS',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // INCORRECT_FEES_ITEMS
      $querycond = array(
        'price_type'=>'INCORRECT_FEES_ITEMS',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // MISSING_FROM_INBOUND
      $querycond = array(
        'price_type'=>'MISSING_FROM_INBOUND',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // REMOVAL_ORDER_LOST
      $querycond = array(
        'price_type'=>'REMOVAL_ORDER_LOST',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // REVERSAL_REIMBURSEMENT
      $querycond = array(
        'price_type'=>'REVERSAL_REIMBURSEMENT',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // WAREHOUSE_DAMAGE
      $querycond = array(
        'price_type'=>'WAREHOUSE_DAMAGE',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // WAREHOUSE_DAMAGE_EXCEPTION
      $querycond = array(
        'price_type'=>'WAREHOUSE_DAMAGE_EXCEPTION',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // WAREHOUSE_LOST
      $querycond = array(
        'price_type'=>'WAREHOUSE_LOST',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;

      // WAREHOUSE_LOST_MANUAL
      $querycond = array(
        'price_type'=>'WAREHOUSE_LOST_MANUAL',
        'date_from'=>$date_from,
        'date_to'=>$date_to,
      );
      $data = $this->makeRow_financialeventrawsgroupbycurrency($querycond,$mkp_c,$preferred_curr);
      $rowd[]=$data;


      //Unclassified
      $querycond = array(
        'event'=>'Unclassified',
      );
      $at=array();
      foreach($rowd as $rowe){
        $ic=3;
        foreach($mkp_c as $m){
          if(!isset($at[$ic])){$at[$ic]=0;}
          $v = str_replace(',', '', $rowe[$ic]);
          $at[$ic] += $v;
          $ic++;
        }
      }
      $data1 = $this->makeRow_event($querycond,$mkp_c,$at);
      $row[]=$data1;
      $rowfortotal[]=$data1;
      foreach($rowd as $rowe){
        $row[]=$rowe;
      }
      $rowd=array();





      $datatotal = $this->makeRow_total($mkp_c,$rowfortotal);
      $row[]=$datatotal;





      $return["data"] = $row;
      return json_encode($return);
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
