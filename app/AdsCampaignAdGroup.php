<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdsCampaignAdGroup extends Model
{
    //

  	protected $connection = 'mysql2';

  	protected $fillable = ['seller_id', 'country', 'adgroupid', 'campaignid', 'name', 'defaultbid', 'state', 'servingstatus', 'creationdate', 'lastupdateddate'];
}
