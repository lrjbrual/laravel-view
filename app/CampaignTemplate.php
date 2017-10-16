<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignTemplate extends Model
{
    protected $connection = 'mysql2';

    protected $fillable = [
        'seller_id',
        'template_name',
        'days_delay',
        'campaign_trigger_id',
        'subject',
        'email_body',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];
    protected $visible = [
      'seller_id',
      'template_name',
      'days_delay',
      'campaign_trigger_id',
      'subject',
      'email_body',
    ];

    public function seller()
    {
      return $this->setConnection('mysql')->belongsTo('App\Seller', 'seller_id');
    }

    public function campaign_trigger()
    {
      return $this->belongsTo('App\CampaignTrigger', 'campaign_trigger_id');
    }

    public function campaign_template_attachment()
    {
      return $this->hasMany('App\CampaignTemplateAttachment', 'campaign_template_id');
    }


    public function getRecords($fields=array('*'),$cond=array(),$order=array()){

        $q = DB::connection('mysql2')->table('campaign_templates');
        $q = $q->select($fields);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }

        if(count($order)>0){
          $q = $q->orderBy($order[0],$order[1]);
        }

        $q = $q->get();
        return $q;
    }

    public function insertRecord(array $data)
    {
      return CampaignTemplate::setConnection('mysql2')->create([
        'seller_id'=>$data['seller_id'],
        'template_name'=>$data['template_name'],
        'days_delay'=>$data['days_delay'],
        'campaign_trigger_id'=>$data['campaign_trigger_id'],
        'subject'=>$data['subject'],
        'email_body'=>$data['email_body']
      ]);

    }
}
