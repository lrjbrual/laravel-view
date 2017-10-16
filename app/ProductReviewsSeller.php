<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ProductReviewsSeller extends Model
{
    //
    public function getProductReviewSeller()
    {
        $getSellers = DB::table('product_reviews_sellers')
                        ->get();

        return $getSellers;
    }
}
