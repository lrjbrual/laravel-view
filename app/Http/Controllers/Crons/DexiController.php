<?php

namespace App\Http\Controllers\Crons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dexi\Dexi;
use Auth;
use App\ProductReviewsRun;
use App\ProductReviewsRobot;
use App\ProductReviewsUrl;
use App\ProductReviewsProduct;
use App\ProductReviewsReviews;
use App\BaseSubscriptionSellerTransaction;
use App\BaseSubscriptionSeller;
use Carbon\Carbon;
use App\Http\Helpers\HelpersFacade;
use App\ProductReviewsSeller;
use App\ProductReviewsTransaction;
use Mail;
use App\Mail\CronNotification;
use DB;
use App\Seller;
use \Config;
use App\Mail\ReviewsEmail;

class DexiController extends Controller
{
    //
    private $helper;

    public function __construct()
    {
        $this->helper = new HelpersFacade;
    }

    public function index()
    {
    	try {
    	$start_run_time = time();
		$isError = false;
		$total_records = 0;
		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Dexi Scraper Controller', true));
	    $dexi = new Dexi;
        $dexi::init(env('DEXIIO_API_KEY'), env('DEXIIO_ACCOUNT_ID'));

        $all = Seller::all();

        if(isset($all))
        {
        	foreach($all as $a)
        	{
        		$check = ProductReviewsSeller::where('seller_id', $a->id)
        										 ->first();

        		if($a->is_trial == 1) //if is in trial, create a PRS for seller if not existing yet
        		{
        			if(!isset($check))
        			{
        				$createPRS = new ProductReviewsSeller;
        				$createPRS->bst_id = null;
        				$createPRS->seller_id = $a->id;
        				$createPRS->schedule = Carbon::now();
        				$createPRS->save();
        			}
				}
				else //if not in trial, check if bst_id = null ('null' means the PRS was made in trial)
				{
					if(isset($check))
					{
						if(is_null($check->bst_id)) //delete if nulll because null is for free trial
						{
							ProductReviewsSeller::destroy($check->id);
						}
						else //change the bst into the one currently used
		                {
		                	$bst = new BaseSubscriptionSeller;
		                	$bst = $bst->getBSSandBST($a->id);
		                	if(isset($bst))
		                	{
			                    $check->bst_id = $bst->id;
			                    $check->save();
			                }
		                }
					}
				}
        	}
        }

        $q = new ProductReviewsSeller;
        $getSellers = $q->getProductReviewSeller();
        
        if(isset($getSellers))
        {
	        foreach($getSellers as $seller)
	    	{
	    		$seller_id = $seller->seller_id;

	    	//check schedule

	    		$schedule = $seller->schedule;

	    		$now = Carbon::now();
	    		if($schedule <= $now)
	    		{
	    			//check mkp first before creating run
	    			 $mkp_assigns = DB::table('marketplace_assigns')
					 				->select('marketplace_id','mws_seller_id')
					 				->where('seller_id', $seller_id)
					 				->get();

					 if(count($mkp_assigns) <= 0)
					 {
					 	echo 'this seller'.$seller_id.' has no mkp assigns';
					 	continue;
					 }

					 $mkp_array = array();
					 foreach($mkp_assigns as $mkp)
					 {
					 		$mkp_array[$mkp->marketplace_id] = $mkp->mws_seller_id;
					 }


		    		//creation of run if it does not exist
				   	 $run = ProductReviewsRun::where('seller_id',$seller_id)
				   	 						   ->where('robot_id', 1)
				   	 						   ->first();

				   	 if(!isset($run))
				   	 {
					   	 $robotId = ProductReviewsRobot::find(1)->robotCode;
						 $create = $dexi::runs()->create($robotId, $seller_id);
						 $new = new ProductReviewsRun;
						 $new->seller_id = $seller_id;
						 $new->robot_id = 1;
						 $new->runCode = $create->_id;
						 $new->save();

						 $run = DB::table('product_reviews_runs')
					 				->select('runCode','id')
					 				->where('robot_id', 1)
					 				->where('seller_id', $seller_id)
					 				->first();

					 	$runId = $run->runCode;
					 }
					 else
					 {
					 	$runId = $run->runCode;
					 }
					
				
					

					 $url = array();
					 if(isset($mkp_array[1]))
					 {
					 	$url[] = ['URL' => 'https://www.amazon.com/s/ref=sr_il_ti_merchant-items?me='.$mkp_array[1].'&rh=i%3Amerchant-items&ie=UTF8&lo=merchant-items'];
					 	$url[] = ['URL' => 'https://www.amazon.ca/s/ref=sr_il_ti_merchant-items?me='.$mkp_array[1].'&rh=i%3Amerchant-items&ie=UTF8&lo=merchant-items'];
					 }
					 if(isset($mkp_array[2]))
					 {
					 	$url[] = ['URL' => 'https://www.amazon.co.uk/s/ref=sr_il_ti_merchant-items?me='.$mkp_array[2].'&rh=i%3Amerchant-items&ie=UTF8&lo=merchant-items'];
					 	$url[] = ['URL' => 'https://www.amazon.de/s/ref=sr_il_ti_merchant-items?me='.$mkp_array[2].'&rh=i%3Amerchant-items&ie=UTF8&lo=merchant-items'];
					 	$url[] = ['URL' => 'https://www.amazon.fr/s/ref=sr_il_ti_merchant-items?me='.$mkp_array[2].'&rh=i%3Amerchant-items&ie=UTF8&lo=merchant-items'];
					 	$url[] = ['URL' => 'https://www.amazon.es/s/ref=sr_il_ti_merchant-items?me='.$mkp_array[2].'&rh=i%3Amerchant-items&ie=UTF8&lo=merchant-items'];
					 	$url[] = ['URL' => 'https://www.amazon.it/s/ref=sr_il_ti_merchant-items?me='.$mkp_array[2].'&rh=i%3Amerchant-items&ie=UTF8&lo=merchant-items'];
					 }
				//

				//save all url into table with matching runId if not exist
					 foreach($url as $u)
					 {
					 	foreach($u as $v => $value)
					 	{
					 		$url_exist = ProductReviewsUrl::where('url',$value)
					 									  ->first();

					 		if(!isset($url_exist))
					 		{
					 			$save = new ProductReviewsUrl;
					 			$save->url = $value;
					 			$save->run_id = $run->id;
					 			$save->save();
					 		}
					 	}
					 }
				//

				//get url primary keys accding to name
					 $getUrl = ProductReviewsUrl::where('run_id', $run->id)
					 							->get();

					 $urlArray = array();
					 $urlCountry = array();
					 foreach($getUrl as $g)
					 {
					 		$urlArray[$g->url] = $g->id;

					 		$string = $g->url;
							$string = strstr($g->url,'/s', true);

							switch($string)
							{
								case "https://www.amazon.co.uk":
									$urlCountry[$g->url] = 'uk';
									break;
								case "https://www.amazon.ca":
									$urlCountry[$g->url] = 'ca';
									break;
								case "https://www.amazon.de":
									$urlCountry[$g->url] = 'de';
									break;
								case "https://www.amazon.it":
									$urlCountry[$g->url] = 'it';
									break;
								case "https://www.amazon.fr":
									$urlCountry[$g->url] = 'fr';
									break;
								case "https://www.amazon.es":
									$urlCountry[$g->url] = 'es';
									break;
								case "https://www.amazon.it":
									$urlCountry[$g->url] = 'it';
									break;
								case "https://www.amazon.com":
								$urlCountry[$g->url] = 'us';
								break;
							}
					 }
				//


			//Process from setting input to getting the results

				    $setInput = $dexi::runs()->setInputs($runId,$url,false);

				    $execute = $dexi::runs()->execute($runId);

				     $count = 0;
				     $stat = $dexi::executions()->getStats($execute->_id);
				     $check = $stat->state;
				   	 while($check == "RUNNING" || $check == "QUEUED" || $check == "PENDING")
				   	 {
				   	 	sleep(60);
				   	 	echo $count;
				   	 	$stat = $dexi::executions()->getStats($execute->_id);
				   	 	if(isset($stat->state))
				   	 	{
				   	 		$check = $stat->state;
				   	 	}
				   	 	else
				   	 	{
				   	 		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Dexi Scraper Controller getStat() error line 192', true));
				   	 		break;
				   	 	}

				   	 	$count++;
				   	 	if($check == "FAILED")
				   	 	{
				   	 		echo '<br>FAILED';
				   	 		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Dexi Scraper Controller getStat() error line 200 "FAILED"', true));
				   	 		break;
				   	 	}
				   	 }

				   	$get = $dexi::runs()->getLatestResult($runId);

					//get result and save to database
				   		foreach($get->rows as $g)
				   		{
				   			$string = $g[3];

				   			//trimming string into 2 decimal places
				   			$string = strstr($string, ' ', true);
						   	if(strlen($string) > 1)
						   	{
						   		$string = str_replace(',','.',$string);
						   	}

						   	$string = (double)$string;
					//

						//trim ASIN
						   	$productASIN = $g[1];

						   	if(is_null($productASIN) || empty($productASIN))
						   	{
						   		echo 'data is null';
						   	}
						   	else
						   	{
							   	$productASIN = strstr($productASIN, '/ref', true);
							   	$productASIN = strstr($productASIN, '/dp/');
							   	$productASIN = trim($productASIN, '/dp/');
							//

							//check if the Product Exists


							   	$find = ProductReviewsProduct::where('url_id' , $urlArray[$g[0]])
							   								 ->where('product_asin',$productASIN)
							   								 ->where('title', $g[2])
							   								 ->orderByDesc('date_of_change')
							   								 ->first();
							//
							   	if(!isset($find))
							   	{
							   		echo '<br>'.$g[0].'not found';
							   		$new = new ProductReviewsProduct;
							   		$new->seller_id = $seller_id;
							   		$new->url_id = $urlArray[$g[0]];
							   		$new->country = $urlCountry[$g[0]];
							   		$new->product_asin = $productASIN;
							   		$new->title = $g[2];

							   		if(is_null($g[4]) || $g[4] == 0)
							   		{
							   			$new->star_rating = 0;
							   		}
							   		else
							   		{
							   			$new->star_rating = $string;
							   		}

							   		$new->nb_of_reviews = $g[4];
							   		$new->status = 'origin';
							   		$new->save();
							   	}
							   	else
							   	{
							   		$checker = 0;
							   		echo '<br>'.$g[0].'found, now comparing';
							   		$starRating = (double)$find->star_rating;
							   		$nbReviews = (double)$find->nb_of_reviews;

							   		if($starRating != $string)
							   		{
							   			echo '<br>'.$find->star_rating.' star rating != string ='.$string;
							   			$checker++;
							   		}
							   		if($nbReviews != ((double)$g[4]))
							   		{
							   			echo '<br>'.$find->nb_of_reviews.' = nb of reviews not equal to g[4] ='.$g[4];
							   			$checker++;
							   		}

							   		echo '<br> checker ='.$checker;

							   		if($checker > 0)
							   		{
							   			$new = new ProductReviewsProduct;
							   			$new->seller_id = $seller_id;
								   		$new->url_id = $urlArray[$g[0]];
								   		$new->country = $urlCountry[$g[0]];
								   		$new->product_asin = $productASIN;
								   		$new->title = $g[2];
								   		if(is_null($g[4]) || $g[4] == 0)
								   		{
								   			$new->star_rating = 0;
								   		}
								   		else
								   		{
								   			$new->star_rating = $string;
								   		}
								   		$new->nb_of_reviews = $g[4];
								   		$new->date_of_change = Carbon::now();
								   		$new->changed = $find->id;
								   		$new->status = 'duplicate';
								   		$new->save();
							   		}

							   	}
							}
					   	}

				   	//Change schedule depends on the base subscription
					   	$seller = Seller::where('id', $seller_id)
					   						->first();

					   	$scheduleCheck = '';
					   	if($seller->is_trial == 1)
					   	{
					   		$scheduleCheck = 'L';
					   	}
					   	else
					   	{
					   		$bst = new BaseSubscriptionSeller;
					   		$bst = $bst->getBSSandBST($seller_id);

					   		if(isset($bst))
					   		$scheduleCheck = $bst->bs_name;
					   	}
 						
 						$update = $this->updateProductReviewDetails($seller_id);

					   	$prs = ProductReviewsSeller::where('seller_id',$seller->id)->first();
					   	if(isset($prs))
						{
						   	echo '<br>'.$seller_id;
					   		if($scheduleCheck == 'XS')
						   	{
						   		echo '<br>XS';
						   		$next_sched = Carbon::parse($prs->schedule)->addDays(10);
						   	}
						   	if($scheduleCheck == 'S')
					   		{
					   			echo '<br>S';
					   			$next_sched = Carbon::parse($prs->schedule)->addDays(5);
					   		}
					   		elseif($scheduleCheck == 'M')
					   		{
					   			echo '<br>M';
					   			$next_sched = Carbon::parse($prs->schedule)->addDays(2);
					   		}
						   	elseif($scheduleCheck == 'L')
						   	{
						   		echo '<br>L';
						   		$next_sched = Carbon::parse($prs->schedule)->addDays(1);
							}

						   		$prs->schedule = $next_sched;
						   		$prs->save();
						}

						if(count($update) > 0)
						{
							$this->send_email_notification($seller->firstname,$update);
						}
					//
		    		}
		    		else
		    		{
		    			echo '<br>not scheduled today';
		    			continue;
		    		}
	   			}
	   		}

	   	$end_run_time = time();
    	$message['message'] = "Dexi Scraper Cron Script Run Successfully!";
		$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
		$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
		$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
		$message['tries'] = 1;
		$message['total_records'] = $total_records;
		$message['isError'] = $isError;
		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Dexi Scraper Controller', false, $message));
		} catch (\Exception $e) {
			dd($e);
			$end_run_time = time();
			$message['time_start'] = date('Y-m-d H:i:s', $start_run_time);
			$message['time_end'] = date('Y-m-d H:i:s', $end_run_time);
			$message['total_time_of_execution'] = ($end_run_time - $start_run_time)/60;
			$message['tries'] = 1;
			$message['total_records'] = (isset($total_records) ? $total_records : 0);
			$message['isError'] = $isError;
			$message['message'] = "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
			Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Dexi Scraper Controller(error)', false, $message));
		}
	}

	public function updateProductReviewDetails($seller_id = null)
	{
		$waypoint = 0;
		$emailArray = array();
		$dexi = new Dexi;
        $dexi::init(env('DEXIIO_API_KEY'), env('DEXIIO_ACCOUNT_ID'));
		$pr = ProductReviewsRun::where('robot_id', 2)
						 ->where('seller_id' , $seller_id)
						 ->first();

		if(!isset($pr))
		{
			 $robotId = ProductReviewsRobot::find(2)->robotCode;
			 $create = $dexi::runs()->create($robotId, $seller_id);
			 $new = new ProductReviewsRun;
			 $new->seller_id = $seller_id;
			 $new->robot_id = 2;
			 $new->runCode = $create->_id;
			 $new->save();

		 	$waypoint = 1;					//first time use track, uses All Pages Run
		}
		else
		{
			$waypoint = 2;					//First Page only track
		}

	//All Pages Run Track
		if($waypoint == 1)
		{
			$run = DB::table('product_reviews_runs')
		 			->select('runCode','id')
		 			->where('robot_id', 2)
		 			->where('seller_id', $seller_id)
		 			->first();

		 	$url = DB::table('product_reviews_runs')
		 			->select('id')
		 			->where('robot_id', 1)
		 			->where('seller_id', $seller_id)
		 			->first();

		 	$runCode = $run->runCode;
		 	$runId = $run->id;

		 	$runUrl_id = $url->id;

		//Get all urls used by the seller
		 	$allURL = DB::table('product_reviews_urls')
		 				->select('id','url')
		 				->where('run_id',$runUrl_id)
		 				->get();

		 	$urlArray = array();
		 	$urlIdArray = array();
		 	foreach($allURL as $a)
		 	{
		 		$trimURL = strstr($a->url, '/s/', true);
		 		$urlArray[$a->id] = $trimURL;
		 		$urlIdArray[$trimURL] = $a->id;
		 	}
		//

		//Get all product of the seller by url id
		 	$allPR = DB::connection('mysql2')->table('product_reviews_products')
		 				->select('product_asin','id','url_id')
		 				->where('seller_id', $seller_id)
		 				->whereNotNull('nb_of_reviews')
		 				->where('status','origin')
		 				->get();

		 	$productArray = array();
		 	$productIdArray = array();
		    foreach($allPR as $a)
		    {
		    	$productArray[$a->id] = ['asin' => $a->product_asin,
		    							 'url_id' => $a->url_id];
		    	$productIdArray[$a->url_id.'_'.$a->product_asin] = $a->id;
		    }
		//

		    $url = array();
		//Combine URL and Products for input
		  if(count($productArray) > 0)
			{
			    foreach($productArray as $pa)
			    {
			    	$url[] = ['URLs' => $urlArray[$pa['url_id']].'/product-reviews/'.$pa['asin'].'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1'];
			    }
			}
			else
			{
				echo '<br>no product';
				return false;
			}
		//
		    $setInput = $dexi::runs()->setInputs($runCode,$url,false);

		    $execute = $dexi::runs()->execute($runCode);

		    $count = 0;
		    $stat = $dexi::executions()->getStats($execute->_id);
		    $check = $stat->state;
		   	while($check == "RUNNING" || $check == "QUEUED" || $check == "PENDING")
		   	{
			   	sleep(60);
			   	echo $count;
			   	$stat = $dexi::executions()->getStats($execute->_id);

			   	if(isset($stat->state))
			   	{
			   		$check = $stat->state;
			   	}
			   	else
			   	{
			   		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Dexi Controller Stat getStats() problem line 444', true));
			   		break;
			    }
			   		$count++;
		   	}

	   		$get = $dexi::runs()->getLatestResult($runCode);

	   	//Saving results
	   		$check_exist = array();
	   		$getReviews = ProductReviewsReviews::where('seller_id', $seller_id)
	   					  					   ->get();

	   		if(isset($getReviews))
	   		{  					   
		   		foreach($getReviews as $r)
		   		{
		   			$check_exist[] = $r->review_code;
		   		}	  					
		   	}
	   		
	   		foreach($get->rows as $g)
	   		{

	   				//trim url
			   		$url = $g[0];
					$url = strstr($url,'/product',true);

					//trim product asin
					$asin = strstr($g[0],'/ref',true);
					$asin = strstr($asin,'/product-reviews/');
					$asin = trim($asin,'/product-reviews/');

					//trim review id/code
					$review_code = strstr($g[1],'/ref',true);
					$review_code = trim($review_code,'/gp/customer-reviews/');

					$urlId = $urlIdArray[$url];
	   				//search productArray for key using url and asin
					$product_id = $productIdArray[$urlId.'_'.$asin];

					if(is_null($review_code) || empty($review_code))
					{
						echo 'data is null';
					}
					else
					{
						//trimming string into 2 decimal places
			   			$star = strstr($g[2], ' ', true);

					   	if(strlen($star) > 1)
					   	{
					   		$star = str_replace(',','.',$star);
					   	}

					   	$star = (double)$star;

					   	$review_title = $g[3];
					   	$author = $g[4];
					   	$author_url = $g[5];

					   	if(is_null($g[6]) || empty($g[6]))
						{
							$time = null;
						}
						else
						{
							$time = $this->helper->dateMonthTranslatorForDexi($g[6],$url);
						}

					   	$variation = $g[7];
					   	$verified_purchase = $g[8];
					   	$review_text = $g[9];
		   				
		   				if(in_array($review_code,$check_exist))
		   				{
		   					echo 'review code already exists';
		   				}
		   				else
		   				{
			   				//saving data
			   				$save = new ProductReviewsReviews;
			   				$save->product_id = $product_id;
			   				$save->review_code = $review_code;
			   				$save->review_date = $time;
			   				$save->review_title = $review_title;
			   				$save->review_text = $review_text;
			   				$save->variation = $variation;
			   				$save->verified_purchase = $verified_purchase;
			   				$save->star = $star;
			   				$save->author = $author;
			   				$save->author_url = $author_url;
			   				$save->seller_id = $seller_id;
			   				$save->save();

			   				$check_exist[] = $review_code;
		   				}
		   			}

	   				//update product status to "scraped"
	   				$product = ProductReviewsProduct::find($product_id);
	   				$product->status = "scraped";
	   				$product->save();
	   		}
	   	//

		}
	//

	//First Page only Track
		if($waypoint == 2)
		{
			$pr = ProductReviewsRun::where('robot_id', 3)
						 ->where('seller_id' , $seller_id)
						 ->first();

			if(!isset($pr))
			{
				$robotId = ProductReviewsRobot::find(3)->robotCode;
				$create = $dexi::runs()->create($robotId, $seller_id);
				$new = new ProductReviewsRun;
				$new->seller_id = $seller_id;
				$new->robot_id = 3;
				$new->runCode = $create->_id;
				$new->save();
			}

			$run = DB::table('product_reviews_runs')
		 			->select('runCode','id')
		 			->where('robot_id', 3)
		 			->where('seller_id', $seller_id)
		 			->first();

		 	$url = DB::table('product_reviews_runs')
		 			->select('id')
		 			->where('robot_id', 1)
		 			->where('seller_id', $seller_id)
		 			->first();


		 	$runCode = $run->runCode;
		 	$runId = $run->id;

		 	$runUrl_id = $url->id;

		//Get all urls used by the seller
		 	$allURL = DB::table('product_reviews_urls')
		 				->select('id','url')
		 				->where('run_id',$runUrl_id)
		 				->get();

		 	$urlArray = array();
		 	$urlIdArray = array();
		 	foreach($allURL as $a)
		 	{
		 		$trimURL = strstr($a->url, '/s/', true);
		 		$urlArray[$a->id] = $trimURL;
		 		$urlIdArray[$trimURL] = $a->id;
		 	}
		//

		//Get all product of the seller by url id

		 	$allPR = DB::connection('mysql2')->table('product_reviews_products')
					 	->select('product_asin','id','url_id','changed','status')
					 	->where('seller_id', $seller_id)
					 	->whereNotNull('nb_of_reviews')
			            ->whereIn('status', ['duplicate', 'origin'])
			        	->get();

        	$productArray = array();
		 	$productIdArray = array();
		    foreach($allPR as $a)
		    {
		    	if($a->status == 'origin')
		    	{
			    	$productArray[$a->id] = ['asin' => $a->product_asin,
			    							 'url_id' => $a->url_id];
			    	$productIdArray[$a->url_id.'_'.$a->product_asin] = ['orig_id' => $a->id,
			    														'dup_id' => $a->id];
			    }
			    elseif($a->status == 'duplicate')
			    {
			    	$productArray[$a->changed] = ['asin' => $a->product_asin,
			    							 'url_id' => $a->url_id];
			    	$productIdArray[$a->url_id.'_'.$a->product_asin] = ['orig_id' => $a->changed,
			    														'dup_id' => $a->id];
			    }
		    }

		//Get all Reviews for searching - optimizing query using array as search instead of query find
			$allReviews = DB::connection('mysql2')->table('product_reviews_reviews')
							->select('review_code','id')
							->where('seller_id', $seller_id)
							->get();

			$allReviewsArray = array();
			foreach($allReviews as $rev)
			{
				$allReviewsArray[$rev->review_code] = $rev->id;
			}

		//

		$url = array();
		//Combine URL and Products for input
		if(count($productArray) > 0)
		{
		    foreach($productArray as $pa)
		    {
		    	$url[] = ['URLs' => $urlArray[$pa['url_id']].'/product-reviews/'.$pa['asin'].'/ref=cm_cr_arp_d_viewopt_srt?ie=UTF8&reviewerType=all_reviews&pageSize=100&sortBy=recent&pageNumber=1'];
		    }
		}
		else
		{
			echo '<br>no product';
			return false;
		}
		//
		    $setInput = $dexi::runs()->setInputs($runCode,$url,false);

		    $execute = $dexi::runs()->execute($runCode);

		    $count = 0;
		    $stat = $dexi::executions()->getStats($execute->_id);
		    $check = $stat->state;
		   	while($check == "RUNNING" || $check == "QUEUED" || $check == "PENDING")
		   	{
			   	sleep(60);
			   	echo $count;
			   	$stat = $dexi::executions()->getStats($execute->_id);

			   	if(isset($stat->state))
			   	{
			   		$check = $stat->state;
			   	}
			   	else
			   	{
			   		Mail::to(env('MAIL_CRON_EMAIL','crons@trendle.io'))->send(new CronNotification('Dexi Scraper Controller getStat() problem', true));
			   		break;
			    }
			   		$count++;
		   	}

	   		$get = $dexi::runs()->getLatestResult($runCode);

	   	//Saving results
	   		foreach($get->rows as $g)
	   		{

   				//trim url
		   		$url = $g[0];
				$url = strstr($url,'/product',true);

				//trim product asin
				$asin = strstr($g[0],'/ref',true);
				$asin = strstr($asin,'/product-reviews/');
				$asin = trim($asin,'/product-reviews/');

				//trim review id/code
				$review_code = strstr($g[1],'/ref',true);
				$review_code = trim($review_code,'/gp/customer-reviews/');

				$urlId = $urlIdArray[$url];
   				//search productArray for key using url and asin
				$product_id = $productIdArray[$urlId.'_'.$asin];

   				//update product status to "scraped"

   				// $productScraped = array();
   				// $productScraped[$product_id['dup_id']] = $product_id['dup_id'];

				//check if review code is null, if it is then skip saving
				if(is_null($review_code) || empty($review_code))
				{
					echo 'data is null';
				}
				else
				{
					//trimming string into 2 decimal places
		   			$star = strstr($g[2], ' ', true);

				   	if(strlen($star) > 1)
				   	{
				   		$star = str_replace(',','.',$star);
				   	}

				   	$star = (double)$star;

				   	$review_title = $g[3];
				   	$author = $g[4];
				   	$author_url = $g[5];

				   	if(is_null($g[6]) || empty($g[6]))
					{
						$time = null;
					}
					else
					{
						$time = $this->helper->dateMonthTranslatorForDexi($g[6],$url);
					}

				   	$variation = $g[7];
				   	$verified_purchase = $g[8];
				   	$review_text = $g[9];

	   				//Check if Review code already exists
					if(isset($allReviewsArray[$review_code]))
					{
						echo '<br>duplicate found';
					}
					else
					{
		   				//saving data
		   				$save = new ProductReviewsReviews;
		   				$save->product_id = $product_id['orig_id'];
		   				$save->review_code = $review_code;
		   				$save->review_date = $time;
		   				$save->review_title = $review_title;
		   				$save->review_text = $review_text;
		   				$save->variation = $variation;
		   				$save->verified_purchase = $verified_purchase;
		   				$save->star = $star;
		   				$save->author = $author;
		   				$save->author_url = $author_url;
		   				$save->seller_id = $seller_id;
		   				$save->save();

		   				
		   				$getProduct = new ProductReviewsProduct;
		   				$getProduct = $getProduct->getProductWithSKU($product_id['orig_id'],$seller_id);
		   				$save->review_date = Carbon::parse($save->review_date)->format('d/m/Y');
		   				$save->country = $getProduct->country;
		   				if(is_null($getProduct->sku))
		   				{
		   					$save->sku = $getProduct->product_asin;
		   				}
		   				else
		   				{
		   					$save->sku = $getProduct->sku;
		   				}
		   				$emailArray[] = $save;
		   			}
	   			}
	   			//update product status to "scraped"
	   				$product = ProductReviewsProduct::find($product_id['dup_id']);
	   				$product->status = "scraped";
	   				$product->save();
	   		}
		}

	return $emailArray;
	}

	public function send_email_notification($first,$array)
	{
        //backup default config
        $backup = Config::get('mail');
        //set new config for sparkpost
	    if(env('SPARKPOST_MAIL_DRIVER') != ""){
	        Config::set('mail',config('constant.SPARK_POST_CONSTANTS'));
      }

      $email = 'alchiebinan21@gmail.com';
      Mail::to($email)->send(new ReviewsEmail($first, $array,'product'));

      //restore default config
      Config::set('mail', $backup);
	}
}
