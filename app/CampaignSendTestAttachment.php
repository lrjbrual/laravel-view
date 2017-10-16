<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignSendTestAttachment extends Model
{
    //
    protected $connection = 'mysql2';
    protected $fillable = [
        'path',
        'original_filename',
    ];

    public function getRecords($fields=array('*'),$cond=array(),$order=array()){

        $q = DB::connection('mysql2')->table('campaign_send_test_attachments');
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

      return CampaignSendTestAttachment::setConnection('mysql2')->create([
        'path'=>$data['path'],
        'original_filename'=>$data['original_filename']
      ]);

    }

    // public function deleteRecordBy(array $data)
    // {
    //   $q = CampaignTemplateAttachment::setConnection('mysql2')
    //
    //   foreach($data['cond'] as $d){
    //     $q = $q->where('id', '!=', $d);
    //   }
    //   // ->where(function ($query) {
    //   //
    //   // })
    //   return $q->delete();
    // }
}
