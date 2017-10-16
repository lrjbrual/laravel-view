<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignTemplateAttachment extends Model
{
  protected $fillable = [
      'campaign_template_id',
      'path',
      'original_filename',
  ];
  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [

  ];

  public function campaign_template()
  {
    return $this->belongsTo('App\CampaignTemplate', 'campaign_template_id');
  }


  public function getRecords($fields=array('*'),$cond=array(),$order=array()){

      $q = DB::connection('mysql2')->table('campaign_template_attachments');
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

    return CampaignTemplateAttachment::setConnection('mysql2')->create([
      'campaign_template_id'=>$data['campaign_template_id'],
      'path'=>$data['path'],
      'original_filename'=>$data['original_filename']
    ]);

  }


    public function deleteRecordByCampaignTemplateId(array $data)
    {
      $q = CampaignTemplateAttachment::setConnection('mysql2')
      ->where('campaign_template_id', '=', $data['campaign_template_id']);

      foreach($data['cond'] as $d){
        $q = $q->where('id', '!=', $d);
      }
      // ->where(function ($query) {
      //
      // })
      return $q->delete();
    }

  public function updateRecord(array $data)
  {
    CampaignTemplateAttachment::setConnection('mysql2')
    ->where('id', $data['id'])
    ->update($data);
  }
}
