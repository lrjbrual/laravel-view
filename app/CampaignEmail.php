<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignEmail extends Model
{
  protected $connection = 'mysql2';
  protected $fillable = [
      'campaign_id',
      'template_name',
      'days_delay',
      'campaign_trigger_id',
      'subject',
      'email_body',
      'is_active',
      'is_deleted',
      'exclude_blacklist',
  ];
  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [

  ];

  public function campaign(){
    return $this->belongsTo('App\Campaign','campaign_id');
  }

  public function campaign_trigger()
  {
    return $this->setConnection('mysql')->belongsTo('App\CampaignTrigger', 'campaign_trigger_id');
  }

  public function campaign_email_attachment()
  {
    return $this->hasMany('App\CampaignEmailAttachment', 'campaign_email_id');
  }


  public function getRecords($fields=array('*'),$cond=array(),$order=array()){

      $q = DB::connection('mysql2')->table('campaign_emails');
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

    return CampaignEmail::setConnection('mysql2')->create([
      'campaign_id'=>$data['campaign_id'],
      'template_name'=>$data['template_name'],
      'days_delay'=>$data['days_delay'],
      'campaign_trigger_id'=>$data['campaign_trigger_id'],
      'subject'=>$data['subject'],
      'email_body'=>$data['email_body'],
      'is_active'=>$data['is_active'],
      'is_deleted'=>0,
      'exclude_blacklist'=>0
    ]);

  }


  public function updateRecord(array $data)
  {
    CampaignEmail::setConnection('mysql2')
    ->where('id', $data['id'])
    ->update($data);
  }

  public function deleteRecord(array $data)
  {
    // $arr=array(
    //   'id'=>$tid,
    //   'campaign_id'=>$cid,
    //   'seller_id'=>$seller_id,
    // );

    $q = "DELETE t2 FROM campaign_emails t2 join campaigns t1 on t1.id=t2.campaign_id where
    t1.seller_id = '".$data['seller_id']."'
    and t2.campaign_id = '".$data['campaign_id']."'
    and t2.id = '".$data['id']."'
    ";
    $status = \DB::connection('mysql2')->delete($q);
    return $status;
  }

}
