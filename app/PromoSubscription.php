<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoSubscription extends Model
{
    //
     protected $fillable = ['seller_id','voucher_code','is_used'];
}
