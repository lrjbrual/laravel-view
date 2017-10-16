<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ProductReviewsProduct extends Model
{
    //
    protected $connection = 'mysql2';
    protected $fillable = array('url_id', 'product_url','title','star_rating','nb_of_reviews','date_of_change');

    public function getProducts($seller_id,$filters,$count = false)
    {
        $fields = array();
        $fields[] = 'prp.country as country';
        $fields[] = 'prp.star_rating as star_rating';
        $fields[] = 'prp.nb_of_reviews as nb_of_reviews';
        $fields[] = 'prp.product_asin as product_asin';
        $fields[] = 'prp.title as title';
        $fields[] = 'products.sku as sku';
        $fields[] = 'prp.id as id';

        $fields2 = array();
        $fields2[] = 'country';
        $fields2[] = 'star_rating';
        $fields2[] = 'nb_of_reviews';

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

    	$products = DB::connection('mysql2')->table('product_reviews_products as prp')
                                            ->leftJoin('products', function($join)
                                            {
                                                $join->on('products.asin', '=', 'prp.product_asin')
                                                ->on('products.country', '=', 'prp.country');
                                            })
                                            ->select($fields)
                                            ->where('prp.seller_id',$seller_id)
                                            ->groupBy('prp.id');
                                            if($count)
                                            {
                                                $products = $products->get()->count();
                                            }
                                            else
                                            {
                                                if(trim($sort_column) != "" AND trim($sort_direction) != "")
                                                $products = $products->orderBy($sort_column, $sort_direction);
                                                $products = $products->skip($filters['skip'])->take($filters['take']);
                                                $products = $products->get();
                                            }
        return $products;
    }

    public function getProductWithSKU($product_id,$seller_id)
    {
        $products = DB::connection('mysql2')->table('product_reviews_products as prp')
                                            ->leftJoin('products', function($join)
                                            {
                                                $join->on('products.asin', '=', 'prp.product_asin')
                                                ->on('products.country', '=', 'prp.country');
                                            })
                                            ->where('prp.seller_id',$seller_id)
                                            ->where('prp.id', $product_id)
                                            ->select('products.sku','prp.country','prp.product_asin')
                                            ->first();

        return $products;
    }

}
