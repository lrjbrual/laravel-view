<?php

namespace App\Http\Controllers\Trendle;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use App\BaseSubscriptionSellerTransaction;
use App\BaseSubscriptionSeller;
use Carbon\Carbon;
use App\ProductReviewsProduct;
use App\ProductReviewsReviews;
use App\ProductReviewsSeller;
use App\ProductReviewsComment;
use DB;

class ProductReviewController extends Controller
{   

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('checkStripe');
    }
    
    public function index(){
    	$seller_id = Auth::user()->seller_id;

    	$data = $this->callBaseSubscriptionName($seller_id);
        if ($data->base_subscription == '' && Auth::user()->seller->is_trial == 0) {
            return redirect('subscription');
        }

        $update = ProductReviewsSeller::where('seller_id',$seller_id)->first();
   
        if(isset($update))
        {
            $lastUpdate = Carbon::createFromFormat('Y-m-d H:i:s', $update->updated_at)->format('d/m/Y');
        }
        else
        {
            $lastUpdate = 'n/a';
        }

    	return view('trendle.productreview.index')
    			->with('bs',$data->base_subscription)
                ->with('lastUpdate', $lastUpdate);
    }

    public function getData(Request $req){

        $draw = $req->draw;
        $filters['skip'] = $req->start;
        $filters['take'] = $req->length;
        $filters['sort_column'] = $req->order[0]['column'];
        $filters['sort_direction'] = $req->order[0]['dir'];
        $response = array();

        switch ($req->tab) {
            case 'inbox':
                $move_to = 'archive';
                $tooltip_title= 'Move to archive';
                $icon = 'file-archive-o';
                break;

            case 'archive':
                $move_to = 'inbox';
                $tooltip_title= 'Move to inbox';
                $icon = 'inbox';

                break;

            case 'products':

                break;
        }

        $seller_id = Auth::user()->seller_id;
        
        if ($req->tab == "inbox") 
        {
            $q = new ProductReviewsReviews;
            $review = $q->getProductReviews($seller_id,$filters);
            $total = $q->getProductReviews($seller_id,$filters,true);

            $data2 = array();
            if(isset($review))
            {
                foreach($review as $r)
                {
                    if($r->country == 'us')
                    {
                        $country = 'com';
                    }
                    elseif($r->country == 'uk')
                    {
                        $country = 'co.uk';
                    }
                    else
                    {
                        $country = $r->country;
                    }

                    $star = $r->star;
                    $star_max = 5;
                    $unstar = $star_max - $star;
                    $star_rating = '';

                    for ($x=1; $x <= $star; $x++) { 
                        $star_rating .= '<i class="fa fa-star color-orange" style="margin-right:5px"></i>';
                    }

                    if ($unstar >= 1) {
                        for ($x=1; $x <= $unstar; $x++) { 
                            $star_rating .= '<i class="fa fa-star-o color-orange" style="margin-right:5px"></i>';
                        }
                    }

                    $star_rating = '<div data-toggle="tooltip" title="Ratings: '.$star.'" style="cursor:pointer">'.$star_rating.'</div>';


                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $r->review_date)->format('d/m/Y');
                    $data = array();
                    $comments_text = $r->review_text;
                    $str_count = strlen($comments_text);

                    if ($str_count >= 50) {
                        $comments = $comments_text;
                    }else{
                        $comments = $comments_text;
                    }

                    $action_button = '<h3 class="text-center" data-toggle="tooltip" title="'.$tooltip_title.'" style="cursor:pointer" onclick="change_review_action('.$r->id.',\''.$move_to.'\');"><i class="fa fa-'.$icon.' color-orange" ></i><h3>';

                    $data['rowId'] = $r->id;
                    $data['DT_RowId'] = $r->id;
                    $data[] = $date;
                    $data[] = strtoupper($r->country);
                    if($req->product_unique == "asin")
                    {
                         $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$r->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$r->product_asin.'</a>';
                    }
                    elseif($req->product_unique == "product_name")
                    {
                        $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$r->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$r->title.'</a>';
                    }
                    elseif($req->product_unique == "sku")
                    {
                        $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$r->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$r->sku.'</a>';
                    }

                    $review_comments = $this->getReviewComments($r->id);
                    $comment="";
                    if(count($review_comments)>0) $comment = "<p>".$review_comments[count($review_comments)-1]['comment']."</p>";
                    $comment.= "<p class='text-center'><button class='popup addComment btn btn-primary btn-sm' data-id=".$r->id." ><i class='fa fa-plus'></i> Add</button></p>";
                    if(count($review_comments)>1){
                        $comment.= " <p class='text-center'><button class='popup btn btn-primary btn-sm' onclick='viewAllComments(".$r->id.");'><i class='fa fa-search'></i> View All";
                        $comment_body="";
                        foreach ($review_comments as $c) {
                            $comment_body .= "<p><small>".$c['comment']."</br>
                            <b>Date Created: </b>".date('Y-m-d', strtotime($c['date_created']))."</small></p>";
                        }
                        $comment.= "<span class='popuptext' id='myPopup".$r->id."'>".$comment_body."</span></button></p>";
                    }

                    $data[] = $comments;
                    $data[] = $star_rating;
                    $data[] = $r->author;
                    $data[] = $r->review_code;
                    $data[] = $action_button;
                    $data[] = $comment;
                    $response[] = $data;
                }
            }

            $data2['draw'] = $draw;
            $data2['recordsTotal'] = $total;
            $data2['recordsFiltered'] = $total;
            $data2['data'] = $response;
        }
        elseif($req->tab == "products")
        {
            $q = new ProductReviewsProduct;
            $products = $q->getProducts($seller_id,$filters);
            $total = $q->getProducts($seller_id,$filters,true);

            if(isset($products))
            {
                foreach($products as $p)
                {
                    if($p->country == 'us')
                    {
                        $country = 'com';
                    }
                    elseif($p->country == 'uk')
                    {
                        $country = 'co.uk';
                    }
                    else
                    {
                        $country = $p->country;
                    }

                    $nbreviews = $p->nb_of_reviews;
                    if(is_null($nbreviews))
                    {
                        $nbreviews = "No Reviews";
                    }

                    $star = $p->star_rating;
                    $get_remainder = (String)$p->star_rating;
                    $has_remainder = false;
                    if (strpos($get_remainder, '.') !== false) {
                        $get_remainder = explode('.', $get_remainder);
                        $has_remainder = true;
                    }

                    $star_max = 5;
                    $unstar = $star_max - $star;
                    $star_rating = '';

                    for ($x=1; $x <= $star; $x++) { 
                        $star_rating .= '<i class="fa fa-star color-orange" style="margin-right:5px"></i>';
                    }

                    if ($has_remainder) {
                       if ($get_remainder[1] >= 5 ) {
                            $star_rating .= '<i class="fa fa-star-half-o color-orange" style="margin-right:5px"></i>';
                        }else{
                            $star_rating .= '<i class="fa fa-star-o color-orange" style="margin-right:5px"></i>';
                        }
                    }

                    if ($unstar >= 1) {
                        for ($x=1; $x <= $unstar; $x++) { 
                            $star_rating .= '<i class="fa fa-star-o color-orange" style="margin-right:5px"></i>';
                        }
                    }

                    $star_rating = '<div data-toggle="tooltip" title="Average Ratings: '.$star.'" style="cursor:pointer">'.$star_rating.'</div>';

                    $data = array();
                    $data['rowId'] = $p->id;
                    $data['DT_RowId'] = $p->id;
                    $data[] = strtoupper($p->country);
                    if($req->product_unique == "asin")
                    {
                         $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$p->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$p->product_asin.'</a>';
                    }
                    elseif($req->product_unique == "product_name")
                    {
                        $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$p->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$p->title.'</a>';
                    }
                    elseif($req->product_unique == "sku")
                    {
                        $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$p->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$p->sku.'</a>';
                    }
                    $data[] = $star_rating;
                    $data[] = $nbreviews;
                    $response[] = $data;
                }

                $data2['draw'] = $draw;
                $data2['recordsTotal'] = $total;
                $data2['recordsFiltered'] = $total;
                $data2['data'] = $response;
            }
        }
        elseif($req->tab == "archive")
        {
            $q = new ProductReviewsReviews;
            $review = $q->getProductReviewsArchived($seller_id,$filters);
            $total = $q->getProductReviewsArchived($seller_id,$filters,true);

            if(isset($review))
            {
                foreach($review as $r)
                {
                    if($r->country == 'us')
                    {
                        $country = 'com';
                    }
                    elseif($r->country == 'uk')
                    {
                        $country = 'co.uk';
                    }
                    else
                    {
                        $country = $r->country;
                    }   

                    $star = $r->star;
                    $star_max = 5;
                    $unstar = $star_max - $star;
                    $star_rating = '';

                    for ($x=1; $x <= $star; $x++) { 
                        $star_rating .= '<i class="fa fa-star color-orange" style="margin-right:5px"></i>';
                    }

                    if ($unstar >= 1) {
                        for ($x=1; $x <= $unstar; $x++) { 
                            $star_rating .= '<i class="fa fa-star-o color-orange" style="margin-right:5px"></i>';
                        }
                    }

                    $star_rating = '<div data-toggle="tooltip" title="Ratings: '.$star.'" style="cursor:pointer">'.$star_rating.'</div>';

                    $date = Carbon::createFromFormat('Y-m-d H:i:s', $r->review_date)->format('d/m/Y');
                    $comments_text = $r->review_text;
                    $data = array();
                    $str_count = strlen($comments_text);

                    if ($str_count >= 50) {
                        $comments = substr($comments_text, 0,50).' ...<br><span data-tipso-title="" data-tipso="'.$comments_text.'" style="cursor:pointer" class="color-blue comments">show more</span>';
                    }else{
                        $comments = $comments_text;
                    }

                    $action_button = '<h3 class="text-center" data-toggle="tooltip" title="'.$tooltip_title.'" style="cursor:pointer" onclick="change_review_action('.$r->id.',\''.$move_to.'\');"><i class="fa fa-'.$icon.' color-orange" ></i><h3>';

                    $data['rowId'] = $r->id;
                    $data['DT_RowId'] = $r->id;
                    $data[] = $date;
                    $data[] = $r->country;
                    if($req->product_unique == "asin")
                    {
                         $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$r->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$r->product_asin.'</a>';
                    }
                    elseif($req->product_unique == "product_name")
                    {
                        $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$r->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$r->title.'</a>';
                    }
                    elseif($req->product_unique == "sku")
                    {
                        $data[] = '<a class="color-blue" target="_blank" href="https://www.amazon.'.$country.'/product-reviews/'.$r->product_asin.'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1">'.$r->sku.'</a>';
                    }
                    $data[] = $r->review_text;
                    $data[] = $star_rating;
                    $data[] = $r->author;
                    $data[] = $r->review_code;
                    $data[] = $action_button;

                    $review_comments = $this->getReviewComments($r->id);
                    $comment="";
                    if(count($review_comments)>0) $comment = "<p>".$review_comments[count($review_comments)-1]['comment']."</p>";
                    $comment.= "<p class='text-center'><button class='popup addComment btn btn-primary btn-sm' data-id=".$r->id." ><i class='fa fa-plus'></i> Add</button></p>";
                    if(count($review_comments)>1){
                        $comment.= " <p class='text-center'><button class='popup btn btn-primary btn-sm' onclick='viewAllComments(".$r->id.");'><i class='fa fa-search'></i> View All";
                        $comment_body="";
                        foreach ($review_comments as $c) {
                            $comment_body .= "<p><small>".$c['comment']."</br>
                            <b>Date Created: </b>".date('Y-m-d', strtotime($c['date_created']))."</small></p>";
                        }
                        $comment.= "<span class='popuptext' id='myPopup".$r->id."'>".$comment_body."</span></button></p>";
                    }
                            $data[] = $comment;
                            $response[] = $data;
                }
            }
            $data2['draw'] = $draw;
            $data2['recordsTotal'] = $total;
            $data2['recordsFiltered'] = $total;
            $data2['data'] = $response;
        }

        echo json_encode($data2);
    }

    public function moveProducts(Request $req)
    {
        /*chie para ni sa e move ang product sa archive or inbox*/
        $archived = ProductReviewsReviews::where('id', $req->id)->first();

        if(isset($archived))
        {
            if($req->moveto == 'inbox')
            {
                $archived->archieved = 0;
            }
            else
            {
                $archived->archieved = 1;
            }

            $archived->save();
        }
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

    public function addReviewComment(Request $request){
        $seller_id = Auth::user()->seller_id;
        $comment = new ProductReviewsComment;
        $comment->setConnection('mysql2');
        $comment->review_id = $request->review_id;
        $comment->comment = $request->comment;
        $comment->date_created = Carbon::now();
        $comment->seller_id = $seller_id;
        $comment->save();
        $comment_body = "<p><small>".$request->comment."</br>
                    <b>Date Created: </b>".date('Y-m-d', strtotime(Carbon::now()))."</small></p>";
        echo $comment_body;
    }

    public function getReviewComments($id){
        $comments = ProductReviewsComment::where('review_id', $id)
                                           ->get();
        $ret = array();
        if(isset($comments))
        {
            
            foreach ($comments as $key => $value) {
                $data = array();
                $data['id'] = $value->id;
                $data['review_id'] = $value->review_id;
                $data['comment'] = $value->comment;
                $data['date_created'] = $value->date_created;
                $data['isEdited'] = $value->isEdited;
                $ret[] = $data;
            }
 
        }
        return $ret;
    }
}
