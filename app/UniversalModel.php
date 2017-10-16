<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UniversalModel extends Model
{
    //
    public function isExist($table, $where=array(),$dbconnection='mysql2'){
      $db_query = DB::connection($dbconnection);
      $query = $db_query->table($table)->select('id');
        foreach ($where as $key => $value) {
            $query->where( $key, $value );
        }
      $data = $query->count();
      if($data>0) return true;
      else return false;
    }

    public function insertData($table, $data = array(),$dbconnection='mysql2'){
        $db_query = DB::connection($dbconnection);
        if($data!=null AND count($data)>0){
            $db_query->table($table)->insert($data);
            return true;
        }else{
            return false;
        }
    }

    public function insertData_return_id($table, $data = array(),$dbconnection='mysql2'){
        $db_query = DB::connection($dbconnection);
        if(!isset($data['created_at'])){
          $data['created_at'] = date('Y-m-d H:i:s');
        }
        if($data!=null AND count($data)>0){
            return $db_query->table($table)->insertGetId($data);
        }else{
            return false;
        }
    }

    public function getRecords($table, $fields = array('*'),$cond=array(),$order=array(),$isLimit=false,$dbconnection='mysql2'){

       // DB::connection('mysql2')->enableQueryLog();
        $q = DB::connection($dbconnection)->table($table);
        $q = $q->select($fields);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            if($thiskey == 'whereBetween') $q = $q->whereBetween($c[0], $c[1]);
            else if($thiskey == 'whereIn') $q = $q->whereIn($c[0], $c[1]);
            else if($thiskey == 'orLike') $q = $q->orWhere($c[0], 'LIKE', '%'.$c[1].'%');
            else if($thiskey == 'like') $q = $q->where($c[0], 'LIKE', '%'.$c[1].'%');
            else if(count($c)>1){
              $q = $q->where($thiskey,$c[0],$c[1]);
            }
            else $q = $q->where($thiskey,$c);
            next($cond);
          }
        }

        if(count($order)>0){
          $q = $q->orderBy($order[0],$order[1]);
        }
        if($isLimit){
          $q = $q->limit(1);
        }

         $q = $q->get();
          //print_r(DB::connection('mysql2')->getQueryLog());
        return $q;
    }
    public function updateData($table, $where, $data,$dbconnection='mysql2'){
      $q = DB::connection($dbconnection)->table($table);
      foreach ($where as $key => $value) $q = $q->where($key,$value);
      $q = $q->update($data);
    }
}
