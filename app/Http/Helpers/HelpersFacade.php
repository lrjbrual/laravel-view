<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Facade;
use Carbon\Carbon;
use App\AdsCampaignAdGroup;
use App\AdsCampaign;

class HelpersFacade extends Facade {

    public static function formatPlanAmount($amount)
    {
        return str_replace(".00", "", (string)number_format($amount / 100, 2, ".", ""));
    }

   /**
   	* added by jason 07-10-17
 	* generate Click to edit for datatables editable column cell
 	* @param  string => value , string => text to display, String => html class name
 	* @return html
 	*/
 	public function editableColumnCell(String $value,String $text,String $class)
 	{
 		if ($value == '') {
 			return '<span class="'.$class.'">'.$text.'</span>';
 		}

 		return '<span class="'.$class.'">'.$value.'</span>';
 	}


 	/* added by Ferdz 09-19-2017
 	 * get data from adgroup and campaign and convert to array
 	 * require seller id
 	 * return json array
 	 */
 	public function getCampaignAdgroupDataForFilter($seller_id){
 		$query = new AdsCampaign;
        $camp_name_q = $query->where('seller_id', $seller_id)
            ->select(['name','country', 'targetingtype', 'campaignid'])
            ->orderBy('name')
            ->get();
        //$c = array();
        $c_c_t = array();
        $campaignid_name = array();
        $country_list = array();
        foreach ($camp_name_q as $key => $value) {
            //$c[$value->name] = $value->name;
            $c_c_t[$value->country][$value->targetingtype][] = $value->campaignid;
            $campaignid_name[$value->campaignid] = $value->name;
            $country_list[] = $value->country;
        }
        $country_list = array_unique($country_list);
        sort($country_list);
        

        $query = new AdsCampaignAdGroup;
        $ad_group_name =$query->where('seller_id', $seller_id)
            ->select(['name','campaignid','adgroupid'])
            ->orderBy('name')
            ->get();
        $c_adg = array();
        $adgroupid_name = array();
        $cname_adgname = array();
        foreach ($ad_group_name as $key => $value) {
            if(isset($campaignid_name[$value->campaignid])){
                $c_adg[$campaignid_name[$value->campaignid]][] = $value->name;
                $adgroupid_name[$value->adgroupid] = $value->name;
            }
        }
        $response = [
            'cct'=>$c_c_t, 
            'campaign_list'=>$campaignid_name, 
            //'adgrouplist'=>$adgroupid_name, 
            'cid_adg'=>$c_adg,
            'country_list' => $country_list
        ];
        return $response;
 	}

 	public function dateMonthTranslatorForDexi($dateString,$url)
 	{
 		if($url == 'https://www.amazon.it')
 		{
 			$string = trim($dateString, 'il ');
			$month = strstr($string,' ');
			$month = trim($month,' ');
			$month = strstr($month,' ',true);
			$month = strtolower($month);

			$year = trim($dateString,'il ');
			$year = strstr($year,' ');
			$year = trim($year,' ');
			$year = strstr($year,' ');
			$year = trim($year,' ');

			$day = trim($dateString,'il ');
			$day = strstr($day,' ',true);
 		}
 		
 		if($url == 'https://www.amazon.de')
 		{
 			$string = trim($dateString, 'am ');
			$month = strstr($string,' ');
			$month = trim($month,' ');
			$month = strstr($month,' ',true);
			$month = strtolower($month);

			$day = trim($dateString,'am ');
			$day = strstr($day,'.',true);

			$year = strstr($dateString,'. ');
			$year = trim($year,'. ');
			$year = strstr($year,' ');
			$year = trim($year,' ');
 		}

 		if($url == 'https://www.amazon.es')
 		{
 			$string = trim($dateString, 'el ');
			$month = strstr($string,'de');
			$month = trim($month,'de');
			$month = trim($month,' ');
			$month = strstr($month,' de',true);

			$day = strstr($dateString,'de',true);
			$day = trim($day,'el ');

			$year = strstr($dateString,'de ');
			$year = trim($year,'de ');
			$year = strstr($year,'de ');
			$year = trim($year,'de ');
 		}

 		if($url == 'https://www.amazon.fr')
 		{
 			$string = trim($dateString,' le ');
			$month = strstr($string,' ');
			$month = trim($month,' ');
			$month = strstr($month,' ',true);

			$day = trim($dateString,'le ');
			$day = strstr($string,' ',true);

			$year = trim($dateString,'le ');
			$year = strstr($year,' ');
			$year = trim($year,' ');
			$year = strstr($year,' ');
			$year = trim($year,' ');
 		}

 		if($url == "https://www.amazon.com" || $url == "https://www.amazon.co.uk" || $url == "https://www.amazon.ca")
 		{
 			$string = trim($dateString, 'on ');
 			$month = Carbon::parse($string)->format('m');
 			$day = Carbon::parse($string)->format('d');
 			$year = Carbon::parse($string)->format('Y');
 		}

 		if(!isset($day) || !isset($month) || !isset($year))
		{
			return null;
		}

			switch ($month) {
			    case "enero":	//es
			    case "janvier": //fr
			    case "januar":	//de
			    case "gennaio": //it
			        $month = 1;
			        break;
			    case "febrero":
			    case "février": 
				case "februar":	
				case "febbraio": 
			        $month = 2;
			        break;
			    case "marzo":
			    case "mars": 
				case "märz":
				case "marzo":
			        $month = 3;
			        break;
			    case "abril":
			    case "avril": 
				case "april":
				case "aprile":
			        $month = 4;
			        break;
			    case "mayo":
			    case "mai": 
				case "kann":
				case "può":
				case "mei":
				case "maggio":
			        $month = 5;
			        break;
			    case "junio":
			    case "juin": 
				case "juni":	
				case "giugno":
			        $month = 6;
			        break;
			    case "julio":
			    case "juillet": 
				case "juli":
				case "luglio":
			        $month = 7;
			        break;
			    case "agosto":
			    case "août": 
				case "august":
				case "agosto":
			        $month = 8;
			        break;
			    case "septiembre":
			    case "septembre": 
				case "september":
				case "settembre":
			        $month = 9;
			        break;
			    case "octubre":
			    case "octobre": 
				case "oktober":	
				case "ottobre":
			        $month = 10;
			        break;
			    case "noviembre":
			    case "novembre": 
				case "november":	
				case "novembre":
			        $month = 11;
			        break;
			    case "diciembre":
			    case "décembre": 
				case "dezember":	
				case "dicembre":
			        $month = 12;
			        break;
			}

		$time = $day.'-'.$month.'-'.$year;
		
		$time = Carbon::parse($time)->format('Y-m-d');
 		return $time;
 	}
}
?>