<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReturnsReport extends Model
{
    
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql2';

	public function isExist($data=array()){
        $db_query = DB::connection('mysql2');
    	$query = $db_query->table('returns_reports')->select('*');
    	$query->where( 'detailed_disposition', $data['detailed_disposition'] );
    	$query->where( 'order_id', $data['order_id'] );
        $query->where( 'sku', $data['sku'] );
        $query->where( 'fnsku', $data['fnsku'] );
        $query->where( 'seller_id', $data['seller_id'] );
    	$data = $query->count();
    	if($data>0) return true;
    	else return false;
    }
    public function insertData($data = array()){
        $db_query = DB::connection('mysql2');
        if($data!=null AND count($data)>0){
            $db_query->table('returns_reports')->insert($data);
            return true;
        }else{
            return false;
        }
    }
    public function getRecords($fields = array('*'),$cond=array(),$order=array(),$checkifempty=false){
        $q = DB::connection('mysql2')->table('returns_reports');
        $q = $q->select($fields);

        if(count($cond)>0){
        end($cond);$last=key($cond);reset($cond);$first = key($cond);
        foreach($cond as $key => $c){
          if ($key === $first){
            reset($cond);
          }
          $thiskey = key($cond);
          if(count($c)>1) $q = $q->where($thiskey,$c[0],$c[1]);
          else $q = $q->where($thiskey,$c);
          next($cond);
        }
        }

        if(count($order)>0){
        $q = $q->orderBy($order[0],$order[1]);
        }

        if($checkifempty){
             $q = $q->limit(1)->get()->count();
        }else{
             $q = $q->get();
        }
        return $q;
    }
}
