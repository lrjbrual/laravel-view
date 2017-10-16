<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrmLoad extends Model
{
    public function seller()
    {
        return $this->belongsTo('App\Seller','seller_id');
    }

}
