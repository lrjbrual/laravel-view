<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SellerReview extends Model
{
    
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql2';

    public function comments(){
    	return $this->hasMany('App\SellerReviewComment', 'seller_review_id');
    }

    public function isExist($data = array()){
        $db_query = DB::connection('mysql2');
    	$query = $db_query->table('seller_reviews')->select('*');
    	$query->where( 'order_number', $data['order_number'] );
        $query->where( 'review_comment', $data['review_comment'] );
        $query->where( 'seller_id', $data['seller_id'] );
    	$data = $query->count();
    	if($data>0) return true;
    	else return false;
    }

    public function insertData($data = array()){
        $db_query = DB::connection('mysql2');
        if($data!=null AND count($data)>0){
            $db_query->table('seller_reviews')->insert($data);
            return true;
        }else{
            return false;
        }
    }

    public function getAllByFilter($request,$seller_id){
        $type="";
        $action="";
        $datef = '1970-01-01 00:00:00';
        $datet = date('Y-m-d').' 23:59:59';
        if(isset($request->display_type))  $type = $request->display_type;
        if(isset($request->action))  $action = $request->action;
        //DB::connection('mysql2')->enableQueryLog();
        //echo $type;
        //Session::flash('message', $type);
        $query = DB::connection('mysql2')->table('seller_reviews')->select('*');
        if(isset($request->date_from)){
            if($request->date_from != "" AND $request->date_from != " " AND $request->date_from != null ){
                $df = explode("/",$request->date_from);
                $df = $df[2]."-".$df[0]."-".$df[1]." 00:00:00";
                $datef=$df;
                $query->where( 'review_date', '>=', $df );
            }
        }
        if(isset($request->date_to)){
            if($request->date_to != "" AND $request->date_to != " " AND $request->date_to != null ){
                $dt = explode("/",$request->date_to);
                $dt = $dt[2]."-".$dt[0]."-".$dt[1]." 00:00:00";
                $datet = $dt;
                $query->where( 'review_date', '<=', $dt );
            }
        }
        //$query->whereBetween('review_date', [$datef, $datet]);
        if(isset($request->rating_from)){
            if($request->rating_from != "" AND $request->rating_from != " " AND $request->rating_from != null  AND $request->rating_to > 0 )
                $query->where( 'reviewer_rating', '>=', $request->rating_from );
        }
        if(isset($request->rating_to)){
            if($request->rating_to != "" AND $request->rating_to != " " AND $request->rating_to != null  AND $request->rating_to > 0 )
                $query->where( 'reviewer_rating', '<=', $request->rating_to );
        }
        if(isset($request->countries)){
            $countries = rtrim($request->countries, "-");
            $countries = explode('-', $countries);
            // for ($i=0; $i < count($countries); $i++) {
            //     if($i==0) $query->where('country', $countries[$i]);
            //     else $query->orWhere('country', $countries[$i]);
            // }
            if(count($countries)>0){
                $query->whereIn('country', $countries);
            }
        }
        if(isset($request->text_filter)){
            if($type=='sku' OR $type==null OR $type=='')
                $field = 'sku';
            else if($type=='asin')
                $field = 'asin';
            else if($type=='product_name')
                $field = 'product_name';
            else
                $field = 'sku';

            if($request->text_filter != "" AND $request->text_filter != " " AND $request->text_filter != null )
                $query->where( $field, 'like', '%' . $request->text_filter . '%' );
        }
        if($action == ""){
            $query->where(function ($query) {
                $query->where('action_date', '0000-00-00 00:00:00')
                      ->orwhereNull('action_date');
            });
        }else if($action == 'archive'){
            $query->where( 'action_date',  '1970-01-01 00:00:00' );
        }else if($action == 'later'){
            $query->where('action_date', '>', '1970-01-01 00:00:00' );
        }else if($action == 'inbox'){
            $query->Where(function ($query) {
                $query->where('action_date', '0000-00-00 00:00:00')
                      ->orwhereNull('action_date');
            });
        }else{
            $query->Where(function ($query) {
                $query->where('action_date', '0000-00-00 00:00:00')
                      ->orwhereNull('action_date');
            });
        }
        $query->where( 'seller_id',  $seller_id );
        $q = $query->get();
        // print_r(DB::connection('mysql2')->getQueryLog());
        return $q;

    }

    public function getRecords($fields = array('*'),$cond=array(),$order=array()){
        $q = DB::connection('mysql2')->table('seller_reviews');
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

        $q = $q->get();
        return $q;
    }
}
