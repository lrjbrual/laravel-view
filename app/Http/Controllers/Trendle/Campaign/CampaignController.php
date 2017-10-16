<?php

namespace App\Http\Controllers\Trendle\Campaign;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Auth;
use froala\froalaeditor\FroalaEditorWidget;
use Mail;
use App\Mail\CampaignSendTest;
use App\User;
use App\Seller;
use App\Marketplace;
use App\MarketplaceCountry;
use App\Campaign;
use App\CampaignTrigger;
use App\CampaignTemplate;
use App\CampaignCountry;
use App\CampaignEmail;
use App\CampaignEmailAttachment;
use App\CampaignTemplateAttachment;
use App\EmailTag;
use App\CampaignSendTestAttachment;
use Storage;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;

class CampaignController extends Controller
{
  private $campaign_template;
  private $mkp_c;
  private $campaign;
  private $campaign_trigger;
  private $campaign_country;
  private $campaign_email;
  private $campaign_email_att;
  private $campaign_temp_att;

  public function __construct()
  {

    $this->mkp_c = new MarketplaceCountry;
    $this->campaign_country = new CampaignCountry();
    $this->campaign_template = new CampaignTemplate();
    $this->campaign_trigger = new CampaignTrigger;
    $this->campaign = new Campaign;
    $this->campaign_email = new CampaignEmail;
    $this->campaign_email_att = new CampaignEmailAttachment;
    $this->campaign_temp_att = new CampaignTemplateAttachment;
    $this->campaign_sendtest_att = new CampaignSendTestAttachment;

    $this->middleware('auth');
    $this->middleware('checkStripe');
  }


  public function index(){
    $seller_id = Auth::user()->seller_id;
    $data = $this->callBaseSubscriptionName($seller_id);
    $campaigns = Campaign::where('is_deleted', 0)->where('seller_id', $seller_id)->get();
    return view('trendle.campaign.index')
          ->with('campaigns', $campaigns)
          ->with('bs',$data->base_subscription);
  }


  public function newCampaign($t_id=0){

      $seller_id = Auth::user()->seller_id;
      $f=array("*");
      $c=array('id'=>$t_id,'seller_id'=>$seller_id);
      $o=array();
      $campaigntemplatedata=$this->campaign_template->getRecords($f,$c,$o);

      $countrymkp = $this->mkp_c->getMarketplaceCountryName();

      $campaign_trigger = $this->campaign_trigger->getRecords();

      foreach($campaigntemplatedata as $key => $ctd){
        $ctd_id = $ctd->id;
        $c=array('campaign_template_id'=>$ctd_id);
        $d4=$this->campaign_temp_att->getRecords($f,$c,$o);
        $campaigntemplatedata{$key}->att_data=$d4;
      }

      $arr = array(
        'type'=>'1',
        'countrymkp'=>$countrymkp,
        'campaigntemplatedata'=>$campaigntemplatedata,
        'campaign_trigger'=>$campaign_trigger,
        'campaign_country'=>'',
        'campaign_id'=>0,
        'tab_index'=>'n1',
        'mode'=>'new',
      );

      $data = $this->callBaseSubscriptionName($seller_id);

      return view('trendle.campaign.campaignemail',$arr)
              ->with('bs',$data->base_subscription);
  }

  public function loadCampaign($c_id=0){

      $seller_id = Auth::user()->seller_id;
      $f=array("*");
      $c=array('campaigns.id'=>$c_id,'campaigns.seller_id'=>$seller_id);
      $o=array();
      $campaigntemplatedata=$this->campaign->getRecordJoinCampaignEmails($f,$c,$o);


      $countrymkp = $this->mkp_c->getMarketplaceCountryName();
      $c=array('campaign_id'=>$c_id);
      $campaign_country = $this->campaign_country->getRecords($f,$c,$o);
      $campaign_trigger = $this->campaign_trigger->getRecords();


      foreach($campaigntemplatedata as $key => $ctd){
        $ctd_id = $ctd->id;
        $c=array('campaign_email_id'=>$ctd_id);
        $d4=$this->campaign_email_att->getRecords($f,$c,$o);
        $campaigntemplatedata{$key}->att_data=$d4;
      }



      $arr = array(
        'type'=>'1',
        'countrymkp'=>$countrymkp,
        'campaigntemplatedata'=>$campaigntemplatedata,
        'campaign_trigger'=>$campaign_trigger,
        'campaign_country'=>$campaign_country,
        'campaign_id'=>$c_id,
        'tab_index'=>'n1',
        'mode'=>'load',
      );

      $data = $this->callBaseSubscriptionName($seller_id);

      return view('trendle.campaign.campaignemail',$arr)
              ->with('bs',$data->base_subscription);
  }

  public function saveCampaign(Request $request){

		$dtime=date('Y-m-d H:i:s');

		$seller_id = Auth::user()->seller_id;
		$campaignname = $request->campaignname;
		$mkp = $request->mkp;
		$cid = $request->cid;

    $q_arr = array(
      'seller_id'=>$seller_id,
      'campaign_name'=>$campaignname,
      'id'=>$cid
    );



		$f=array('*');
		$c=array('id'=>$cid);
		$o=array();

		$q = $this->campaign->getRecords($f,$c,$o);

    if (count($q)) {
      // $arr=array(
      //   'id'=>1,
      //   'seller_id' => 1,
      //   'campaign_type' => '1',
      //   'campaign_name' => 'ssss',
      //   'is_active' => 0,
      //   'is_deleted' => 0
      // );
      // $this->campaign->updateRecord($arr);die();
      // print_r($q_arr);
      $this->campaign->updateRecord($q_arr);
    }else{
      $cid = $this->campaign->insertRecord($q_arr)->id;
    }




		$arr = array(
		'campaign_id'=>$cid
		);
		$this->campaign_country->deleteRecord($arr);

		if(is_array($mkp)){
			foreach($mkp as $m){
				$arr = array(
				'campaign_id'=>$cid,
				'country_id'=>$m
				);
				$this->campaign_country->insertRecord($arr);
			}
		}

		echo $cid;

	}


  public function saveTemplate(Request $request){

		$dtime=date('Y-m-d H:i:s');
    $seller_id = Auth::user()->seller_id;
    $templatename = $request->templatename;
    $delayval = $request->delayval;
    $eventval = $request->eventval;
    $subject = $request->subject;
    $body = $request->body;
    $mode = $request->mode;
    $cid = $request->cid;
    $tid = $request->tid;
    $isactive = $request->isactive;
    $to = $request->to;
    $loadmode = $request->loadmode;

		if($mode=='preftemp'){

			$arr = array(
			'seller_id'=>$seller_id,
			'template_name'=>$templatename,
			'days_delay'=>$delayval,
			'campaign_trigger_id'=>$eventval,
			'subject'=>$subject,
			'email_body'=>$body
			);
      $pt_id = $this->campaign_template->insertRecord($arr)->id;
      echo $pt_id;

    }else if($mode=='savetemp'){

			if(($tid==0)||($loadmode=='new')){
        
        $arr = array(
        'campaign_id'=>$cid,
        'template_name'=>$templatename,
        'days_delay'=>$delayval,
        'campaign_trigger_id'=>$eventval,
        'subject'=>$subject,
        'email_body'=>$body,
        'is_active'=>$isactive
        );

        $ce_id = $this->campaign_email->insertRecord($arr)->id;
        echo $ce_id;

			}else{
        $arr = array(
        'id'=>$tid,
        'campaign_id'=>$cid,
        'template_name'=>$templatename,
        'days_delay'=>$delayval,
        'campaign_trigger_id'=>$eventval,
        'subject'=>$subject,
        'email_body'=>$body,
        'is_active'=>$isactive
        );

        $this->campaign_email->updateRecord($arr);
        echo $tid;

			}

    }

  }

  public function newTabContent($tab_index){

		  $campaign_trigger = $this->campaign_trigger->getRecords();

      $arr=array(
        'campaign_trigger'=>$campaign_trigger,
        'tab_index'=>$tab_index,
        'isClassActive'=>'active',
        'mode'=>'new',
      );
      // echo $tab_index;
  		echo view('trendle.campaign.partials._campaign_email_tab_content_section',$arr);

	}



  public function getCampaignTemplateList(Request $request){

    $seller_id = Auth::user()->seller_id;
    $seller = Seller::find($seller_id);

    $templates = $seller->campaign_temp;
    // print_r($templates[0]->template_name); die();
    $result=array();

    $row["recid"]=0;
    $row['dateCreatedColumn'] = '';
    $row['templateNameColumn'] = 'Blank Template';
    $result[] = $row;

    foreach($templates as $ct){
      $row["recid"] =  $ct->id;
      $row['dateCreatedColumn'] = $ct->created_at.' ';
      $row['templateNameColumn'] = $ct->template_name;
      $result[] = $row;
    }
    $data_response["data"] = $result;
    echo json_encode($data_response);
  }

  public function doUpload(Request $request){

    $exec_mode = $request->exec_mode;
    // $ret['mode']=$exec_mode;
    // echo json_encode($ret);
    // die();
    if(empty($_FILES)) {
      echo 'noupload';
      // return false;
    }

    $this->file = $_FILES["myfile"];
    if(!file_exists($this->file['tmp_name']) || !is_uploaded_file($this->file['tmp_name'])){
        $this->errors['FileNotExists'] = true;
        echo 'noupload';
        // return false;
    }else{
      $file = $request->file('myfile');

      $seller_id = Auth::user()->seller_id;
      $tid = $request->tid;
      $temp_tid_email = $request->temp_tid_email;
      $temp_tid_template = $request->temp_tid_template;

      if($exec_mode=='savetemp'){
        $fkfield = 'campaign_template_id';
        $fkval = $temp_tid_template;
        $mode='new';
      }else if($exec_mode=='saveeml'){
        $fkfield = 'campaign_email_id';
        $fkval = $temp_tid_email;
        $mode='load';
      }else if($exec_mode=='sendtest'){
        $fkfield = '';
        $fkval = '';
        $mode='test';
      }


      $destinationPath = '/app/crm_uploads/campaign/';
      if(isset($_FILES["myfile"]))
  		{
        $ret = array();
        $error =$_FILES["myfile"]["error"];
        if(!is_array($_FILES["myfile"]["name"])) //single file
  			{
          $old_fileName = $file->getClientOriginalName();
  				$size = $file->getSize();
          $new_fileName = round(microtime(true) * 1000) . (rand(1,1000)) . '_' . $seller_id .'.'.strtolower(pathinfo($_FILES["myfile"]['name'], PATHINFO_EXTENSION));

          $file->move(storage_path().$destinationPath, $new_fileName);


          $arr=array(
            $fkfield=>$fkval,
  					'path'=>$new_fileName,
  					'original_filename'=>$old_fileName
  				);

          if($exec_mode=='savetemp'){
            $ret[0]['id']=$this->campaign_temp_att->insertRecord($arr)->id;
          }else if($exec_mode=='saveeml'){
            $ret[0]['id']=$this->campaign_email_att->insertRecord($arr)->id;
          }else if($exec_mode=='sendtest'){
            $ret[0]['id']=$this->campaign_sendtest_att->insertRecord($arr)->id;
          }


  				$ret[0]['oldname']=$old_fileName;
          $ret[0]['newname']=$new_fileName;


        }else  //Multiple files, file[]
  			{

          $fileCount = count($_FILES["myfile"]["name"]);

          for($i=0; $i < $fileCount; $i++)
  			  {
            $old_fileName = $file->getClientOriginalName();
            $size = $file->getSize();
            $new_fileName = round(microtime(true) * 1000) . (rand(1,1000)) . '_' . $seller_id .'.'.strtolower(pathinfo($_FILES["myfile"]['name'], PATHINFO_EXTENSION));

            $file->move(storage_path().$destinationPath, $new_fileName);


    				$arr=array(
              $fkfield=>$fkval,
    					'path'=>$new_fileName,
    					'original_filename'=>$old_fileName,
    				);

            if($exec_mode=='savetemp'){
              $ret[0]['id']=$this->campaign_temp_att->insertRecord($arr)->id;
            }else if($exec_mode=='saveeml'){
              $ret[0]['id']=$this->campaign_email_att->insertRecord($arr)->id;
            }else if($exec_mode=='sendtest'){
              $ret[0]['id']=$this->campaign_sendtest_att->insertRecord($arr)->id;
            }

    				$ret[$i]['oldname']=$old_fileName;
            $ret[$i]['newname']=$new_fileName;

  			  }

        }


        $ret['mode']=$mode;
        echo json_encode($ret);

      }

    }

  }

  public function removeCampaignEmail(Request $request){
    $seller_id = Auth::user()->seller_id;
    $tid = $request->tid;
    $cid = $request->cid;

    $arr=array(
      'id'=>$tid,
      'campaign_id'=>$cid,
      'seller_id'=>$seller_id,
    );


    echo $this->campaign_email->deleteRecord($arr);
  }

  public function manageAttachment(Request $request){

    $old_atts = $request->old_atts;
    $mode = $request->mode;
    $tid = $request->tid;

    $included_eml_att=array();
    $included_temp_att=array();
    $included_sendtest_att=array();
    if(is_array($old_atts)){

      foreach($old_atts as $oa){
        if($mode=='savetemp'){
          $included_eml_att[]=$oa['id'];
        }else if($mode=='preftemp'){
          $included_temp_att[]=$oa['id'];
        }else if($mode=='sendtest'){
          $included_sendtest_att[]=$oa['id'];
        }


      }
    }

    if($mode=='savetemp'){
      $arr_eml=array(
        'campaign_email_id'=>$tid,
        'cond'=>$included_eml_att
      );
      // print_r($arr_eml);
      $this->campaign_email_att->deleteRecordByCampaignEmailId($arr_eml);

      if(is_array($old_atts)){
        foreach($old_atts as $oa){
          $f=array("*");
          $c=array('id'=>$oa['id']);
          $o=array();

          if($oa['loadmode']=='load'){
            // dont insert coz madodoble ugn record sa att table
            // $q = $this->campaign_email_att->getRecords($f,$c,$o);
          }else if($oa['loadmode']=='new'){
            $q = $this->campaign_temp_att->getRecords($f,$c,$o);
            $arr=array(
              'campaign_email_id'=>$tid,
              'path'=>$q{0}->path,
              'original_filename'=>$q{0}->original_filename,
            );

            $this->campaign_email_att->insertRecord($arr);
          }else if($oa['loadmode']=='test'){
            $q = $this->campaign_sendtest_att->getRecords($f,$c,$o);
            $arr=array(
              'campaign_email_id'=>$tid,
              'path'=>$q{0}->path,
              'original_filename'=>$q{0}->original_filename,
            );
            $this->campaign_email_att->insertRecord($arr);
          }
        }
      }
    }else if($mode=='preftemp'){
      $arr_temp=array(
        'campaign_template_id'=>$tid,
        'cond'=>$included_temp_att
      );
      $this->campaign_temp_att->deleteRecordByCampaignTemplateId($arr_temp);

      if(is_array($old_atts)){
        foreach($old_atts as $oa){
          $f=array("*");
          $c=array('id'=>$oa['id']);
          $o=array();

          if($oa['loadmode']=='load'){
            $q = $this->campaign_email_att->getRecords($f,$c,$o);
            $arr=array(
              'campaign_template_id'=>$tid,
              'path'=>$q{0}->path,
              'original_filename'=>$q{0}->original_filename,
            );

            $this->campaign_temp_att->insertRecord($arr);
          }else if($oa['loadmode']=='new'){
            // dont insert coz madodoble ugn record sa att table
            // $q = $this->campaign_temp_att->getRecords($f,$c,$o);
          }else if($oa['loadmode']=='test'){
            $q = $this->campaign_sendtest_att->getRecords($f,$c,$o);
            $arr=array(
              'campaign_email_id'=>$tid,
              'path'=>$q{0}->path,
              'original_filename'=>$q{0}->original_filename,
            );
            $this->campaign_temp_att->insertRecord($arr);

          }


        }
      }


    }else if($mode=='sendtest'){

      $arr_eml=array(
        'cond'=>$included_sendtest_att
      );


      // $this->campaign_sendtest_att->deleteRecord($arr_temp);
      $r = array();
      if(is_array($old_atts)){
        foreach($old_atts as $oa){
          $f=array("*");
          $c=array('id'=>$oa['id']);
          $o=array();

          if($oa['loadmode']=='load'){
            $q = $this->campaign_email_att->getRecords($f,$c,$o);
            $arr=array(
              // 'campaign_template_id'=>$tid,
              'path'=>$q{0}->path,
              'original_filename'=>$q{0}->original_filename,
            );

            $r[] = $this->campaign_sendtest_att->insertRecord($arr)->id;
          }else if($oa['loadmode']=='new'){
            $q = $this->campaign_temp_att->getRecords($f,$c,$o);
            $arr=array(
              'campaign_email_id'=>$tid,
              'path'=>$q{0}->path,
              'original_filename'=>$q{0}->original_filename,
            );

            $r[] = $this->campaign_sendtest_att->insertRecord($arr)->id;

          }else if($oa['loadmode']=='test'){

          }
        }
      }
    }
  }


    public function setStatus(Request $request)
    {
        $campaign = Campaign::find($request->id);
        $campaign->is_active = $request->status;
        $campaign->save();

        return $campaign->is_active == 1 ? 'active' : 'inactive';
    }

    public function getEmailTags()
    {
        $tags = EmailTag::all();

        $seller_id = Auth::user()->seller_id;
        $data = $this->callBaseSubscriptionName($seller_id);

        return view('trendle.campaign.tags')
              ->with('tags', $tags)
              ->with('bs',$data->base_subscription);
    }

    public function getEmailBodyTags()
    {
        $tags = EmailTag::all();

        $seller_id = Auth::user()->seller_id;
        $data = $this->callBaseSubscriptionName($seller_id);

        return view('trendle.campaign.bodytags')
              ->with('tags', $tags)
              ->with('bs',$data->base_subscription);
    }

  public function sendTest(Request $request){

    echo Mail::to($request->to)->send(new CampaignSendTest($request));

  }

  public function deleteCampaign(Request $request){

    $campaign = Campaign::find($request->id);
    $campaign->is_deleted = 1;
    $campaign->save();
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
