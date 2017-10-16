<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdsCampaignProduct extends Model
{
    //

  	protected $connection = 'mysql2';

  	protected $fillable = ['seller_id', 'country', 'adgroupid', 'campaignid', 'adid', 'sku', 'asin', 'state', 'servingstatus', 'creationdate', 'lastupdateddate'];
}
