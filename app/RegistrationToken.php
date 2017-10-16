<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RegistrationToken extends Model
{
    //
    protected $fillable = [
        'email',
        'token',
        'expiration',
        'status'
    ];

    protected $hidden = [

    ];

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

      return RegistrationToken::create([
          'email' => $data['email'],
          'token' => $data['token'],
          'expiration' => $data['date_plus1day'],
          'status' => false,
      ]);

    }
}
