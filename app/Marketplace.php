<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    //

    public function marketplace_assign()
    {
        return $this->hasMany('App\MarketplaceAssign');
    }
    public function marketplace_country()
    {
        return $this->hasMany('App\MarketplaceCountry');
    }

    


}
