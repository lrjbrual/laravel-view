<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;
use App\MarketplaceAssign;
use App\UniversalModel;
use App\CampaignAdMegaReport;
use App\CampaignAdvertising;
use App\Product;
use App\Log;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Seller;

class UpdateCampaignSPMegaController extends Controller
{
    private $seller_id;
    private $mkp='';

    public function index(){
      try {
    	ini_set('memory_limit', '1024M');
        ini_set("zlib.output_compression", 0);  // off
        ini_set("implicit_flush", 1);  // on
        ini_set("max_execution_time", 0);  // on
    	$total_records = 0;
    	$report_type = '_GET_SP_MEGA_REPORT_';
    	$univ = new UniversalModel();
		$mkp_q= new MarketplaceAssign();
		$tries = 0;

    	if( Input::get('seller_id') == null OR Input::get('seller_id') == "" )
        {
        	echo "<p style='color:red;'><b>SELLER ID is required as part of the parameter in the url to run this cron script</b></p>";
            exit;
        }

        $this->seller_id = trim(Input::get('seller_id'));

        //checker for invalid payment -Altsi
        $seller = Seller::find($this->seller_id);

        $now = Carbon::now();
        if(isset($seller->billing->payment_invalid_date)){
          $pid = $seller->billing->payment_invalid_date;
            if(!is_null($pid))
            {
              $invalidDate = Carbon::parse($pid);

              $diff = ($now)->diffInDays($invalidDate);

              if($diff >= 30)
              {
                echo "<p style='color:red;'><b>SELLER has invalid payment method for 30 days or more!</b></p>";
                exit;
              }
          }
        }
        //

    	$w = array();
    	if( Input::get('mkp') == null OR Input::get('mkp') == "" )
        {
        	echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
			exit();
        }
        $this->mkp = trim(Input::get('mkp'));

        $where  = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);
		$mkp_assign = $mkp_q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());

		if(count($mkp_assign) > 0)
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign SP MEGA Report for seller'.$this->seller_id.' mkp'.$this->mkp, true));
		else
			exit();
        //response for mail
        $time_start = time();
        $isError=false;
        $message = "Campaign SP MEGA Cron Successfully Fetch Data!";
        $response['time_start'] = date('Y-m-d H:i:s');
        $response['total_time_of_execution'] = 0;
        $response['message'] = $message;
        $response['isError'] = false;
        $response['tries'] = 0;
        $tries=0;

		$country_arr = array();
		foreach ($mkp_assign as $value) {
			if($value->marketplace_id == 1) $mkp = config('constant.amz_keys.na.marketplaces');
			if($value->marketplace_id == 2) $mkp = config('constant.amz_keys.eu.marketplaces');

			$mkp_id = $value->marketplace_id;
			$merchantId = $value->mws_seller_id;
			$MWSAuthToken = $value->mws_auth_token;

		    foreach ($mkp as $key => $mkp_data) {
		    	$report_ids = array();
		    	$tries++;
		    	$country = $key;
		    	$country_arr[$country] = array();

		    	// $w = array('seller_id'=> $this->seller_id, 'country'=>$country);
       			// $ff_data_count = $univ->getRecords('campaign_ad_mega_reports',array('*'),$w,array(),true);
		    	$start_date = Carbon::today()->addMinutes(5)->subDays(60);
                $end_date = Carbon::today()->addMinutes(4);
		    	$init = array(
					'merchantId'    => $merchantId,
		            'MWSAuthToken'  => $MWSAuthToken,		//mkp_auth_token
		            'country'		=> $country,			//mkp_country
		            'marketPlace'	=> $mkp_data['id'],		//seller marketplace id
		    		'start_date'	=> (string)$start_date,
		    		'end_date'		=> (string)$end_date,
		    		'name'			=> 'Manual Campaign Advertising API'
		    		);
		    	$amz = new MWSFetchReportClass();
                $amz->initialize($init);
                $report_ids[date('Y-m-d')." 00:05"] = $amz->request_RequestID($report_type);
		    	$country_arr[$country]['report_ids'] = $report_ids;
                $country_arr[$country]['init'] = $init;
		    }
		}
		foreach ($country_arr as $keys2 => $value) {
            $country = $keys2;

            foreach ($country_arr[$country]['report_ids'] as $key => $value) {
            	$amz = new MWSFetchReportClass();
                $amz->initialize($country_arr[$country]['init']);
                echo "Request ID : ".$value."<br>";
                $posted_date = $key;
                $return = $amz->fetchData($report_type, $value);
                echo '<br>Country: '.$country.' | Posted Date: '.(string)$posted_date.'<br>';
                echo 'Saving '.count($return['data']).' rows to database...<br>';
                $sku_flag = "";
                $p = array();
		    	foreach ($return['data'] as $value) {
                    $item = array();
		    		$item = $this->convert_keys_to_english($value);
                    $df = $item['start_date'];
                    $dt = $item['end_date'];
                    if($country == 'us' OR $country == 'ca'){
                        $item['start_date'] = date('Y-m-d', strtotime($df));
                        $item['end_date'] = date('Y-m-d', strtotime($dt));
                    }else{
                        $item['start_date'] = date('Y-m-d', strtotime(str_replace('/', '-', $df)));
                        $item['end_date'] = date('Y-m-d', strtotime(str_replace('/', '-', $dt)));
                    }
		    		$item['country'] = $country;
		    		$item['seller_id'] = $this->seller_id;
		    		$item['posted_date'] = $posted_date;
                    $item['ctr'] = (!isset($item['ctr'])) ? 0 : trim($item['ctr'],'%');
                    $item['1_day_convertion_rate'] = (!isset($item['1_day_convertion_rate'])) ? 0 : trim($item['1_day_convertion_rate'],'%');
                    $item['1_week_convertion_rate'] = (!isset($item['1_week_convertion_rate'])) ? 0 : trim($item['1_week_convertion_rate'],'%');
                    $item['1_month_convertion_rate'] = (!isset($item['1_month_convertion_rate'])) ? 0 : trim($item['1_month_convertion_rate'],'%');
	    			$total_records++;
		    		$item['created_at'] = date('Y-m-d H:i:s');
	    			$save = $univ->insertData('campaign_ad_mega_reports',$item);
                    if($sku_flag!=$item['advertised_sku']){
                    $p = DB::connection('mysql2')->table('products')
                        ->where('products.seller_id', $this->seller_id)
                        ->where('products.sku', $item['advertised_sku'])
                        ->where('products.country', $country)
                        ->leftJoin('product_costs', function ($leftJoin) {
                            $leftJoin->on('products.id', '=', 'product_costs.product_id')
                            ->where('product_costs.created_at', '=', DB::raw("(select max(`created_at`) from product_costs where product_costs.product_id = products.id)"));
                        })
                        ->get(['products.id','products.sku','products.price','products.sale_price','products.advice_margin','products.time_period','products.estimate_fees','product_costs.unit_cost'])
                        ->first();
                        $sku_flag = $p->sku;
                    }

                    $ad = new CampaignAdvertising;
                    $from_date = Carbon::today()->addMinutes(5)->subDays($p->time_period);
                    $ads = $ad->where('seller_id', $this->seller_id)
                            ->where('campaign_name', $item['campaign_name'])
                            ->where('ad_group_name', $item['ad_group_name'])
                            ->where('keyword', $item['keyword'])
                            ->where('match_type', $item['match_type'])
                            ->where('posted_date', '>=', $from_date)
                            ->get();
                    $ads_ids = array();
                    $cpc = 0;
                    $acos = 0;
                    if(count($ads) > 0){
                        foreach ($ads as $value) {
                            $ads_ids[] = $value->id;
                            $cpc += $value->average_cpc;
                            $acos += ($value->acos / 100);
                        }
                        if($acos == 0 OR $cpc == 0){
                            $max_bid = 0;
                        }else{
                            $cpc = $cpc / count($ads_ids);
                            $acos = $acos / count($ads_ids);
                            $max_bid = abs($p->sale_price - $p->estimate_fees - $p->unit_cost) / $p->sale_price;
                            $max_bid = ($max_bid / $cpc) * $acos;
                            $max_bid = round($max_bid, 2);
                        }
                        $ads = DB::connection('mysql2')->table('campaign_advertisings')->where('seller_id', $this->seller_id)
                                ->where('campaign_name', $item['campaign_name'])
                                ->where('ad_group_name', $item['ad_group_name'])
                                ->where('keyword', $item['keyword'])
                                ->where('match_type', $item['match_type'])
                                ->update(['max_bid_recommendation'=> 0]);

                        $ads = DB::connection('mysql2')->table('campaign_advertisings');
                        for ($i=0; $i < count($ads_ids); $i++) {
                            if($i == 0) $ads = $ads->where('id', $ads_ids[$i]);
                            else $ads = $ads->orWhere('id', $ads_ids[$i]);
                        }
                        $ads = $ads->update(['max_bid_recommendation'=> $max_bid]);
                        echo "SKU ".$item['advertised_sku']." max recommendation ".$max_bid."<br>";
                    }
		    	}
	    	}

    	}

        $time_end = time();
        $response['total_records'] = $total_records;
        $response['isError'] = $isError;
        $response['time_end'] = date('Y-m-d H:i:s');
        $response['time_start'] = date('Y-m-d H:i:s', $time_start);
        $response['total_time_of_execution'] = ($time_end - $time_start)/60;
        $response['tries'] = $tries;
        $response['message'] = $message;

        $log = new Log;
        $log->seller_id = $this->seller_id;
        $log->description = 'Campaign SP MEGA Report';
        $log->date_sent = date('Y-m-d H:i:s');
        $log->subject = 'Cron Notification for Campaign SP MEGA Report';
        $log->api_used = $report_type;
        $log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
        $log->record_fetched = $total_records;
        $log->message = $message;
        $log->save();

        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign SP MEGA Report for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
        } catch (\Exception $e) {
          $time_end = time();
          $response['time_start'] = date('Y-m-d H:i:s', $time_start);
          $response['time_end'] = date('Y-m-d H:i:s', $time_end);
          $response['total_time_of_execution'] = ($time_end - $time_start)/60;
          $response['tries'] = 1;
          $response['total_records'] = (isset($total_records) ? $total_records : 0);
          $response['isError'] = $isError;
          $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
          Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign SP MEGA Report for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
        }

    }

    private function convert_keys_to_english($data){
    	$new_data = array();
    	foreach ($data as $keys => $value) {
    		switch ($keys) {
    			case 'campaign_name':
    			case 'kampagne':
    			case 'nombre_de_campaña':
    			case 'nom_de_la_campagne':
    			case 'nome_campagna':
    				$new_data['campaign_name'] = $value;
    				break;

    			case 'ad_group_name':
    			case 'anzeigengruppenname':
    			case 'nombre_grupo_anuncios':
    			case "nom_du_groupe_d'annonces":
    			case 'nome_gruppo_di_annunci':
    				$new_data['ad_group_name'] = $value;
    				break;

    			case 'advertised_sku':
    			case 'beworbene_sku':
    			case 'sku_anunciados':
    			case 'sku_annoncé':
    			case 'sku_pubblicizzato':
    				$new_data['advertised_sku'] = $value;
    				break;

    			case 'keyword':
    			case 'palabra_clave':
    			case 'mot_clé':
    			case 'parola_chiave':
    				$new_data['keyword'] = $value;
    				break;

    			case 'match_type':
    			case strtolower('Übereinstimmungstyp'):
    			case 'tipo_de_concordancia':
    			case 'type_de_correspondance':
    			case 'tipo_di_corrispondenza':
    				$new_data['match_type'] = $value;
    				break;

    			case 'start_date':
    			case 'start_datum':
    			case 'fecha_inicio':
    			case 'date_de_début':
    			case "data_d'inizio":
    				$new_data['start_date'] = $value;
    				break;

    			case 'end_date':
    			case 'ende_datum':
    			case 'fecha_finalización':
    			case 'date_de_fin':
    			case 'data_di_fine':
    				$new_data['end_date'] = $value;
    				break;

    			case 'clicks':
    			case 'klicks':
    			case 'clics':
    			case 'nombre_de_clics':
    				$new_data['clicks'] = $value;
    				break;

    			case 'impressions':
    			case 'aufrufe':
    			case 'impresiones':
    			case 'impressions':
    			case 'impressioni':
    				$new_data['impressions'] = $value;
    				break;

    			case 'ctr':
    			case 'klickrate_(ctr)':
    				$new_data['ctr'] = $value;
    				break;

    			case 'total_spend':
    			case 'gesamtausgaben':
    			case 'gasto_total':
    			case 'dépenses_totales':
    			case 'spesa_totale':
    				$new_data['total_spend'] = $value;
    				break;

    			case 'average_cpc':
    			case 'durchschnittliche_cpc':
    			case 'cpc_medio':
    			case 'cpc_moyen':
    			case 'cpc_medio':
    				$new_data['average_cpc'] = $value;
    				break;

    			case 'currency':
    			case 'währung':
    			case 'divisa':
    			case 'devise':
    			case 'valuta':
    				$new_data['currency'] = $value;
    				break;

    			case '1_day_orders_placed_(#)':
    			case 'aufgegebene_bestellungen,_1_tag':
    			case 'pedidos_realizados_en_1_día':
    			case 'commandes_passées_en_1_jour_(#)':
    			case 'ordini_effettuati_entro_1_giorno':
    				$new_data['1_day_orders_placed'] = $value;
    				break;

    			case '1_day_ordered_product_sales_(£)':
    			case '1_day_ordered_product_sales_($)':
    			case '1_day_ordered_product_sales_(cdn$)':
    			case 'bestellumsatz,_1_tag_(€)':
    			case 'ventas_de_productos_pedidos_en_1_día_(€)':
    			case 'ventes_de_produits_commandés_en_1_jour_(€)':
    			case 'vendite_di_prodotti_ordinati_entro_1_giorno_(€)':
    				$new_data['1_day_ordered_product_sales'] = $value;
    				break;

    			case '1_day_convertion_rate':
    			case 'konversionsrate,_1_tag':
    			case 'tasa_de_conversión_en_1_día':
    			case 'taux_de_conversion_en_1_jour':
    			case 'tasso_di_conversione_entro_1_giorno':
    				$new_data['1_day_convertion_rate'] = $value;
    				break;

    			case '1_day_same_sku_units_ordered':
    			case 'bestellte_gleiche_sku_einheiten,_1_tag':
    			case 'unidades_sku_iguales_pedidas_en_1_día':
    			case 'unités_du_même_sku_commandées_en_1_jour':
    			case 'unità_con_lo_stesso_sku_ordinate_entro_1_giorno':
    				$new_data['1_day_same_sku_units_ordered'] = $value;
    				break;

    			case '1_day_other_sku_units_ordered':
    			case 'bestellte_andere_sku_einheiten,_1_tag':
    			case 'unidades_sku_diferentes_pedidas_en_1_día':
    			case "unités_d'un_autre_sku_commandées_en_1_jour":
    			case 'unità_con_altri_sku_ordinate_entro_1_giorno':
    				$new_data['1_day_other_sku_units_ordered'] = $value;
    				break;

    			case '1_day_same_sku_units_ordered_product_sales':
    			case 'bestellumsatz_gleiche_sku,_1_tag':
    			case 'ventas_de_productos_pedidos_correspondientes_a_unidades_sku_iguales_en_1_día':
    			case 'ventes_de_produits_commandés_des_unités_du_même_sku_en_1_jour':
    			case 'vendite_di_prodotti_con_lo_stesso_sku_ordinati_entro_1_giorno':
    				$new_data['1_day_same_sku_units_ordered_product_sales'] = $value;
    				break;

    			case '1_day_other_sku_units_ordered_product_sales':
    			case 'bestellumsatz_andere_skus,_1_tag':
    			case 'ventas_de_productos_pedidos_correspondientes_a_unidades_sku_diferentes_en_1_día':
    			case "ventes_de_produits_commandées_par_unités_d'un_autre_sku_en_1_jour":
    			case 'vendite_di_prodotti_con_altri_sku_ordinati_entro_1_giorno':
    				$new_data['1_day_other_sku_units_ordered_product_sales'] = $value;
    				break;

    			case '1_week_orders_placed_(#)':
    			case 'aufgegebene_bestellungen,_1_woche':
    			case 'pedidos_realizados_en_1_semana':
    			case 'commandes_passées_en_1_semaine_(#)':
    			case 'ordini_effettuati_entro_1_settimana':
    				$new_data['1_week_orders_placed'] = $value;
    				break;

    			case '1_week_ordered_product_sales_(£)':
    			case '1_week_ordered_product_sales_($)':
    			case '1_week_ordered_product_sales_(cdn$)':
    			case 'bestellumsatz,_1_woche_(€)':
    			case 'ventas_de_productos_pedidos_en_1_semana_(€)':
    			case 'ventes_de_produits_commandés_en_1_semaine_(€)':
    			case 'vendite_di_prodotti_ordinati_entro_1_settimana_(€)':
    				$new_data['1_week_ordered_product_sales'] = $value;
    				break;

    			case '1_week_convertion_rate':
    			case 'konversionsrate,_1_woche':
    			case 'tasa_de_conversión_en_1_semana':
    			case 'taux_de_conversion_en_1_semaine':
    			case 'tasso_di_conversione_entro_1_settimana':
    				$new_data['1_week_convertion_rate'] = $value;
    				break;

    			case '1_week_same_sku_units_ordered':
    			case 'bestellte_gleiche_sku_einheiten,_1_woche':
    			case 'unidades_sku_iguales_pedidas_en_1_semana':
    			case 'unités_du_même_sku_commandées_en_1_semaine':
    			case 'unità_con_lo_stesso_sku_ordinate_entro_1_settimana':
    				$new_data['1_week_same_sku_units_ordered'] = $value;
    				break;

    			case '1_week_other_sku_units_ordered':
    			case 'bestellte_andere_sku_einheiten,_1_woche':
    			case 'unidades_sku_diferentes_pedidas_en_1_semana':
    			case "unités_d'un_autre_sku_commandées_en_1_semaine":
    			case 'unità_con_altri_sku_ordinate_entro_1_settimana':
    				$new_data['1_week_other_sku_units_ordered'] = $value;
    				break;

    			case '1_week_same_sku_units_ordered_product_sales':
    			case 'bestellumsatz_gleiche_sku,_1_woche':
    			case 'ventas_de_productos_pedidos_correspondientes_a_unidades_sku_iguales_en_1_semana':
    			case 'ventes_de_produits_commandés_des_unités_du_même_sku_en_1_semaine':
    			case 'vendite_di_prodotti_con_lo_stesso_sku_ordinati_entro_1_settimana':
    				$new_data['1_week_same_sku_units_ordered_product_sales'] = $value;
    				break;

    			case '1_week_other_sku_units_ordered_product_sales':
    			case 'bestellumsatz_andere_skus,_1_woche':
    			case 'ventas_de_productos_pedidos_correspondientes_a_unidades_sku_diferentes_en_1_semana':
    			case "ventes_de_produits_commandées_par_unités_d'un_autre_sku_en_1_semaine":
    			case 'vendite_di_prodotti_con_altri_sku_ordinati_entro_1_settimana':
    				$new_data['1_week_other_sku_units_ordered_product_sales'] = $value;
    				break;

    			case '1_month_orders_placed_(#)':
    			case 'aufgegebene_bestellungen,_1_monat':
    			case 'pedidos_realizados_en_1_mes':
    			case 'commandes_passées_en_1_mois_(#)':
    			case 'ordini_effettuati_entro_1_mese':
    				$new_data['1_month_orders_placed'] = $value;
    				break;

    			case '1_month_ordered_product_sales_(£)':
    			case '1_month_ordered_product_sales_($)':
    			case '1_month_ordered_product_sales_(cdn$)':
    			case 'bestellumsatz,_1_monat_(€)':
    			case 'ventas_de_productos_pedidos_en_1_mes_(€)':
    			case 'ventes_de_produits_commandés_en_1_mois_(€)':
    			case 'vendite_di_prodotti_ordinati_entro_1_mese_(€)':
    				$new_data['1_month_ordered_product_sales'] = $value;
    				break;

    			case '1_month_convertion_rate':
    			case 'konversionsrate,_1_monat':
    			case 'tasa_de_conversión_en_1_mes':
    			case 'taux_de_conversion_en_1_mois':
    			case 'tasso_di_conversione_entro_1_mese':
    				$new_data['1_month_convertion_rate'] = $value;
    				break;

    			case '1_month_same_sku_units_ordered':
    			case 'bestellte_gleiche_sku_einheiten,_1_monat':
    			case 'unidades_sku_iguales_pedidas_en_1_mes':
    			case 'unités_du_même_sku_commandées_en_1_mois':
    			case 'unità_con_lo_stesso_sku_ordinate_entro_1_mese':
    				$new_data['1_month_same_sku_units_ordered'] = $value;
    				break;

    			case '1_month_other_sku_units_ordered':
    			case 'bestellte_andere_sku_einheiten,_1_monat':
    			case 'unidades_sku_diferentes_pedidas_en_1_mes':
    			case "unités_d'un_autre_sku_commandées_en_1_mois":
    			case 'unità_con_altri_sku_ordinate_entro_1_mese':
    				$new_data['1_month_other_sku_units_ordered'] = $value;
    				break;

    			case '1_month_same_sku_units_ordered_product_sales':
    			case 'bestellumsatz_gleiche_sku,_1_monat':
    			case 'ventas_de_productos_pedidos_correspondientes_a_unidades_sku_iguales_en_1_mes':
    			case 'ventes_de_produits_commandés_des_unités_du_même_sku_en_1_mois':
    			case 'vendite_di_prodotti_con_lo_stesso_sku_ordinati_entro_1_mese':
    				$new_data['1_month_same_sku_units_ordered_product_sales'] = $value;
    				break;

    			case '1_month_other_sku_units_ordered_product_sales':
    			case 'bestellumsatz_andere_skus,_1_monat':
    			case 'ventas_de_productos_pedidos_correspondientes_a_unidades_sku_diferentes_en_1_mes':
    			case "ventes_de_produits_commandées_par_unités_d'un_autre_sku_en_1_mois":
    			case 'vendite_di_prodotti_con_altri_sku_ordinati_entro_1_mese':
    				$new_data['1_month_other_sku_units_ordered_product_sales'] = $value;
    				break;

    			default:
    				# code...
    				break;
    		}
    	}
        return $new_data;
    }
}
