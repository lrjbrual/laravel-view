<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignCountry extends Model
{
  protected $fillable = [
      'campaign_id',
      'country_id',
  ];
  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [

  ];

  public function campagin(){
    return $this->belongsTo('App\Campaign','campaign_id');
  }

  public function country(){
    return $this->setConnection('mysql')->belongsTo('Webpatser\Countries\Countries','country_id');
  }

  public function getRecords($fields=array('*'),$cond=array(),$order=array()){

      $q = DB::connection('mysql2')->table('campaign_countries');
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

    return CampaignCountry::setConnection('mysql2')->create([
        'campaign_id' => $data['campaign_id'],
        'country_id' => $data['country_id']
    ]);

  }

  public function deleteRecord(array $data)
  {
    return $affectedRows = CampaignCountry::setConnection('mysql2')->where('campaign_id', '=', $data['campaign_id'])
    ->delete();
  }

}
