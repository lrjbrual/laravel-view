<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MWSCustomClasses\MWSFetchReportClass;
use App\MarketplaceAssign;
use App\UniversalModel;
use App\Log;
use App\Mail\CronNotification;
use Illuminate\Support\Facades\Input;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\CampaignAdEntityReport;
use App\TrendleChecker;
use App\Seller;

class UpdateAdsEntityReportController extends Controller
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
    	$report_type = '_GET_SP_ENTITY_REPORT_';
    	$univ = new UniversalModel();
		$mkp_q= new MarketplaceAssign();

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

    	$w = array();
    	$where  = array('seller_id'=>$this->seller_id);
    	if( Input::get('mkp') != null OR Input::get('mkp') != "" )
        {
        	$this->mkp = trim(Input::get('mkp'));
        	$where  = array('seller_id'=>$this->seller_id, 'marketplace_id'=>$this->mkp);
        }else{
        	echo "<p style='color:red;'><b>Marketplace is required to run this cron script</b></p>";
			exit();
        }


        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Ads Entity Report for seller'.$this->seller_id.' mkp'.$this->mkp, true));

        //response for mail
        $time_start = time();
        $isError=false;
        $message = "Campaign Advertising Cron Successfully Fetch Data!";
        $response['time_start'] = date('Y-m-d H:i:s');
        $response['total_time_of_execution'] = 0;
        $response['message'] = $message;
        $response['isError'] = false;
        $response['tries'] = 0;
        $tries=0;
		$mkp_assign = $mkp_q->getRecords(config('constant.tables.mkp'),array('*'),$where,array());
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

                $w = array('seller_id'=> $this->seller_id, 'country'=>$country);
                $ff_data_count = $univ->getRecords('campaign_ad_entity_reports',array('*'),$w,array(),true);

                $start_date = Carbon::today()->addMinutes(5)->subDay();
                $end_date = Carbon::today()->addMinutes(4);
                if(count($ff_data_count)>0){
                    $stop_date = Carbon::today()->addMinutes(5)->subDays(8);
                    $isEmpty = false;
                }
                else{
                    $stop_date = Carbon::today()->addMinutes(5)->subDays(61);
                    $isEmpty = true;
                }
                $day_count = 1;
                // add checker and use checker to continue the last fetching if not done
                $tc_first = TrendleChecker::where('seller_id', '=', $this->seller_id)
                            ->where('checker_name', '=', 'campaign_ad_entity_first')
                            ->where('checker_country', '=', $country)
                            ->first();
                if (isset($tc_first)) {
                    if ((int)$tc_first->checker_status < 60 ) {
                        $isEmpty = true;
                        $day_count = (int)$tc_first->checker_status + 1;
                        $d1 = date_format(date_create($tc_first->checker_date), 'Y-m-d');
                        $d2 = date_format(date_create($tc_first->created_at), 'Y-m-d');
                        $start_date = Carbon::parse($d1)->addMinutes(5)->subDays(1);
                        $end_date = Carbon::parse($d2)->addMinutes(4);
                        $stop_date = Carbon::parse($d2)->addMinutes(5)->subDays(61);
                    } else {
                        $isEmpty = false;
                        $tc_daily = TrendleChecker::where('seller_id', '=', $this->seller_id)
                            ->where('checker_name', '=', 'campaign_ad_daily')
                            ->where('checker_country', '=', $country)
                            ->first();

                        if (isset($tc_daily)) {
                            if ((int)$tc_daily->checker_status < 7) {
                                $day_count = (int)$tc_daily->checker_status + 1;
                                $d1 = date_format(date_create($tc_daily->checker_date), 'Y-m-d');
                                $d2 = date_format(date_create($tc_daily->updated_at), 'Y-m-d');
                                $start_date = Carbon::parse($d1)->addMinutes(5)->subDays(1);
                                $end_date = Carbon::parse($d2)->addMinutes(4);
                                $stop_date = Carbon::parse($d2)->addMinutes(5)->subDays(8);
                            }
                        }
                    }
                }
                $tc_daily = TrendleChecker::where('seller_id', '=', $this->seller_id)
                    ->where('checker_name', '=', 'campaign_ad_entity_daily')
                    ->where('checker_country', '=', $country)
                    ->first();

                if (isset($tc_daily)) {
                    $isEmpty = false;
                    if ((int)$tc_daily->checker_status < 7) {
                        $day_count = (int)$tc_daily->checker_status + 1;
                        $d1 = date_format(date_create($tc_daily->checker_date), 'Y-m-d');
                        $d2 = date_format(date_create($tc_daily->updated_at), 'Y-m-d');
                        $start_date = Carbon::parse($d1)->addMinutes(5)->subDays(1);
                        $end_date = Carbon::parse($d2)->addMinutes(4);
                        $stop_date = Carbon::parse($d2)->addMinutes(5)->subDays(8);
                    }
                }

                $country_arr[$country]['isEmpty'] = $isEmpty;
                $country_arr[$country]['day_count'] = $day_count;
                $cnt = 0;
                while((string)$start_date!=(string)$stop_date) {

                    $init = array(
                        'merchantId'    => $merchantId,
                        'MWSAuthToken'  => $MWSAuthToken,       //mkp_auth_token
                        'country'       => $country,            //mkp_country
                        'marketPlace'   => $mkp_data['id'],     //seller marketplace id
                        'start_date'    => (string)$start_date,
                        'end_date'      => (string)$end_date,
                        'name'          => 'Campaign Advertising Entity Report API'
                    );
                    $amz = new MWSFetchReportClass();
                    $amz->initialize($init);
                    $report_ids[(string)$start_date] = $amz->request_RequestID($report_type);
                    $start_date->subDay();
                    $end_date->subDay();
                }
                $country_arr[$country]['report_ids'] = $report_ids;
                $country_arr[$country]['init'] = $init;
            }
        }
        $columnChecked=false;
        foreach ($country_arr as $keys2 => $value) {
            $country = $keys2;
            $isEmpty = $country_arr[$keys2]['isEmpty'];
            $day_count = $country_arr[$keys2]['day_count'];
            foreach ($country_arr[$country]['report_ids'] as $key => $value) {
                $amz = new MWSFetchReportClass();
                $amz->initialize($country_arr[$country]['init']);
                echo "<br>Request ID : ".$value."<br>";
                $start_date = $key;

                $return = $amz->fetchData($report_type, $value);
                echo 'Country: '.$country.' <br>';
                echo 'Saving '.count($return['data']).' rows to database...<br>';
                if(($columnChecked==false)&&(isset($return['data'][0]))){
                  $amz->checkForNewColumn('campaign_ad_entity_reports',$return['data'][0]);
                  $columnChecked=true;
                }
                foreach ($return['data'] as $value ) {
                    $data = $this->convert_keys_to_english($value);
                    $df = $data['campaign_start_date'];
                    $dt = $data['campaign_end_date'];
                    if($mkp_id == 1){
                        $data['campaign_start_date'] = date('Y-m-d', strtotime($df));
                        $data['campaign_end_date'] = date('Y-m-d', strtotime($dt));
                    }else{
                        $data['campaign_start_date'] = date('Y-m-d', strtotime(str_replace('/', '-', $df)));
                        $data['campaign_end_date'] = date('Y-m-d', strtotime(str_replace('/', '-', $dt)));
                    }
                    $data['posted_date'] = $start_date;
                    $data['impressions'] = (!isset($data['impressions'])) ? 0 : $data['impressions'];
                    $data['clicks'] = (!isset($data['clicks'])) ? 0 : $data['clicks'];
                    $data['spend'] = (!isset($data['spend'])) ? "0" : $data['spend'];
                    $data['orders'] = (!isset($data['orders'])) ? 0 : $data['orders'];
                    $data['sales'] = (!isset($data['sales'])) ? 0 : $data['sales'];
                    $data['acos'] = (!isset($data['acos'])) ? "" : $data['acos'];
                    $data['bid+'] = (!isset($data['bid+'])) ? "0" : $data['bid+'];
                    $data['max_bid'] = (!isset($data['max_bid'])) ? "0" : $data['max_bid'];


                     $where = array(
                     'country'=> $country,
                     'seller_id' => $this->seller_id,
                     'campaign_name' => $data['campaign_name'],
                     'ad_group_name' => $data['ad_group_name'],
                     'keyword' => $data['keyword'],
                     'posted_date' => $data['posted_date'],
                     'match_type' => $data['match_type']
                    );

                    $univ->updateData('campaign_advertisings', $where, ['bid'=>$data['max_bid']]);


                    if ($isEmpty == true) {
                    	$data['created_at'] = date('Y-m-d H:i:s');
                    	$data['seller_id'] = $this->seller_id;
                    	$data['country'] = $country;
                    	$save = $univ->insertData('campaign_ad_entity_reports',$data);
                    }else{
                    	$where['seller_id'] = $this->seller_id;
                    	$where['country'] = $country;
                    	$where['record_id'] = $data['record_id'];
                    	$where['record_type'] = $data['record_type'];
                    	$where['campaign_name'] = $data['campaign_name'];
                    	$where['campaign_start_date'] = $data['campaign_start_date'];
                    	$where['campaign_end_date'] = $data['campaign_end_date'];
                    	$where['campaign_targeting_type'] = $data['campaign_targeting_type'];
                    	$where['ad_group_name'] = $data['ad_group_name'];
                    	$where['keyword'] = $data['keyword'];
                    	$where['match_type'] = $data['match_type'];
                    	$where['sku'] = $data['sku'];
                    	if($univ->isExist('campaign_ad_entity_reports', $where)){
                    		$data['updated_at'] = date('Y-m-d H:i:s');
                    		$univ->updateData('campaign_ad_entity_reports', $where, $data);
                    	}
                    }
                    $total_records++;
                }
                if ($isEmpty == true) {
                    $tc = TrendleChecker::where('seller_id', '=', $this->seller_id)
                        ->where('checker_name', '=', 'campaign_ad_first')
                        ->where('checker_country', '=', $country)
                        ->first();
                    if (isset($tc)){
                        $tc->checker_status = $day_count;
                        $tc->checker_date = $start_date;
                        $tc->updated_at = date('Y-m-d H:i:s');
                        $tc->save();
                    } else {
                        $tc = new TrendleChecker();
                        $tc->seller_id = $this->seller_id;
                        $tc->checker_name = 'campaign_ad_first';
                        $tc->checker_country = $country;
                        $tc->checker_status = $day_count;
                        $tc->checker_date = $start_date;
                        $tc->created_at = date('Y-m-d H:i:s');
                        $tc->updated_at = date('Y-m-d H:i:s');
                        $tc->save();
                    }
                } else {
                    $tc = TrendleChecker::where('seller_id', '=', $this->seller_id)
                        ->where('checker_name', '=', 'campaign_ad_daily')
                        ->where('checker_country', '=', $country)
                        ->first();
                    if (isset($tc)){
                        $tc->checker_status = $day_count;
                        $tc->checker_date = $start_date;
                        $tc->updated_at = date('Y-m-d H:i:s');
                        $tc->save();
                    } else {
                        $tc = new TrendleChecker();
                        $tc->seller_id = $this->seller_id;
                        $tc->checker_name = 'campaign_ad_daily';
                        $tc->checker_country = $country;
                        $tc->checker_status = $day_count;
                        $tc->checker_date = $start_date;
                        $tc->created_at = date('Y-m-d H:i:s');
                        $tc->updated_at = date('Y-m-d H:i:s');
                        $tc->save();
                    }
                }
                $day_count++;
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
        $log->description = 'Campaign Advertisings';
        $log->date_sent = date('Y-m-d H:i:s');
        $log->subject = 'Cron Notification for Campaign Ads Entity Report';
        $log->api_used = $report_type;
        $log->start_time = $response['time_start'];
        $log->end_sent = date('Y-m-d H:i:s');
        $log->record_fetched = $total_records;
        $log->message = $message;
        $log->save();

        Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Ads Entity Report for seller'.$this->seller_id.' mkp'.$this->mkp, false, $response));
        } catch (\Exception $e) {
          $time_end = time();
          $response['time_start'] = date('Y-m-d H:i:s', $time_start);
          $response['time_end'] = date('Y-m-d H:i:s', $time_end);
          $response['total_time_of_execution'] = ($time_end - $time_start)/60;
          $response['tries'] = 1;
          $response['total_records'] = (isset($total_records) ? $total_records : 0);
          $response['isError'] = $isError;
          $response['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
          Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Campaign Ads Entity Report for seller'.$this->seller_id.' mkp'.$this->mkp.' (error)', false, $response));
        }
  }

	public function convert_keys_to_english($data = array()){
		$new_data = array();
    	foreach ($data as $key => $value) {
    		switch ( strtolower(trim($key)) ) {
    			case 'record_id':
    			case 'id':
    			case 'id_de_registro':
    				$new_data['record_id'] = $value;
    				break;

    			case 'record_type':
    			case 'typ_der_aufzeichnung':
    			case 'tipo_de_registro':
    			case "type_d'enregistrement":
    			case 'tipo_di_record':
    				$new_data['record_type'] = $value;
    				break;

    			case 'campaign_name':
    			case 'kampagnenname':
    			case 'nombre_de_la_campaña':
    			case 'nom_de_la_campagne':
    			case 'nome_della_campagna':
    				$new_data['campaign_name'] = $value;
    				break;

    			case 'campaign_daily_budget':
    			case 'tägliches_budget_für_die_kampagne':
    			case 'presupuesto_diario_de_la_campaña':
    			case 'budget_quotidien_de_la_campagne':
    			case 'budget_giornaliero_della_campagna':
    				$new_data['campaign_daily_budget'] = $value;
    				break;

    			case 'campaign_start_date':
    			case 'startdatum_der_kampagne':
    			case 'fecha_de_inicio_de_la_campaña':
    			case 'date_de_début_de_la_campagne':
    			case 'data_di_inizio_della_campagna':
    				$new_data['campaign_start_date'] = $value;
    				break;

    			case 'campaign_end_date':
    			case 'enddatum_der_kampagne':
    			case 'fecha_de_finalización_de_la_campaña':
    			case 'date_de_fin_de_la_campagne':
    			case 'data_di_fine_della_campagna':
    				$new_data['campaign_end_date'] = $value;
    				break;

    			case 'campaign_targeting_type':
    			case 'ausrichtungstyp_der_kampagne':
    			case 'tipo_de_segmentación_de_la_campaña':
    			case 'type_de_ciblage_de_la_campagne':
    			case 'tipo_di_targeting_della_campagna':
    				$new_data['campaign_targeting_type'] = $value;
    				break;

    			case 'ad_group_name':
    			case 'anzeigengruppenname':
    			case 'nombre_del_grupo_de_anuncios':
    			case "nom_du_groupe_d'annonces":
    			case 'nome_del_gruppo_di_annunci':
    				$new_data['ad_group_name'] = $value;
    				break;

    			case 'max_bid':
    			case 'maximales_gebot':
    			case 'puja_máxima':
    			case 'enchère_max':
    			case 'offerta_massima':
    				$new_data['max_bid'] = $value;
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
    			case 'yype_de_correspondance':
    			case 'tipo_di_corrispondenza':
    				$new_data['match_type'] = $value;
    				break;

    			case 'sku':
    				$new_data['sku'] = $value;
    				break;

    			case 'campaign_status':
    			case 'kampagnenstatus':
    			case 'estado_de_la_campaña':
    			case 'statut_de_la_campagne':
    			case 'stato_della_campagna':
    				$new_data['campaign_status'] = $value;
    				break;

    			case 'adgroup_status':
    			case 'status_der_anzeigengruppe':
    			case 'estado_del_grupo_de_anuncios':
    			case 'statut_du_groupe_d’annonces':
    			case 'stato_del_gruppo':
    				$new_data['adgroup_status'] = $value;
    				break;

    			case 'status':
    			case 'estado':
    			case 'statut':
    			case 'stato':
    				$new_data['status'] = $value;
    				break;

    			case 'impressions':
    			case 'seitenaufrufe':
    			case 'impresiones':
    			case 'impressioni':
    				$new_data['impressions'] = $value;
    				break;

    			case 'clicks':
    			case 'klicks':
    			case 'clics':
    			case 'clic':
    				$new_data['clicks'] = $value;
    				break;

    			case 'spend':
    			case 'ausgaben':
    			case 'invertido':
    			case 'dépenses':
    			case 'spesa':
    				$new_data['spend'] = $value;
    				break;

    			case 'orders':
    			case 'bestellungen':
    			case 'pedidos':
    			case 'commandes':
    			case 'ordini':
    				$new_data['orders'] = $value;
    				break;

    			case 'sales':
    			case 'verkäufe':
    			case 'ventas':
    			case 'ventes':
    			case 'vendite':
    				$new_data['sales'] = $value;
    				break;

    			case 'acos':
    			case 'zugeschriebene_umsatzkosten':
    			case 'coste_publicitario_de_las_ventas':
    			case 'ratio_dépenses_publicitaires/chiffre_d’affaires':
    			case 'costo_delle_vendite_pubblicitarie':
    				$new_data['acos'] = $value;
    				break;

    			case 'bid+':
    			case 'gebot+':
    			case 'puja+':
    			case 'enchère+':
    			case 'offerta+':
    				$new_data['bid+'] = $value;
    				break;
    		}
    	}
    	return $new_data;
	}

}
