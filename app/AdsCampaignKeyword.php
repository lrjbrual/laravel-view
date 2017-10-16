<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdsCampaignKeyword extends Model
{
    //

  	protected $connection = 'mysql2';

  	protected $fillable = ['seller_id', 'country', 'adgroupid', 'campaignid', 'keywordid', 'keywordtext', 'matchtype', 'bid', 'state', 'servingstatus', 'creationdate', 'lastupdateddate'];
}
