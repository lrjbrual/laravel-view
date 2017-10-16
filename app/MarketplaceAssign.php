<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarketplaceAssign extends Model
{
    //
    //
    protected $fillable = [
        'seller_id',
        'marketplace_id',
        'mws_seller_id',
        'mws_auth_token'
    ];

    protected $hidden = [

    ];

    public function test(){
      echo "This is a test function";
    }


    public function getRecords($table="",$fields=array('*'),$cond=array(),$order=array()){

        $q = DB::table($table);
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

      return MarketplaceAssign::create([
          'seller_id' => $data['sys_sellerid'],
          'marketplace_id' => $data['mkpid'],
          'mws_seller_id' => $data['mkp_sellerid'],
          'mws_auth_token' => $data['mkp_authtoken']
      ]);

    }

    public function deleteRecord(array $data)
    {
      return $affectedRows = MarketplaceAssign::where('seller_id', '=', $data['sys_sellerid'])
      ->where('marketplace_id', '=', $data['mkpid'])
      ->delete();
    }
}
