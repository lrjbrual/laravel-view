<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Dusk\DuskServiceProvider;


use Illuminate\Support\Facades\Schema;
use App\SellerCronSchedule;
use App\CronMasterList;
use App\MarketplaceAssign;
use App\AmazonSellerDetail;
use Carbon\Carbon;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    private $route = "";
    private $seller_id=null;
    private $sched=null;
    private $cron_name=null;
    private $enable=null;
    public function boot()
    {
        //uncomment when running the composer update for solutions require
        if (Schema::hasTable('cron_master_lists')) {
            \Event::listen('cron.collectJobs', function() {
                \Cron::setDisablePreventOverlapping(true);
                \Cron::setDatabaseLogging(true);
                \Cron::setLogOnlyErrorJobsToDatabase(false);
                \Cron::setDeleteDatabaseEntriesAfter(0);
                \Cron::setRunInterval(1);
                //seller base crons
                $q = new SellerCronSchedule();
                $mkp_model= new MarketplaceAssign();

                $seller_crons = $q->getRecords(array('*', 'seller_cron_schedules.seller_id as siid'));
                foreach ($seller_crons as $cron) {

                    $where = array('seller_id'=>$cron->siid);
                    $mkp_assign = $mkp_model->getRecords(config('constant.tables.mkp'),array('*'),$where,array());

                    $this->cron_name = $cron->description." SellerID ".$cron->siid;
                    $this->sched = $cron->minutes." ".$cron->hours." ".$cron->day_of_month." ".$cron->month." ".$cron->day_of_week;
                    $this->route = $cron->route;
                    $this->seller_id = $cron->siid;
                    $this->enable = false;
                    if($cron->isactive == 1) $this->enable = true;
                    //adding seller base crons
                    //if($this->seller_id > 0){
                    //if($this->_is_name_in_cronjobs($this->cron_name,\Cron::getCronJobs())){
                    
                    \Cron::add($this->cron_name, $this->sched, function() use($cron,$mkp_assign) {

                    $noMkpCron = ['CRMAutoCampaign','UpdateProductImage'];
                      if($cron->siid!=0){
                        if(in_array($cron->route,$noMkpCron)){
                          exec("curl '" . config('app.url') . "/".$cron->route."?seller_id=".$cron->siid."' > /dev/null 2>&1 & echo $!",$output);
                        }else{
                          foreach($mkp_assign as $mkp){
                            exec("curl '" . config('app.url') . "/".$cron->route."?seller_id=".$cron->siid."&mkp=".$mkp->marketplace_id."' > /dev/null 2>&1 & echo $!",$output);
                          }
                        }
                      }else{
                        exec("curl " . config('app.url') . "/".$cron->route);
                      }

                    }, $this->enable);
                

                    
                
                    //}
                    //}
                }

                //non-seller base crons
                // $crons = CronMasterList::all()->where('is_seller_cron', 0);
                // $hour_interval = 0;
                // foreach ($crons as $cron) {
                //     $this->cron_name = $cron->description;
                //     $this->sched = "0 ".$hour_interval." * * *";
                //     //$this->sched = "38 7 * * *";
                //     $this->route = $cron->route;
                //     //if($this->_is_name_in_cronjobs($this->cron_name,\Cron::getCronJobs())){
                //         \Cron::add($this->cron_name, $this->sched, function() use($cron) {
                //             exec("curl " . config('app.url') . "/".$cron->route);
                //         }, true);
                //         $hour_interval++;
                //     //}
                // }
                \Event::listen('cron.jobError', function($name, $return, $runtime, $rundate){
                    \Log::error('Job with the name ' . $name . ' returned an error.'.$return);
                });
            });
        }

        Cashier::useCurrency('gbp', '£');

        app('view')->composer('layouts.setting', function ($view) {
            $action = app('request')->route()->getAction();

            $controller = class_basename($action['controller']);

            list($controller, $action) = explode('@', $controller);

            $view->with('controller', $controller);
        });
    }

    private function _is_name_in_cronjobs($needle, $haystack){
      foreach ($haystack as $h) {
        if(in_array($needle,$h)){
          return true;
        }
      }
      return false;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        if ($this->app->environment('local', 'testing')) {
        $this->app->register(DuskServiceProvider::class);
    }
    }
}
