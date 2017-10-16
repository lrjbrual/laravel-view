<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Seller extends Model
{
    //
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'company',
        'address',
        'city',
        'state',
        'zipcode',
        'country_id',
        'phone',
        'is_deleted',
        'reason_for_leaving',
        'email_for_crm',
        'emailpw_for_crm'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function billing()
    {
      return $this->hasOne('App\Billing', 'seller_id');
    }

    public function trialperiod(){
      return $this->hasOne('App\TrialPeriod', 'seller_id');
    }

    public function user()
    {
        return $this->hasMany('App\User', 'seller_id');
    }

    public function basesubscription()
    {
        return $this->hasMany('App\BaseSubscriptionSeller', 'seller_id');
    }

    public function mkpassign()
    {
        return $this->hasMany('App\MarketplaceAssign', 'seller_id');
    }

    public function campaign()
    {
        return $this->hasMany('App\Campaign', 'seller_id');
    }

    public function campaign_temp()
    {
        return $this->setConnection('mysql2')->hasMany('App\CampaignTemplate', 'seller_id');
    }

    public function crm_load()
    {
        return $this->hasMany('App\CrmLoad');
    }

    // fb refunds
    public function reimbursement()
    {
        return $this->setConnection('mysql2')->hasMany('App\Reimbursement', 'seller_id');
    }

    public function settlement_report()
    {
        return $this->setConnection('mysql2')->hasMany('App\SettlementReport', 'seller_id');
    }

    public function returns_report()
    {
        return $this->setConnection('mysql2')->hasMany('App\ReturnsReport', 'seller_id');
    }

    public function inventory_adjustment_report()
    {
        return $this->setConnection('mysql2')->hasMany('App\InventoryAdjustmentReport', 'seller_id');
    }
    // end fba refunds

    public function getRecords($table="",$fields=array('*'),$cond=array(),$order=array()){

        $q = DB::table($table);
        $q = $q->select($fields);

        if(count($cond)>0){
          end($cond);$last=key($cond);reset($cond);$first = key($cond);
          foreach($cond as $key => $c){
            if ($key === $first){
              reset($cond);
            }
            $thiskey = key($cond);
            $q = $q->where($thiskey,$c);
            next($cond);
          }
        }

        if(count($order)>0){
          $q = $q->orderBy($order[0],$order[1]);
        }

        $q = $q->get();
        return $q;
    }

    public function insertRecord(array $data)
    {

      return Seller::create([
        'firstname'=>$data['fname'],
        'lastname'=>$data['lname'],
        'email'=>$data['email'],
        'company'=>$data['company'],
        'address'=>'',
        'city'=>'',
        'state'=>'',
        'zipcode'=>'',
        'country_id'=>null,
        'phone'=>'',
        'is_deleted'=>'0',
        'is_trial'=>false,
        'reason_for_leaving'=>'',
        'email_for_crm'=>'',
        'emailpw_for_crm'=>'',
        'email_for_sc'=>''
      ]);
  }
}
