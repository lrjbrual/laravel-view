<?php
namespace App\MWSCustomClasses;

use AmazonReport;
use AmazonMWSConfig;
use AmazonReportRequest;
use AmazonReportList;
use AmazonParticipationList;
use AmazonFinancialEventList;
use AmazonProductList;
use AmazonProduct;
use App\MarketplaceAssign;
use App\Mail\CronNotification;
use App\Mail\CronNewColumnReport;
use Mail;
use Schema;


class MWSFetchReportClass {
    private $get_report_waiting_time = 120;
    private $mkpassign_model;
    private $merchantId, $marketplaceId, $keyId, $secretKey, $MWSAuthToken, $country;
    private $report_type, $marketPlace;
    private $start_fetch_date='-1 month';
	private $end_fetch_date = null;
  private $mkp_type=null;

	public function initialize($data = array()){
		$this->merchantId = $data['merchantId'];
		$this->marketplaceId = $data['marketPlace'];
		$this->MWSAuthToken = $data['MWSAuthToken'];
		$this->country = strtolower(trim($data['country']));
		$this->marketPlace = $data['marketPlace']; //seller marketplace id
		$this->start_fetch_date = $data['start_date'];
		$this->name = "";
		$this->end_fetch_date = (!isset($data['end_date'])) ? null : $data['end_date'] ;
	}

	public function request_RequestID($report_type){
		ini_set('memory_limit', '1024M');
		if (!headers_sent()) {
			ini_set("zlib.output_compression", 0);  // off
			ini_set("implicit_flush", 1);  // on
			ini_set("max_execution_time", 0);  // on
		}
		$this->report_type = $report_type;
		$this->name = $this->country ." ".$report_type;
		if ($this->country == 'uk' || $this->country == 'de' || $this->country == 'fr' || $this->country == 'es' || $this->country == 'it' ){
			$country_key = 'eu';
			$this->mkp_type = 'eu';
			$urlpref = '.co.uk';
		}else if($this->country == 'us' || $this->country == 'ca'){
			$country_key = 'na';
			$this->mkp_type = 'na';
			$urlpref = '.com';
		}

		if (!headers_sent()) {
			header('X-Accel-Buffering: no');
		}

        $configObject = new \AmazonMWSConfig($this->setAmazonConfig($country_key, $urlpref));

        $amz = new AmazonReportRequest($configObject);
		$amz->setReportType($this->report_type);
		$amz->setMarketplaces($this->marketPlace);
		$amz->setTimeLimits($this->start_fetch_date, $this->end_fetch_date);
		$response = $amz->requestReport();
		$request_id = $amz->getResponse()['ReportRequestId'];
		sleep(30);
		echo $this->country." Request ID: ".$request_id."<br>";
		ob_flush();
		flush();
		return $request_id;
	}

	public function fetchData($report_type, $requestid = null){
		ini_set('memory_limit', '1024M');
		if (!headers_sent()) {
			ini_set("zlib.output_compression", 0);  // off
			ini_set("implicit_flush", 1);  // on
			ini_set("max_execution_time", 0);  // on
		}

		$start_run_time = time();
		$this->report_type = $report_type;
		$report_request_tries=0;
		$response = array();
		$message = array();
		$isError = false;
		$this->name = $this->country ." ".$report_type;

		echo "<br><b>Country: </b>".$this->country;

		$timestamp_start_date = strtotime( '-1 month', time() );
		$start_date = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", $timestamp_start_date);

		if ($this->country == 'uk' || $this->country == 'de' || $this->country == 'fr' || $this->country == 'es' || $this->country == 'it' ){
			$country_key = 'eu';
      $this->mkp_type = 'eu';
		}else if($this->country == 'us' || $this->country == 'ca'){
			$country_key = 'na';
      $this->mkp_type = 'na';
		}

		if($this->country == 'uk' ) $urlpref = '.co.uk';
		else if($this->country == 'us') $urlpref = '.com';
		else $urlpref = ".".$this->country;

		if (!headers_sent()) {
			header('X-Accel-Buffering: no');
		}

		$request_id = $requestid;
	    $configObject = new \AmazonMWSConfig($this->setAmazonConfig($country_key, $urlpref));
		if($request_id == null OR $request_id != ""){
      $amz = new AmazonReportRequest($configObject);
			$amz->setReportType($this->report_type);
			$amz->setMarketplaces($this->marketPlace);
			$amz->setTimeLimits($this->start_fetch_date, $this->end_fetch_date);
			$response = $amz->requestReport();
			$ctr=0;
			$request_id = $amz->getResponse()['ReportRequestId'];
		}
		if($request_id != null && $request_id != ""){
			echo "<br/><b>request_id = " . $request_id."</b>";

			ob_flush();
			flush();

			$report_id = null;

			echo "<br/>Getting report data from Amazon ...";

			ob_flush();
			flush();

			$report_request_tries = 1;

			$get_report_response = array();

			$amz_reportreq = new AmazonReportList($configObject);
			$amz_reportreq->setRequestIds($request_id);
			while($report_id == null AND $report_request_tries<=5)
			{
				$amz_reportreq->fetchReportList();
				$haslist = $amz_reportreq->getList();
				if($haslist!=false){

					$report_id = $amz_reportreq->getReportId();
					if($report_id == null)
					{
						echo "<br/><b>Report Not Yet Available ... retrying after " . ($this->get_report_waiting_time / 60). " minutes ..</b>";
						ob_flush();
						flush();
						sleep($this->get_report_waiting_time);
						$report_request_tries += 1;
						echo "<br/><b style='color:green'>Retrying ...</b>";
						ob_flush();
						flush();
					}
				}else{
					echo "<br/><b>Report Not Yet Available ... retrying after " . ($this->get_report_waiting_time / 60). " minutes ..</b>";
					ob_flush();
					flush();
					sleep($this->get_report_waiting_time);
					$report_request_tries += 1;
					echo "<br/><b style='color:green'>Retrying ...</b>";
					ob_flush();
					flush();
				}
			}
			if($report_request_tries>=5 AND $report_id == null){
				$message['message'] = "No Response from Amazon!";
				$message['isError'] = true;
				$response = array();
				$isError = true;
			}else{
				echo "<br/><b>DONE after " . $report_request_tries . " tries.</b> <i>report_id = " . $report_id . "</i>";
				ob_flush();
				flush();
				echo "<br/>Parsing response ... ";
				ob_flush();
				flush();

				$amz_report = new AmazonReport($configObject);
				$amz_report->setReportId($report_id);

				$amz_report->fetchReport();
				$report = $amz_report->getRawReport();

				//print_r($report);
				if($report != null){
					$result = $this->convertReportToArray($report);
					$response = $result;
				}else{
					$message['message'] = "Empty Report!";
					$message['isError'] = true;
					$response = array();
					$isError = true;
				}
			}
		}else{
			$message['error_message'] = "Invalid MWS Authentication!";
			$message['isError'] = true;
			$response = array();
			$isError = true;
		}

		$end_run_time = time();

		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = $report_request_tries - 1;
		$message['data'] = $response;
		if(!$isError){
			$message['message'] = 'Successfully Fetch Data!';
			$message['isError'] = false;
			$message['data'] = $response;
		}else{
			$message['data'] = array();
			$message['total_records'] = 0;
			$message['message'] = 'No Response from Amazon!';
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification($this->name, false, $message));
		}

		return $message;

	}

	public function fetchDataProduct($array){
		ini_set('memory_limit', '1024M');
		if (!headers_sent()) {
			ini_set("zlib.output_compression", 0);  // off
			ini_set("implicit_flush", 1);  // on
			ini_set("max_execution_time", 0);  // on
		}

		$start_run_time = time();
		$report_request_tries=0;
		$response = array();
		$message = array();
		$isError = false;

		echo "<br><b>Country: </b>".$this->country;

		if ($this->country == 'uk' || $this->country == 'de' || $this->country == 'fr' || $this->country == 'es' || $this->country == 'it' ){
			$country_key = 'eu';
      $this->mkp_type = 'eu';
		}else if($this->country == 'us' || $this->country == 'ca'){
			$country_key = 'na';
      $this->mkp_type = 'na';
		}

		if($this->country == 'uk' ) $urlpref = '.co.uk';
		else if($this->country == 'us') $urlpref = '.com';
		else $urlpref = ".".$this->country;

		if (!headers_sent()) {
			header('X-Accel-Buffering: no');
		}

	    $configObject = new \AmazonMWSConfig($this->setAmazonConfig($country_key, $urlpref));


			$amz_reportreq = new AmazonProductList($configObject);
			$amz_reportreq->setIdType('ASIN');
			$amz_reportreq->setProductIds($array);
			$results = $amz_reportreq->getXMLofProduct();

			$productImage = array();
			foreach($results as $result)
			{
				$id = (string)$result->attributes()->Id[0];
				$a = $amz_reportreq->getXMLofProductAttribute($result);
				if(isset($a))
				$productImage[$id] = (string) $a->ItemAttributes->SmallImage->URL;
				else
				{
					if($id != "")
					$productImage[$id] = null;
				}
			}

			return $productImage;
	}

	private function getURLByCountry($country){
		$url = "https://mws.amazonservices.";
		$country = strtolower($country);
		if($country == 'us') $url .= 'com';
		else if($country == 'uk') $url .= 'co.uk';
		else $url .= $country;
		return $url;
	}

  private function translate_key($h){
      switch($h){
        case 'artikelbezeichnung':$h='item_name';break;
        case 'artikelbeschreibung':$h='item_description';break;
        case 'angebotsnummer':$h='listing_id';break;
        case 'handler_sku':$h='seller_sku';break;
        case 'preis':$h='price';break;
        case 'menge':$h='quantity';break;
        case 'erstellungsdatum':$h='open_date';break;
        case 'artikel_ist_marketplace_angebot':$h='item_is_marketplace';break;
        case 'produkt_id_typ':$h='product_id_type';break;
        case 'anmerkung_zum_artikel':$h='item_note';break;
        case 'artikelzustand':$h='item_condition';break;
        case 'asin_1':$h='asin1';break;
        case 'asin_2':$h='asin2';break;
        case 'asin_3':$h='asin3';break;
        case 'internationaler_versand':$h='will_ship_internationally';break;
        case 'expressversand':$h='expedited_shipping';break;
        case 'produkt_id':$h='product_id';break;
        case 'hinzufugen_loschen':$h='add_delete';break;
        case 'anzahl_bestellungen':$h='pending_quantity';break;
        case 'versender':$h='fulfillment_channel';break;
        case 'handlerversandgruppe':$h='merchant_shipping_group';break;
      }
      return $h;
  }
  private function remove_accented_key($string){

  $normalizeChars = array(
      'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
      'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
      'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
      'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
      'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
      'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
      'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
      'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
  );

  //Output: E A I A I A I A I C O E O E O E O O e U e U i U i U o Y o a u a y c
  return strtr($string, $normalizeChars);
  }

	public function convertReportToArray($report){
		$string='';
		$row_delimiter=PHP_EOL;
    
    $tabdelimitedreporttype=array('_GET_SP_ENTITY_REPORT_','_SC_VAT_TAX_REPORT_');
		if(in_array($this->report_type,$tabdelimitedreporttype)){
			$delimiter = ",";
		}else{
			$delimiter = "\t";
		}
		$enclosure = '"';
		$escape = "\\";
		$string = $report;
		$rows = array_filter(explode($row_delimiter, $string));
        $header = NULL;
        $data = array();
        foreach($rows as $row)
        {
            $row = str_getcsv ($row, $delimiter, $enclosure , $escape);

            if(!$header OR ($this->report_type == '_GET_SP_ENTITY_REPORT_' AND count($header)<5))
            {
                $header_temp = $row;
                $header = array();

                foreach($header_temp as $header_item)
                {
                    if($header_item != "" && $header_item != NULL){
                    	$new_key = trim(str_replace("-","_",$header_item));
  		                $new_key = trim(str_replace(" ","_",$new_key));
  		                $new_key = strtolower($new_key);
          						if($new_key == 'your_price') $new_key = 'price';
          						if($new_key == 'mfn_fulfillable_quantity') $new_key = 'quantity';
                      $new_key=utf8_encode($new_key);
                      $new_key=$this->remove_accented_key($new_key);
                      $new_key=$this->translate_key($new_key);
                      $header[] = $new_key;
                    }
                }

            }
            else
            {
                $field_count_header = count($header);
                $field_count_row = count($row);
                //print_r($row);

				if($field_count_header > $field_count_row){
					$rows2 = explode($delimiter, $row[count($row)-1]);
					$first=true;
					foreach($rows2 as $row2){
						if($first){
							$first = false;
							$row[count($row)-1] = $row2;
						}else{
							$row[] = $row2;
						}
					}
				}

                $index = $field_count_header - 1;
                $field_count_header = count($header);
                $field_count_row = count($row);
				$extra_rows = abs($field_count_header - $field_count_row);
				if(count($header) > count($row)){
					while($extra_rows > 0 )
					{
						unset($row[$index]);
						$index = $index + 1;
						$extra_rows = $extra_rows - 1;
					}
				}

                if(count($header) > count($row)){
					for($x=count($row); $x<count($header); $x++) $row[]="";
				}
              if($this->mkp_type=='na'){
              	$row = array_map('utf8_encode', $row);
              }
              $data[] = array_combine($header, $row);

            }
        }
        $cleaned_data = $data;
        return $cleaned_data;
	}

	public function getReportIDList($report_type,$country_key){
		ini_set("max_execution_time", 0);  // on
		echo "Report Type: ".$report_type."<br>";
		echo "Getting Report ID in ".$country_key.".....<br>";
		ob_flush();
		flush();
		$this->report_type = $report_type;
		if($country_key == 'eu') $urlpref = '.co.uk';
		else $urlpref = '.com';

		$configObject = new \AmazonMWSConfig($this->setAmazonConfig($country_key, $urlpref));

    $amz = new AmazonReportList($configObject);
		$amz->setReportTypes($this->report_type);
		$amz->setUseToken(true);
		$amz->setTimeLimits($this->start_fetch_date, $this->end_fetch_date);
		$amz->setMaxCount(100);
		$amz->fetchReportList();
		$id_list = $amz->getList();
		echo " Done<br><br><br>";
		ob_flush();
		flush();
		return $id_list;
	}

	private function setAmazonConfig($country_key="", $urlpref=""){
		if (!headers_sent()) {
			header('X-Accel-Buffering: no');
		}
		$amz_conf = array(
          'stores' =>
              array('YourAmazonStore' =>
                  array(
                      'merchantId'    => $this->merchantId, //mkp_seller_id
                      'MWSAuthToken'  => $this->MWSAuthToken,   //mkp_auth_token
                      'marketplaceId' => $this->marketplaceId,
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
        return $amz_conf;
	}

	public function fetchReportByID($report_id, $country_key){
		ini_set('memory_limit', '512M');
		ini_set("max_execution_time", 0);

		$start_run_time = time();

		if($country_key == 'eu') $urlpref = '.co.uk';
		else $urlpref = '.com';


        $configObject = new \AmazonMWSConfig($this->setAmazonConfig($country_key, $urlpref));

		$amz_report = new AmazonReport($configObject);
		$amz_report->setReportId($report_id);
		$amz_report->fetchReport();
		$report = $amz_report->getRawReport();
		$response=array();
		if($report != null OR $report != false){
			$response = $this->convertReportToArray($report);
		}

		$end_run_time = time();
		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = 1;
		$message['message'] = 'Successfully Fetch Data!';
		$message['isError'] = false;
		$message['data'] = $response;

		return $message;

	}

  public function checkForNewColumn($thistable='',$reportsampledata=array()){
    if($thistable!=''){
      $currentcolumns = Schema::connection('mysql2')->getColumnListing($thistable);
      $reportcolumns = array_keys($reportsampledata);
      $newcols=array();
      foreach($reportcolumns as $rc){
        if(!in_array($rc,$currentcolumns)){
          array_push($newcols,$rc);
        }
      }
      $this->sendmailfornewcolumn($newcols,$reportsampledata,$thistable);
      // $r=array($newcols,$reportsampledata,$thistable);
      return $newcols;
    }else{
      return array();
    }
  }
  private function sendmailfornewcolumn($newcols=array(),$reportsampledata,$thistable){
    if(count($newcols)>0){
      Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNewColumnReport($newcols,$reportsampledata,$thistable));
    }
  }
}
