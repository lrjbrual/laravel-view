<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\SellerReview;
use App\SellerReviewFilter;
use App\SellerReviewComment;
use Route;
use Session;
use Auth;
use Carbon\Carbon;
use App\BaseSubscriptionSeller;
use App\BaseSubscriptionSellerTransaction;

class SellerReviewController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkStripe');
    }

    public function index()
    {
 		//$seller_reviews = SellerReview::all();
      	//return view('trendle.sellerreview.sellerreview', array('reviews' => $seller_reviews));        
        $seller_id = Auth::user()->seller_id;
        $data = $this->callBaseSubscriptionName($seller_id);
        return view('trendle.sellerreview.sellerreview')
              ->with('bs',$data->base_subscription);
    }
     public function create()
    {
        //
    }
    public function list(){

    }

    public function filter_reviews(Request $request){
        $q = new SellerReview;
        $seller_id = Auth::user()->seller_id;
        $seller_reviews = $q->getAllByFilter($request, $seller_id);
        $datas = array();
        $type = $request->display_type;
        $action = $request->action;
        $move_to = '';
        foreach ($seller_reviews as $key => $value) {
            $url_ext = strtolower($value->country);
            if($url_ext == 'us') $url_ext = 'com';
            elseif ($url_ext == 'uk') $url_ext = 'co.uk';
            $data = array();
            $data['rowID'] = $value->id;
            $data[] = date('d-m-Y',strtotime($value->review_date));
            $data[] = strtoupper($value->country);
            if($type=='sku' OR $type==null OR $type=='')
                $data[] = '<a target="_blank" href="https://www.amazon.'.$url_ext.'/dp/'.$value->asin.'" >'.$value->sku.'</a>';
            else if($type=='asin')
                $data[] = '<a target="_blank" href="https://www.amazon.'.$url_ext.'/dp/'.$value->asin.'" >'.$value->asin.'</a>';
            else if($type=='product_name')
                $data[] = '<a target="_blank" href="https://www.amazon.'.$url_ext.'/dp/'.$value->asin.'" >'.$value->product_name.'</a>';
            $data[] = '<p align="justify">'.$value->review_comment.'</p>';
            $rating = "";
            for ($i=0; $i < $value->reviewer_rating ; $i++)  $rating.="<i class='".$value->reviewer_rating." fa fa-star fa-1'></i>";
            $data[] = $rating;
            $data[] = $value->reviewer_name;
            $data[] = '<a target="_blank" href="https://sellercentral.amazon.'.$url_ext.'/hz/orders/details?_encoding=UTF8&orderId='.$value->order_number.'" >'.$value->order_number.'</a>';
            $archive="";
            $unactioned = "";
            $day1 = "";
            $day2 = "";
            $day3 = "";
            $custom = "";
            $ishidden = "hidden";
            $current = Carbon::now();
            $current = new Carbon();
            $today = Carbon::today();
            $action_date = "";
            if($action == 'archive') $archive = "selected=selected";
            else if($action == "inbox") $unactioned = "selected=selected";
            else if($action == "later"){
                if($value->action_date == $today->addDay()) $day1 = "selected=selected";
                else if($value->action_date == $today->addDay()) $day2 = "selected=selected";
                else if($value->action_date == $today->addDay()) $day3 = "selected=selected";
                else{ 
                    $custom = "selected=selected"; 
                    $ishidden="text"; 
                    $action_date = date('m/d/Y',strtotime($value->action_date)); 
                }
            }

            if($action == 'archive'){
                $move_to = null;
                $tooltip_title= 'Move to inbox';
                $icon = 'inbox';
            }else{
                $move_to = 'archive';
                $tooltip_title= 'Move to archive';
                $icon = 'file-archive-o';
            }
            $today = Carbon::today();
            // $action_button = '<small><select id="date-action-'.$value->id.'" onchange="change_review_action('.$value->id.');">
            //         <option value="unactioned" '.$unactioned.'>Unactioned</option>
            //         <option value="'.$today->addDay().'" '.$day1.'>1 day</option>
            //         <option value="'.$today->addDay().'" '.$day2.'>2 Days</option>
            //         <option value="'.$today->addDay().'" '.$day3.'>3 Days</option>
            //         <option value="custom"  '.$custom.'>Custom</option>
            //         <option value="archive" '.$archive.'>Archive</option>
            //     </select><br>
            //     <input type="'.$ishidden.'" class="custom-date-action" id="custom-date-action-'.$value->id.'" size="10" value="'.$action_date.'" readonly/></small>';

            $action_button = '<h3 class="text-center" data-toggle="tooltip" title="'.$tooltip_title.'" style="cursor:pointer" onclick="change_review_action('.$value->id.',\''.$move_to.'\');"><i class="fa fa-'.$icon.' color-orange" ></i><h3>';

            $data[] = $action_button;
            $review_comments = $this->getReviewComments($value->id);
            $comment="";
            if(count($review_comments)>0) $comment = "<p>".$review_comments[count($review_comments)-1]['comment']."</p>";
            $comment.= "<p class='text-center'><button class='popup btn btn-primary btn-sm addComment' data-id=".$value->id." ><i class='fa fa-plus'></i> Add</button></p> ";
            if(count($review_comments)>1){
                $comment.= " <p class='text-center'><button class='popup btn btn-primary btn-sm' onclick='viewAllComments(".$value->id.");'><i class='fa fa-search'></i> View All";
                $comment_body="";
                foreach ($review_comments as $c) {
                    $comment_body .= "<p><small>".$c['comment']."</br>
                    <b>Date Created: </b>".date('Y-m-d', strtotime($c['date_created']))."</small></p>";
                }
                $comment.= "<span class='popuptext' id='myPopup".$value->id."'>".$comment_body."</span></button></p class='text-center'>";
            }
            $data[] = $comment;
            $datas[] = $data;
        }
        echo json_encode($datas);
    }

    public function update_reviews_action(Request $request){
        $seller_id = Auth::user()->seller_id;
        $action_date = "";
        $id = "";
        $custom_date = "";
        if(isset($request->action_date)) $action_date = $request->action_date;
        if(isset($request->id)) $id = $request->id;
        if(isset($request->custom_date)) $custom_date = $request->custom_date;
        if($action_date == "archive"){
            $action_date = "1970-01-01 00:00:00";
        }else if($action_date == 'custom'){
            $action_date = $custom_date;
        }else if($action_date == 'unactioned'){
            $action_date = null;
        }
        $q = new SellerReview;
        $q->setConnection('mysql2');
        $reviews = $q->find($id);
        $reviews->action_date = $action_date;
        $reviews->save();
        echo "success";
    }

    public function getReviewFilters(Request $request){
        $seller_id = Auth::user()->seller_id;
        $id="";
        $filters="";
        $q = new SellerReviewFilter();
        if(isset($request->id)) $id=$request->id;
        if($id!='' AND $id!=" " AND $id!=null AND $id!=0){
            $filters = $q->getRecords(array('*'), array('seller_id'=> $seller_id, 'id'=>$id));
        }else{
            $filters = $q->getRecords(array('*'), array('seller_id'=> $seller_id));
        }
        echo json_encode($filters);
    }

    public function addReviewFilter(Request $request){
        $seller_id = Auth::user()->seller_id;
        $filter = new SellerReviewFilter();
        $filter->setConnection('mysql2');
        $filter->filter_name = $request->title;
        $filter->country_filter = rtrim($request->countries,'-');
        $filter->column_to_filter = $request->display_type;
        $filter->text_filter = $request->text_filter;
        $filter->rating_to_filter = $request->rating_to;
        $filter->rating_from_filter = $request->rating_from;
        $filter->date_range_filter = $request->date_range;
        $filter->date_from_filter = $request->date_from;
        $filter->date_to_filter = $request->date_to;
        $filter->date_created = Carbon::now();
        $filter->seller_id = $seller_id;
        $filter->save();
    }

    public function getReviewComments($id=0){
        $q = new SellerReview;
        $q->setConnection('mysql2');
        $comments =  $q->find($id)->comments;
        $ret = array();
        foreach ($comments as $key => $value) {
            $data = array();
            $data['id'] = $value->id;
            $data['user_id'] = $value->user_id;
            $data['seller_review_id'] = $value->seller_review_id;
            $data['comment'] = $value->comment;
            $data['date_created'] = $value->date_created;
            $data['isEdited'] = $value->isEdited;
            $ret[] = $data;
        }
        return $ret;
    }

    public function addReviewComment(Request $request){
        $seller_id = Auth::user()->seller_id;
        $comment = new SellerReviewComment;
        $comment->setConnection('mysql2');
        $comment->user_id = Auth::id();
        $comment->seller_review_id = $request->review_id;
        $comment->comment = $request->comment;
        $comment->date_created = Carbon::now();
        $comment->seller_id = $seller_id;
        $comment->save();
        $comment_body = "<p><small>".$request->comment."</br>
                    <b>Date Created: </b>".date('Y-m-d', strtotime(Carbon::now()))."</small></p>";
        echo $comment_body;
    }

    /**
     *
     * Gets the bs_name from base_subscription_sellers table
     * and adds a checker for the radio buttons of the view
     *
     * @param    integer    $seller_id
     * @return   object     $data
     *
     */
    private function callBaseSubscriptionName($seller_id) {
      $data = (object) null;

      $data->base_subscription = '';
      $is_trial = Auth::user()->seller->is_trial;

      if ($is_trial == 1) {
        $data->base_subscription = 'XL';
      } else {
        $bss = BaseSubscriptionSeller::where('seller_id', '=', $seller_id)->first();
        if (isset($bss)) {
            $bsst = BaseSubscriptionSellerTransaction::where('bss_id', '=', $bss->id)
                                                        ->where('currently_used', '=', true)
                                                        ->first();
            $data->base_subscription = $bsst->bs_name;
        }
      }

      return $data;
    }
}
