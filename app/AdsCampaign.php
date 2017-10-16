<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdsCampaign extends Model
{
    //

  	protected $connection = 'mysql2';

  	protected $fillable = ['seller_id', 'country', 'campaignid', 'name', 'campaigntype', 'targetingtype', 'premiumbidadjustment', 'dailybudget', 'state', 'servingstatus', 'startdate', 'enddate', 'creationdate', 'lastupdateddate'];
}
