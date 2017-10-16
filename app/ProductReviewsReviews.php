<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ProductReviewsReviews extends Model
{
    //
    protected $connection = 'mysql2';

    public function getProductReviews($seller_id,$filters,$count = false)
    {
        $fields = array();
        $fields[] = 'prr.review_date as review_date';
        $fields[] = 'prp.country as country';
        $fields[] = 'prr.review_text as review_text';
        $fields[] = 'prr.star as star';
        $fields[] = 'prr.author as author';
        $fields[] = 'products.sku as sku';
        $fields[] = 'prp.product_asin as product_asin';
        $fields[] = 'prp.title as title';
        $fields[] = 'prr.id as id';
        $fields[] = 'prr.review_code';

        $fields2 = array();
        $fields2[] = 'review_date';
        $fields2[] = 'country';
        $fields2[] = 'review_text';
        $fields2[] = 'star';
        $fields2[] = 'author';

        $sort_column = '';
        $sort_direction = '';
        if(!$count)
        {
            if(isset($filters['sort_column']))
            {
                if($filters['sort_column'] > 1)
                { 
                    $filters['sort_column'] = $filters['sort_column']-1;
                   
                }

                $sort_column = (!isset($filters['sort_column'])) ? " " : $fields2[$filters['sort_column']];
                $sort_direction = (!isset($filters['sort_direction'])) ? " " : $filters['sort_direction'];
            }
        }

    	$review = DB::connection('mysql2')->table('product_reviews_products as prp')
                        ->leftJoin('product_reviews_reviews as prr','prp.id', '=', 
                        'prr.product_id')
                        ->leftJoin('products', function($join)
                        {
                            $join->on('products.asin', '=', 'prp.product_asin')
                            ->on('products.country', '=', 'prp.country');
                        })
                        ->whereNotNull('prr.id')
                        ->where('prp.seller_id', $seller_id)
                        ->where('prr.archieved', 0)
                        ->groupBy('prr.id')
                        ->select($fields);
                        if($count)
                        {
                            $review = $review->get()->count();
                        }
                        else
                        {
                            if(trim($sort_column) != "" AND trim($sort_direction) != "")
                            $review = $review->orderBy($sort_column, $sort_direction);
                            $review = $review->skip($filters['skip'])->take($filters['take']);
                            $review = $review->get();
                        }

        return $review;
    }

    public function getProductReviewsArchived($seller_id,$filters,$count = false)
    {
        $fields = array();
        $fields[] = 'prr.review_date as review_date';
        $fields[] = 'prp.country as country';
        $fields[] = 'prr.review_text as review_text';
        $fields[] = 'prr.star as star';
        $fields[] = 'prr.author as author';
        $fields[] = 'products.sku as sku';
        $fields[] = 'prp.product_asin as product_asin';
        $fields[] = 'prp.title as title';
        $fields[] = 'prr.id as id';
        $fields[] = 'prr.review_code';

        $fields2 = array();
        $fields2[] = 'review_date';
        $fields2[] = 'country';
        $fields2[] = 'review_text';
        $fields2[] = 'star';
        $fields2[] = 'author';

        $sort_column = '';
        $sort_direction = '';
        if(!$count)
        {
            if(isset($filters['sort_column']))
            {
                if($filters['sort_column'] > 1)
                { 
                    $filters['sort_column'] = $filters['sort_column']-1;
                   
                }

                $sort_column = (!isset($filters['sort_column'])) ? " " : $fields2[$filters['sort_column']];
                $sort_direction = (!isset($filters['sort_direction'])) ? " " : $filters['sort_direction'];
            }
        }
        
    	$review = DB::connection('mysql2')->table('product_reviews_products as prp')
                        ->leftJoin('product_reviews_reviews as prr','prp.id', '=', 
                        'prr.product_id')
                        ->leftJoin('products', function($join)
                        {
                            $join->on('products.asin', '=', 'prp.product_asin')
                            ->on('products.country', '=', 'prp.country');
                        })
                        ->whereNotNull('prr.id')
                        ->where('prp.seller_id', $seller_id)
                        ->where('prr.archieved', 1)
                        ->groupBy('prr.id')
                        ->select($fields);
                        if($count)
                        {
                            $review = $review->get()->count();
                        }
                        else
                        {
                            if(trim($sort_column) != "" AND trim($sort_direction) != "")
                            $review = $review->orderBy($sort_column, $sort_direction);
                            $review = $review->skip($filters['skip'])->take($filters['take']);
                            $review = $review->get();
                        }

        return $review;
    }
}
