<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdsCampaignReportByKeyword extends Model
{
    //

  	protected $connection = 'mysql2';
  	protected $fillable = ['seller_id', 'country', 'keyword_id', 'query', 'impressions', 'clicks', 'cost', 'attributedconversions1dsamesku', 'attributedconversions1d', 'attributedsales1dsamesku', 'attributedsales1d', 'attributedconversions7dsamesku', 'attributedconversions7d', 'attributedsales7dsamesku', 'attributedsales7d', 'attributedconversions30dsamesku', 'attributedconversions30d', 'attributedsales30dsamesku', 'attributedsales30d', 'posted_date'];
}
