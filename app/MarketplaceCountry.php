<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketplaceCountry extends Model
{
    //

    public function marketplace(){
      return $this->belongsTo('App\Marketplace','marketplace_id');
    }

    //not sure if it would work
    //edit: junry says this one would work!
    public function country(){
      return $this->belongsTo('Webpatser\Countries\Countries','country_id');
    }


    public function getMarketplaceCountryName(){
      $q = MarketplaceCountry::select('countries.name','countries.id')
      ->join('countries', 'marketplace_countries.country_id', '=', 'countries.id')
      ->orderBy('countries.name')
      ->get();
      return $q;
    }
}
