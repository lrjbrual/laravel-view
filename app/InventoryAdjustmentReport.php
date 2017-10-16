<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryAdjustmentReport extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql2';

    public function get_fnsku_claims($seller_id, $country_code = null, $today=null, $dt=null, $mkp)
    {
      if ($country_code != null && $today == 'YES') {
        // $c = DB::connection('mysql2')->table('fulfillment_country')->where('country_code', '=', $country_code)->get();
        // $c_arr = array();
        // foreach ($c as $key => $value) {
        //   $c_arr[] = $value->fulfillment_center_id;
        // }
        $date_from = Carbon::today()->subMonths(18)->toDateString();

        $c_arr = array();
        if ($mkp == 2) {
          $c_arr[] = 'uk';
          $c_arr[] = 'de';
          $c_arr[] = 'es';
          $c_arr[] = 'it';
          $c_arr[] = 'fr';
        } else {
          $c_arr[] = $country_code;
        }
        return DB::connection('mysql2')
        ->table('inventory_adjustment_reports')
        ->where('inventory_adjustment_reports.seller_id', '=', $seller_id)
        ->whereIn('inventory_adjustment_reports.country', $c_arr)
        ->whereRaw("DATE(inventory_adjustment_reports.adjusted_date) >= '".$date_from."'")
        ->groupBy('inventory_adjustment_reports.fnsku')
        ->get([
          'inventory_adjustment_reports.fnsku',
          'inventory_adjustment_reports.asin'
        ]);
      } elseif ($country_code != null && $today == 'NO') {
        // $c = DB::connection('mysql2')->table('fulfillment_country')->where('country_code', '=', $country_code)->get();
        // $c_arr = array();
        // foreach ($c as $key => $value) {
        //   $c_arr[] = $value->fulfillment_center_id;
        // }

        $c_arr = array();
        if ($mkp == 2) {
          $c_arr[] = 'uk';
          $c_arr[] = 'de';
          $c_arr[] = 'es';
          $c_arr[] = 'it';
          $c_arr[] = 'fr';
        } else {
          $c_arr[] = $country_code;
        }
        return DB::connection('mysql2')
        ->table('inventory_adjustment_reports')
        ->where('inventory_adjustment_reports.seller_id', '=', $seller_id)
        ->whereIn('inventory_adjustment_reports.country', $c_arr)
        ->whereBetween('inventory_adjustment_reports.created_at', [$dt, date('Y-m-d')])
        ->groupBy('inventory_adjustment_reports.fnsku')
        ->get([
          'inventory_adjustment_reports.fnsku',
          'inventory_adjustment_reports.asin'
        ]);
      }
    }

    public function getSingleColumns($fnsku, $country_code, $mkp) {
      $date_from = Carbon::today()->subMonths(18)->toDateString();

      $c_arr = array();
      if ($mkp == 2) {
        $c_arr[] = 'uk';
        $c_arr[] = 'de';
        $c_arr[] = 'es';
        $c_arr[] = 'it';
        $c_arr[] = 'fr';
      } else {
        $c_arr[] = $country_code;
      }

      $data = (object) null;
      $data->three = 0;
      $data->four = 0;
      $data->five = 0;
      $data->six = 0;
      $data->d = 0;
      $data->e = 0;
      $data->f = 0;
      $data->m = 0;
      $data->n = 0;
      $data->o = 0;
      $data->p = 0;
      $data->q = 0;

      $query = DB::connection('mysql2')
        ->table('inventory_adjustment_reports')
        ->where('inventory_adjustment_reports.fnsku', '=', $fnsku)
        ->whereIn('inventory_adjustment_reports.country', $c_arr)
        ->whereRaw("DATE(inventory_adjustment_reports.adjusted_date) >= '".$date_from."'")
        ->get([
          'inventory_adjustment_reports.quantity',
          'inventory_adjustment_reports.reason',
          DB::raw("IF(inventory_adjustment_reports.adjusted_date + INTERVAL 44 DAY <= CURDATE(),'YES','NO') AS over_45days"),
          DB::raw("IF(inventory_adjustment_reports.adjusted_date + INTERVAL 29 DAY <= CURDATE(),'YES','NO') AS over_30days")
        ]);

      foreach ($query as $val) {
        if ($val->reason == '3') { // reason 3
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->three += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->three += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->three += $val->quantity;
          }
        } elseif ($val->reason == '4') {  // reason 4
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->four += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->four += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->four += $val->quantity;
          }
        } elseif ($val->reason == '5') {  // reason 5
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->five += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->five += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->five += $val->quantity;
          }
        }  elseif ($val->reason == '6') {  // reason 6
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->six += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->six += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->six += $val->quantity;
          }
        } elseif ($val->reason == 'D') {  // reason D
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->d += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->d += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->d += $val->quantity;
          }
        } elseif ($val->reason == 'E') { // reason E
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->e += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->e += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->e += $val->quantity;
          }
        } elseif ($val->reason == 'F') { // reason F
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->f += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->f += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->f += $val->quantity;
          }
        } elseif ($val->reason == 'M') { // reason M
          if ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->m += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->m += $val->quantity;
          }
        } elseif ($val->reason == 'N') { // reason N
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->n += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->n += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->n += $val->quantity;
          }
        } elseif ($val->reason == 'O') { // reason O
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->o += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->o += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->o += $val->quantity;
          }
        } elseif ($val->reason == 'P') { // reason P
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->p += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->p += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->p += $val->quantity;
          }
        } elseif ($val->reason == 'Q') { // reason Q
          if ($val->over_30days == 'NO') {
            if ($val->quantity > 0) {
              $data->q += $val->quantity;
            }
          } elseif ($val->over_30days == 'YES' && $val->over_45days == 'NO') {
            if ($val->quantity > 0) {
              $data->q += $val->quantity;
            }
          } elseif ($val->over_45days == 'YES') {
            $data->q += $val->quantity;
          }
        }
      }

      $data->sum = $data->three + $data->four + $data->five + $data->d + $data->e + $data->f + $data->m + $data->p + $data->q;

      return $data;
    }

    public function getNbOrder($asin, $mkp, $country) {
      $data = (object) null;

      if ($mkp == 2) {
        $r_exp = '.uk|.it|.fr|.de|.es';
      } else {
        if ($country == 'us') {
          $r_exp = '.com';
        } elseif ($country == 'ca') {
          $r_exp = '.ca';
        }
      }

      $currency = '';
      if ($country == 'us') {
        $currency == 'USD';
      } elseif ($country == 'ca') {
        $currency = 'CAD';
      } elseif ($country == 'uk') {
        $currency = 'GBP';
      } elseif ($country == 'it' || $country == 'fr' || $country == 'de' || $country == 'es') {
        $currency = 'EUR';
      }

      $query1 = DB::connection('mysql2')
                ->table('financial_events_reports')
                ->whereRaw("financial_events_reports.type = 'Order'")
                ->whereRaw("financial_events_reports.price_type = 'Principal'")
                ->whereRaw("financial_events_reports.asin = '".$asin."'")
                ->where('financial_events_reports.quantity', '=', 1)
                ->whereRaw("DATE(financial_events_reports.posted_date + INTERVAL 89 DAY) >= CURDATE()")
                ->whereRaw("financial_events_reports.marketplace_name REGEXP '".$r_exp."'")
                ->get([
                  'financial_events_reports.quantity',
                  'financial_events_reports.price_amount',
                  'financial_events_reports.currency',
                  DB::raw("(financial_events_reports.irft_commission
                    +financial_events_reports.irft_fba_per_order_fulfillmen_fee
                    +financial_events_reports.irft_fba_per_unit_fulfillmen_fee
                    +financial_events_reports.irft_fba_weight_based_fee
                    +financial_events_reports.irft_fixed_closing_fee
                    +financial_events_reports.irft_sales_tax_collection_fee
                    +financial_events_reports.irft_variable_closing_fee) AS revenue")
                ]);

      $quantity = 0;
      $revenue = 0;

      foreach ($query1 as $val1) {
        if ($quantity < 2000) {
          $quantity += $val1->quantity;
          $revenue += currency($val1->price_amount, $val1->currency, $currency, false);
          $revenue += currency($val1->revenue, $val1->currency, $currency, false);
        }
      }

      if ($quantity >= 2000) {
        $quantity = 2000;
      } else {
        $query2 = DB::connection('mysql2')
                ->table('financial_events_reports')
                ->whereRaw("financial_events_reports.type = 'Order'")
                ->whereRaw("financial_events_reports.asin = '".$asin."'")
                ->where('financial_events_reports.quantity', '=', 1)
                ->whereRaw("financial_events_reports.marketplace_name REGEXP '".$r_exp."'")
                ->groupBy('financial_events_reports.order_id')
                ->get([
                  'financial_events_reports.quantity',
                  'financial_events_reports.price_amount',
                  'financial_events_reports.currency',
                  DB::raw("(financial_events_reports.irft_commission
                    +financial_events_reports.irft_fba_per_order_fulfillmen_fee
                    +financial_events_reports.irft_fba_per_unit_fulfillmen_fee
                    +financial_events_reports.irft_fba_weight_based_fee
                    +financial_events_reports.irft_fixed_closing_fee
                    +financial_events_reports.irft_sales_tax_collection_fee
                    +financial_events_reports.irft_variable_closing_fee) AS revenue")
                ]);

        $quantity = 0;
        $revenue = 0;

        foreach ($query2 as $val2) {
          if ($quantity < 2000) {
            $quantity += $val2->quantity;
            $revenue += currency($val2->price_amount, $val2->currency, $currency, false);
            $revenue += currency($val2->revenue, $val2->currency, $currency, false);
          }
        }
      }

      $data->quantity = $quantity;
      $data->revenue = $revenue;
      if ($quantity == 0) {
        $data->average = 0;
      } else {
        $data->average = $revenue / $quantity;
      }

      return $data;
    }

    public function getFMV($asin, $mkp, $country) {
      $data = (object) null;

      if ($mkp == 2) {
        $r_exp = '.uk|.it|.fr|.de|.es';
      } else {
        if ($country == 'us') {
          $r_exp = '.com';
        } elseif ($country == 'ca') {
          $r_exp = '.ca';
        }
      }

      $currency = '';
      if ($country == 'us') {
        $currency == 'USD';
      } elseif ($country == 'ca') {
        $currency = 'CAD';
      } elseif ($country == 'uk') {
        $currency = 'GBP';
      } elseif ($country == 'it' || $country == 'fr' || $country == 'de' || $country == 'es') {
        $currency = 'EUR';
      }

      $query = DB::connection('mysql2')
                ->table('financial_events_reports')
                ->whereRaw("financial_events_reports.type = 'Order'")
                ->whereRaw("financial_events_reports.asin = '".$asin."'")
                ->where('financial_events_reports.quantity', '=', 1)
                ->whereRaw("financial_events_reports.marketplace_name REGEXP '".$r_exp."'")
                ->groupBy('financial_events_reports.order_id')
                ->get([
                  'financial_events_reports.quantity',
                  'financial_events_reports.price_amount',
                  'financial_events_reports.currency',
                  DB::raw("(financial_events_reports.irft_commission
                    +financial_events_reports.irft_fba_per_order_fulfillmen_fee
                    +financial_events_reports.irft_fba_per_unit_fulfillmen_fee
                    +financial_events_reports.irft_fba_weight_based_fee
                    +financial_events_reports.irft_fixed_closing_fee
                    +financial_events_reports.irft_sales_tax_collection_fee
                    +financial_events_reports.irft_variable_closing_fee) AS revenue")
                ]);
      $sales = 0;
      $cost = 0;
      $quantity = 0;
      foreach ($query as $val) {
        if ($quantity < 2000) {
          $quantity += $val->quantity;
          $sales += currency($val->price_amount, $val->currency, $currency, false);
          $cost += currency($val->revenue, $val->currency, $currency, false);
        }
      }
      $data->sales = $sales;
      $data->cost = $cost;
      $data->quantity = $quantity;
      return $data;

    }

    public function getFMV3months($asin, $mkp, $country) {
      $data = (object) null;

      if ($mkp == 2) {
        $r_exp = '.uk|.it|.fr|.de|.es';
      } else {
        if ($country == 'us') {
          $r_exp = '.com';
        } elseif ($country == 'ca') {
          $r_exp = '.ca';
        }
      }

      $currency = '';
      if ($country == 'us') {
        $currency == 'USD';
      } elseif ($country == 'ca') {
        $currency = 'CAD';
      } elseif ($country == 'uk') {
        $currency = 'GBP';
      } elseif ($country == 'it' || $country == 'fr' || $country == 'de' || $country == 'es') {
        $currency = 'EUR';
      }

      $query = DB::connection('mysql2')
                ->table('financial_events_reports')
                ->whereRaw("financial_events_reports.type = 'Order'")
                ->whereRaw("financial_events_reports.asin = '".$asin."'")
                ->where('financial_events_reports.quantity', '=', 1)
                ->whereRaw("DATE(financial_events_reports.posted_date + INTERVAL 89 DAY) >= CURDATE()")
                ->whereRaw("financial_events_reports.marketplace_name REGEXP '".$r_exp."'")
                ->groupBy('financial_events_reports.order_id')
                ->get([
                  'financial_events_reports.quantity',
                  'financial_events_reports.price_amount',
                  'financial_events_reports.currency',
                  DB::raw("(financial_events_reports.irft_commission
                    +financial_events_reports.irft_fba_per_order_fulfillmen_fee
                    +financial_events_reports.irft_fba_per_unit_fulfillmen_fee
                    +financial_events_reports.irft_fba_weight_based_fee
                    +financial_events_reports.irft_fixed_closing_fee
                    +financial_events_reports.irft_sales_tax_collection_fee
                    +financial_events_reports.irft_variable_closing_fee) AS revenue")
                ]);
      $sales = 0;
      $cost = 0;
      $quantity = 0;
      foreach ($query as $val) {
        $quantity += $val->quantity;
        $sales += currency($val->price_amount, $val->currency, $currency, false);
        $cost += currency($val->revenue, $val->currency, $currency, false);
      }
      $data->sales = $sales;
      $data->cost = $cost;
      $data->quantity = $quantity;
      return $data;
      
    }

    public function getReimbursedUnits($fnsku, $mkp, $country) {
      $data = (object) null;
      $r_arr = array();
      $r_arr[] = 'Damaged_Inbound';
      $r_arr[] = 'Damaged_Warehouse';
      $r_arr[] = 'FeeCorrection';
      $r_arr[] = 'Lost_Inbound';
      $r_arr[] = 'Lost_Warehouse';

      if ($mkp == 2) {     
        $c_arr[] = 'GBP';
        $c_arr[] = 'EUR';
      } else {
        if ($country == 'us') {
          $c_arr[] = 'USD';
        } elseif ($country == 'ca') {
          $c_arr[] = 'CAD';
        }
      }

      $query = DB::connection('mysql2')
                ->table('reimbursements')
                ->where('reimbursements.fnsku', '=', $fnsku)
                ->whereIN('reimbursements.currency_unit', $c_arr)
                ->whereIN('reimbursements.reason', $r_arr)
                ->whereRaw('(reimbursements.amazon_order_id= "" OR reimbursements.amazon_order_id=NULL)')
                ->get([
                  'reimbursements.quantity_reimbursed_cash',
                  'reimbursements.quantity_reimbursed_inventory',
                  'reimbursements.reason',
                  DB::raw("IF(reimbursements.approval_date + INTERVAL 29 DAY <= CURDATE(),'YES','NO') AS over_30days")
                ]);

      $quantity = 0;
      $quantity_lost = 0;
      $quantity_damaged_in = 0;
      $quantity_damaged_wa = 0;

      foreach ($query as $val) {
        $quantity += $val->quantity_reimbursed_cash;
        if ($val->reason == 'Lost_Warehouse') {
          if ($val->over_30days == 'YES') {
            $quantity_lost += ($val->quantity_reimbursed_cash+$val->quantity_reimbursed_inventory);
          }
        }
        if ($val->reason == 'Damaged_Inbound') {
          if ($val->over_30days == 'YES') {
            $quantity_damaged_in += ($val->quantity_reimbursed_cash+$val->quantity_reimbursed_inventory);
          }
        }
        if ($val->reason == 'Damaged_Warehouse') {
          if ($val->over_30days == 'YES') {
            $quantity_damaged_wa += ($val->quantity_reimbursed_cash+$val->quantity_reimbursed_inventory);
          }
        }
      }

      $data->reimbursed_units = $quantity;
      $data->quantity_lost = $quantity_lost;
      $data->quantity_damaged_in = $quantity_damaged_in;
      $data->quantity_damaged_wa = $quantity_damaged_wa;

      return $data;
    }

    public function getLatestLost($fnsku, $mkp, $country) {
      $data = (object) null;
      $r_arr = array();
      $r_arr[] = 'Lost_Warehouse';

      if ($mkp == 2) {     
        $c_arr[] = 'GBP';
        $c_arr[] = 'EUR';
      } else {
        if ($country == 'us') {
          $c_arr[] = 'USD';
        } elseif ($country == 'ca') {
          $c_arr[] = 'CAD';
        }
      }

      $query = DB::connection('mysql2')
                ->table('reimbursements')
                ->where('reimbursements.fnsku', '=', $fnsku)
                ->whereIN('reimbursements.currency_unit', $c_arr)
                ->whereIN('reimbursements.reason', $r_arr)                
                ->whereRaw('(reimbursements.amazon_order_id= "" OR reimbursements.amazon_order_id=NULL)')
                ->whereRaw("DATE(approval_date + INTERVAL 29 DAY) <= CURDATE()")
                ->get([
                  'reimbursements.quantity_reimbursed_cash',
                  'reimbursements.quantity_reimbursed_inventory',
                  'reimbursements.reason'
                ]);
      $quantity_lost = 0;
      foreach ($query as $val) {
        $quantity_lost += ($val->quantity_reimbursed_cash+$val->quantity_reimbursed_inventory);
      }

      $data->quantity_lost = $quantity_lost;

      return $data;
    }

    public function getLatestDamaged($fnsku, $mkp, $country) {
      $data = (object) null;
      $r_arr = array();
      $r_arr[] = 'Damaged_Inbound';
      $r_arr[] = 'Damaged_Warehouse';

      if ($mkp == 2) {     
        $c_arr[] = 'GBP';
        $c_arr[] = 'EUR';
      } else {
        if ($country == 'us') {
          $c_arr[] = 'USD';
        } elseif ($country == 'ca') {
          $c_arr[] = 'CAD';
        }
      }

      $query = DB::connection('mysql2')
                ->table('reimbursements')
                ->where('reimbursements.fnsku', '=', $fnsku)
                ->whereIN('reimbursements.currency_unit', $c_arr)
                ->whereIN('reimbursements.reason', $r_arr)
                ->whereRaw('(reimbursements.amazon_order_id= "" OR reimbursements.amazon_order_id=NULL)')
                ->whereRaw("DATE(approval_date + INTERVAL 29 DAY) <= CURDATE()")
                ->get([
                  'reimbursements.quantity_reimbursed_cash',
                  'reimbursements.quantity_reimbursed_inventory',
                  'reimbursements.reason'
                ]);
      $quantity_damaged = 0;
      foreach ($query as $val) {
        $quantity_damaged += ($val->quantity_reimbursed_cash+$val->quantity_reimbursed_inventory);
      }

      $data->quantity_damaged = $quantity_damaged;

      return $data;
    }

    public function getFnsku($fnsku) {
      $data = (object) null;
      $query = DB::connection('mysql2')
                ->table('fnsku_claims')
                ->where('fnsku_claims.fnsku', '=', $fnsku)
                ->get([
                  'fnsku_claims.support_ticket',
                  'fnsku_claims.support_ticket2',
                  'fnsku_claims.status',
                  'fnsku_claims.comments'
                ]);
      $st = NULL;
      $st2 = NULL;
      $s = NULL;
      $c = NULL;
      foreach ($query as $val) {
        $st = $val->support_ticket;
        $st2 = $val->support_ticket2;
        $s = $val->status;
        $c = $val->comments;
      }
      $data->support_ticket = $st;
      $data->support_ticket2 = $st2;
      $data->status = $s;
      $data->comments = $c;

      return $data;
    }

    public function getSalesUk($seller_id) {
      $data = (object) null;
      $r_exp = '.uk';
      $query = DB::connection('mysql2')
                ->table('flat_file_all_orders_by_dates')
                ->where('flat_file_all_orders_by_dates.seller_id', '=', $seller_id)
                ->whereRaw("flat_file_all_orders_by_dates.item_status = 'Shipped'")
                ->whereRaw("flat_file_all_orders_by_dates.sales_channel REGEXP '".$r_exp."'")
                ->get([
                  'flat_file_all_orders_by_dates.quantity'
                ]);
      $s = 0;
      foreach ($query as $val) {
        $s += $val->quantity;
      }

      $data->sales = $s;

      return $data;
    }

    public function getSalesFr($seller_id) {
      $data = (object) null;
      $r_exp = '.fr';
      $query = DB::connection('mysql2')
                ->table('flat_file_all_orders_by_dates')
                ->where('flat_file_all_orders_by_dates.seller_id', '=', $seller_id)
                ->whereRaw("flat_file_all_orders_by_dates.item_status = 'Shipped'")
                ->whereRaw("flat_file_all_orders_by_dates.sales_channel REGEXP '".$r_exp."'")
                ->get([
                  'flat_file_all_orders_by_dates.quantity'
                ]);
      $s = 0;
      foreach ($query as $val) {
        $s += $val->quantity;
      }

      $data->sales = $s;

      return $data;
    }

    public function getSalesIt($seller_id) {
      $data = (object) null;
      $r_exp = '.it';
      $query = DB::connection('mysql2')
                ->table('flat_file_all_orders_by_dates')
                ->where('flat_file_all_orders_by_dates.seller_id', '=', $seller_id)
                ->whereRaw("flat_file_all_orders_by_dates.item_status = 'Shipped'")
                ->whereRaw("flat_file_all_orders_by_dates.sales_channel REGEXP '".$r_exp."'")
                ->get([
                  'flat_file_all_orders_by_dates.quantity'
                ]);
      $s = 0;
      foreach ($query as $val) {
        $s += $val->quantity;
      }

      $data->sales = $s;

      return $data;
    }

    public function getSalesEs($seller_id) {
      $data = (object) null;
      $r_exp = '.es';
      $query = DB::connection('mysql2')
                ->table('flat_file_all_orders_by_dates')
                ->where('flat_file_all_orders_by_dates.seller_id', '=', $seller_id)
                ->whereRaw("flat_file_all_orders_by_dates.item_status = 'Shipped'")
                ->whereRaw("flat_file_all_orders_by_dates.sales_channel REGEXP '".$r_exp."'")
                ->get([
                  'flat_file_all_orders_by_dates.quantity'
                ]);
      $s = 0;
      foreach ($query as $val) {
        $s += $val->quantity;
      }

      $data->sales = $s;

      return $data;
    }

    public function getSalesDe($seller_id) {
      $data = (object) null;
      $r_exp = '.de';
      $query = DB::connection('mysql2')
                ->table('flat_file_all_orders_by_dates')
                ->where('flat_file_all_orders_by_dates.seller_id', '=', $seller_id)
                ->whereRaw("flat_file_all_orders_by_dates.item_status = 'Shipped'")
                ->whereRaw("flat_file_all_orders_by_dates.sales_channel REGEXP '".$r_exp."'")
                ->get([
                  'flat_file_all_orders_by_dates.quantity'
                ]);
      $s = 0;
      foreach ($query as $val) {
        $s += $val->quantity;
      }

      $data->sales = $s;

      return $data;
    }

    public function isExist($data=array()){
      $db_query = DB::connection('mysql2');
      $query = $db_query->table('inventory_adjustment_reports')->select('*');
        foreach ($data as $key => $value) {
            $query->where( $key, $value );
        }
      $data = $query->count();
      if($data>0) return true;
      else return false;
    }
    public function insertData($data = array()){
        $db_query = DB::connection('mysql2');
        if($data!=null AND count($data)>0){
            $db_query->table('inventory_adjustment_reports')->insert($data);
            return true;
        }else{
            return false;
        }
    }
    public function getRecords($fields = array('*'),$cond=array(),$order=array(),$checkifempty=false){
        $q = DB::connection('mysql2')->table('inventory_adjustment_reports');
        $q = $q->select($fields);

        if(count($cond)>0){
        end($cond);$last=key($cond);reset($cond);$first = key($cond);
        foreach($cond as $key => $c){
          if ($key === $first){
            reset($cond);
          }
          $thiskey = key($cond);
          if(count($c)>1) $q = $q->where($thiskey,$c[0],$c[1]);
          else $q = $q->where($thiskey,$c);
          next($cond);
        }
        }

        if(count($order)>0){
        $q = $q->orderBy($order[0],$order[1]);
        }else if($checkifempty == 0){
            $q = $q->orderBy('adjusted_date','desc');
        }

        if($checkifempty == 0){
            $q = $q->limit(1)->get();
        }else if($checkifempty){
             $q = $q->limit(1)->get()->count();
        }else{
             $q = $q->get();
        }
        return $q;
    }
}
