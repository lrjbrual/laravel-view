<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FbaFulfillmentInventoryReceipts extends Model
{
  protected $connection = 'mysql2';
  protected $fillable = [
      'seller_id',
    	'received_date',
    	'fnsku',
    	'sku',
    	'product_name',
    	'quantity',
    	'fba_shipment_id',
    	'fulfillment_center_id',
      'mkp',
  ];
}
