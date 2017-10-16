<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignEmailAttachment extends Model
{
    protected $connection = 'mysql2';
    protected $fillable = [
        'campaign_email_id',
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

    public function campaign_email()
    {
      return $this->belongsTo('App\CampaignEmail', 'campaign_email_id');
    }


    public function getRecords($fields=array('*'),$cond=array(),$order=array()){

        $q = DB::connection('mysql2')->table('campaign_email_attachments');
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

      return CampaignEmailAttachment::setConnection('mysql2')->create([
        'campaign_email_id'=>$data['campaign_email_id'],
        'path'=>$data['path'],
        'original_filename'=>$data['original_filename']
      ]);

    }

    public function deleteRecordByCampaignEmailId(array $data)
    {

      $q = CampaignEmailAttachment::setConnection('mysql2')
      ->where('campaign_email_id', '=', $data['campaign_email_id']);

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
      CampaignEmailAttachment::setConnection('mysql2')
      ->where('id', $data['id'])
      ->update($data);
    }
}
