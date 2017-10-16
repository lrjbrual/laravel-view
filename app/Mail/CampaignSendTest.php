<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Http\Request;

use Storage;
use App\CampaignEmailAttachment;
use App\CampaignTemplateAttachment;
use App\CampaignSendTestAttachment;

class CampaignSendTest extends Mailable
{
    use Queueable, SerializesModels;
    public $mailcontent;
    public $subject;
    public $att;
    // public $to;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    // public function __construct()
    public function __construct(Request $data)
    {
      $this->mailcontent = $data->body;
      $this->subject = $data->subject;
      $this->att = $data->old_atts;

      $this->campaign_email_att = new CampaignEmailAttachment;
      $this->campaign_temp_att = new CampaignTemplateAttachment;
      $this->campaign_sendtest_att = new CampaignSendTestAttachment;


      // $this->to = $data->to;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

      $realpath = env('UPLOADS_LOCATION');

      $q = $this->view('emails.campaignsendtest');
      $q = $this->from('crm@trendle.io');
      $q = $this->subject($this->subject);

      if(is_array($this->att)){

        foreach($this->att as $att){

          $f=array("*");
          $c=array('id'=>$att['id']);
          $o=array();

          if($att['loadmode']=='new'){
            $q = $this->campaign_temp_att->getRecords($f,$c,$o);

          }else if($att['loadmode']=='load'){
            $q = $this->campaign_email_att->getRecords($f,$c,$o);

          }else if($att['loadmode']=='test'){
            $q = $this->campaign_sendtest_att->getRecords($f,$c,$o);

          }
          print_r($q);
          echo $realpath . $q[0]->path;
          $q = $this->attach($realpath . $q[0]->path,['as' => $q[0]->original_filename]);
        }

      }


      return $q;
    }
}
