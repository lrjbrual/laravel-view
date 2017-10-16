<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Campaign extends Model
{
  protected $connection = 'mysql2';
  protected $fillable = [
      'seller_id',
      'campaign_type',
      'campaign_name',
      'is_active',
      'is_deleted'
  ];
  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [

  ];

  public function campaign_email()
  {
    return $this->setConnection('mysql2')->hasMany('App\CampaignEmail', 'campaign_id');
  }

  public function active_campaign_email($seller_id)
  {
    return $this->setConnection('mysql2')
            ->hasMany('App\CampaignEmail', 'campaign_id')
            ->where('is_active', true)
            ->where('seller_id', $seller_id)
            ->where('is_deleted', false);
  }

  public function seller_campaign_country()
  {
    return $this->setConnection('mysql2')
            ->hasMany('App\CampaignCountry', 'campaign_id');
  }

  public function campaign_country()
  {
    return $this->hasMany('App\CampaignCountry', 'campaign_id');
  }

  public function emails()
  {
    return $this->hasMany('App\CampaignEmail', 'campaign_id');
  }

  public function getRecords($fields=array('*'),$cond=array(),$order=array()){

      $q = DB::connection('mysql2')->table('campaigns');
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

  public function getRecordJoinCampaignEmails($fields=array('*'),$cond=array(),$order=array()){

      $q = DB::connection('mysql2')->table('campaigns');
      $q = $q->select($fields);
      $q = $q->leftjoin('campaign_emails', 'campaigns.id', '=', 'campaign_emails.campaign_id');

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

    return Campaign::setConnection('mysql2')->create([
        'seller_id' => $data['seller_id'],
        'campaign_type' => '1',
        'campaign_name' => $data['campaign_name'],
        'is_active' => 0,
        'is_deleted' => 0
    ]);

  }

  public function updateRecord(array $data)
  {
    Campaign::setConnection('mysql2')
    ->where('id', $data['id'])
    ->update($data);
  }


}
