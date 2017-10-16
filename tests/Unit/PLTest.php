<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers\Trendle\PnLController;
use Auth;
use App\User;
use App\Billing;
use App\FinancialEventsReport;
use Mockery;
use App;

class PLTest extends TestCase
{
    public function provider_test_pnl_http(){
      return array(
        array(1,200),//case ok 200
        array(2,302),//case error 302 redirect

      );
    }
    /**
     *@group pnlcontroller
     *@dataProvider provider_test_pnl_http
     */
    public function test_pnl_http($mode,$expectedstatus)
    {
        if($mode == 1){
          //mock authenticated user
          $user =  User::where('email','=', 'admin@ls.com')->first();
          $this->actingAs($user);
        }else if($mode == 2){
          //just casually access /pnl without mocking authenticated user.
        }

        $response = $this->get('/pnl');
        $response->assertStatus($expectedstatus);
    }

    /**
     *@group pnlcontroller
     */
    public function test_getPnLCostTable(){

      $user =  User::where('email','=', 'admin@ls.com')->first();
      $this->actingAs($user);
      $response = $this->call('POST','pnl/pnl_getpnlcosttable',array('_token'=>csrf_token(),'date_from'=>'2017-01-01','date_to'=>'2017-05-01'));
      $response->assertStatus(200);
    }


    /**
     *@group pnlcontroller
     */
    public function test_getCountryListForThisSeller()
    {
      $pnlcontroller = new PnLController;
      $u = new Auth;//use the Auth facade
      Auth::shouldReceive('user')->andReturn((object) ['seller_id' => 1]);//mock the  data inside the Auth

      $result = $pnlcontroller->getCountryListForThisSeller();
      if(is_object($result)){
        if(count($result)>0){//has mkp
          $this->assertArrayHasKey('iso_3166_2',$result[0]);
        }else{
          $this->assertEquals(true,true);
        }
      }else{
        $this->assertEquals(true,false);
      }

    }


    public function provider_test_iso_3166_2_to_marketplacename(){
      return array(
        array('GB','Amazon.co.uk'),
        array('FR','Amazon.fr'),
        array('DE','Amazon.de'),
        array('IT','Amazon.it'),
        array('ES','Amazon.es'),
        array('US','Amazon.com'),
        array('CA','Amazon.ca'),
        array('SOMETHINGDIFF','Amazon.co.uk'),
        array(null,'Amazon.co.uk'),
        array('','Amazon.co.uk'),
      );
    }
    /**
     *@group pnlcontroller
     *@dataProvider provider_test_iso_3166_2_to_marketplacename
     */
    public function test_iso_3166_2_to_marketplacename($mkp,$expected)
    {
      $pnlcontroller = new PnLController;
      $result = $pnlcontroller->iso_3166_2_to_marketplacename($mkp);
      $this->assertEquals($result,$expected);

    }

    public function provider_test_getSumFromArrayStack(){
      return array(

        //case where mkp is amazon.fr
        array(
          array(
            array('mkp'=>'Amazon.co.uk','sum'=>'2'),
            array('mkp'=>'Amazon.com','sum'=>'4'),
            array('mkp'=>'Amazon.fr','sum'=>'6'),
            array('mkp'=>'Amazon.de','sum'=>'8'),
          ),
          'Amazon.fr',
          '6',//expect 6
        ),

        //case where mkp is amazon.com
        array(
          array(
            array('mkp'=>'Amazon.ca','sum'=>'239.8'),
            array('mkp'=>'Amazon.co.uk','sum'=>'0.0'),
            array('mkp'=>'Amazon.com','sum'=>'470.21'),
            array('mkp'=>'Amazon.de','sum'=>'0.0'),
          ),
          'Amazon.com',
          '470.21',//expect 82
        ),

        //case where mkpname is not existing on the arraystack
        array(
          array(
            array('mkp'=>'Amazon.co.uk','sum'=>'100'),
            array('mkp'=>'Amazon.com','sum'=>'-99'),
            array('mkp'=>'Amazon.fr','sum'=>'-21'),
            array('mkp'=>'Amazon.de','sum'=>'443'),
          ),
          'Amazon.es',
          '0',//expect 0
        ),

        //case where arraystack is empty array
        array(
          array(
          ),
          'Amazon.com',
          '0',//expect 0
        ),

        //case where mpk is blank
        array(
          array(
            array('mkp'=>'Amazon.co.uk','sum'=>'100'),
            array('mkp'=>'Amazon.com','sum'=>'-99'),
            array('mkp'=>'Amazon.fr','sum'=>'-21'),
            array('mkp'=>'Amazon.de','sum'=>'443'),
          ),
          '',
          '0',//expect 0
        ),

        //case where mpk is null
        array(
          array(
            array('mkp'=>'Amazon.co.uk','sum'=>'100'),
            array('mkp'=>'Amazon.com','sum'=>'-99'),
            array('mkp'=>'Amazon.fr','sum'=>'-21'),
            array('mkp'=>'Amazon.de','sum'=>'443'),
          ),
          null,
          '0',//expect 0
        ),

      );
    }
    /**
     *@group pnlcontroller
     *@dataProvider provider_test_getSumFromArrayStack
     */
    public function test_getSumFromArrayStack($arraystack,$mkpname,$expected)
    {
      $pnlcontroller = new PnLController;
      $result = $pnlcontroller->getSumFromArrayStack($arraystack,$mkpname);

      $this->assertEquals($result,$expected);

    }


    public function provider_test_makeRow_event(){
      return array(
        array(
          array('event'=>'SampleEvent'),
          array(
            array('iso_3166_2'=>'UK'),
            array('iso_3166_2'=>'FR'),
            array('iso_3166_2'=>'US'),
            array('iso_3166_2'=>'CA'),
          ),
          true,
        ),

        array(
          array('event'=>'another'),
          array(
            array('iso_3166_2'=>'UK'),
          ),
          false
        ),

        array(
          array(),
          array(
          ),
          true,
        ),

      );
    }
    /**

     *@group pnlcontroller
     *@dataProvider provider_test_makeRow_event
     */
    public function test_makeRow_event($q,$mkplist,$hasDetails)
    {
      $pnlcontroller = new PnLController;


      $result = $pnlcontroller->makeRow_event($q,$mkplist,array(),$hasDetails);

      $i0='';
      if($hasDetails){
        $i0='<span class="row-details row-details-close"></span>';
      }

      $this->assertEquals($i0,$result[0]);
      $this->assertEquals('<b>'.(isset($q['event']) ? $q['event'] : '').'</b>',$result[1]);
      $this->assertCount((6+(count($mkplist))),$result);
    }



    public function provider_test_makeRow_financialeventrawsgroupbymkp(){
      return array(
        array(
          array('event'=>'SampleEvent'),
          array(
            array('iso_3166_2'=>'UK'),
            array('iso_3166_2'=>'FR'),
            array('iso_3166_2'=>'US'),
            array('iso_3166_2'=>'CA'),
          ),
          true,
        ),

        array(
          array('event'=>'another'),
          array(
            array('iso_3166_2'=>'UK'),
          ),
          false
        ),

        array(
          array(),
          array(
          ),
          true,
        ),

      );
    }



    public function provider_test_manageRowDataByiso_3166_2(){
      return array(
        array(
          array('iso_3166_2'=>'GB','currency_code'=>'GBP'),
          'gbp',
        ),
        array(
          array('iso_3166_2'=>'FR','currency_code'=>'EUR'),
          'gbp',
        ),

        array(
          array('iso_3166_2'=>'DE','currency_code'=>'EUR'),
          'gbp',
        ),

        array(
          array('iso_3166_2'=>'IT','currency_code'=>'EUR'),
          'gbp',
        ),

        array(
          array('iso_3166_2'=>'ES','currency_code'=>'EUR'),
          'gbp',
        ),

        array(
          array('iso_3166_2'=>'US','currency_code'=>'USD'),
          'gbp',
        ),

        array(
          array('iso_3166_2'=>'CA','currency_code'=>'CAD'),
          'gbp',
        ),
      );
    }

    /**
     *@group pnlcontroller
      *@dataProvider provider_test_manageRowDataByiso_3166_2
     */
    public function test_manageRowDataByiso_3166_2($mkplist,$prefcurr)//$q,$mkplist,$hasDetails
    {
        $arraystack = $this->_getMockarraystackByMKP();

        $pnlcontroller = new PnLController;
        $result = $pnlcontroller->manageRowDataByiso_3166_2($mkplist,$arraystack,$prefcurr);

        $marketplacename = $pnlcontroller->iso_3166_2_to_marketplacename($mkplist['iso_3166_2']);
        $v = $pnlcontroller->getSumFromArrayStack($arraystack,$marketplacename);
        $convertedv = currency($v,$mkplist['currency_code'],$prefcurr,false);

        $this->assertEquals($convertedv,$result);

    }

    /**
     *@group pnlcontroller
      *@dataProvider provider_test_manageRowDataByiso_3166_2
     */
    public function test_manageRowDataBycurrency_code($mkplist,$prefcurr)//$q,$mkplist,$hasDetails
    {
        $arraystack = $this->_getMockarraystackSumByCurrency();

        $pnlcontroller = new PnLController;
        $result = $pnlcontroller->manageRowDataBycurrency_code($mkplist,$arraystack,$prefcurr);

        $v = $pnlcontroller->getSumFromArrayStack($arraystack,$mkplist['currency_code']);
        $convertedv = currency($v,$mkplist['currency_code'],$prefcurr,false);

        $this->assertEquals($convertedv,$result);

    }




    public function provider_test_convertdatetoYmd(){
      return array(
        array(
          '01/01/2017',
          '02/01/2017',
          '2017-01-01',
          '2017-02-01',
        ),
        array(
          '05/01/2016',
          '12/21/2016',
          '2016-05-01',
          '2016-12-21',
        ),

        array(
          '',//case none
          '',//case none
          date('Y-m-d',strtotime('-30 days')),
          date('Y-m-d'),
        ),


      );
    }
    /**
     *@group pnlcontroller
      *@dataProvider provider_test_convertdatetoYmd
     */
     //might fail due to execution time between that took between different seconds.. sample
    // --- Expected
    // +++ Actual
    // @@ @@
    // -'2017-05-24 14:41:47'
    // +'2017-05-24 14:41:48'
    public function test_convertdatetoYmd($d1,$d2,$expectedd1,$expectedd2)//$q,$mkplist,$hasDetails
    {
        $pnlcontroller = new PnLController;
        $result = $pnlcontroller->convertdatetoYmd($d1,$d2);

        $this->assertEquals($expectedd1,$result[0]);
        $this->assertEquals($expectedd2,$result[1]);

    }


    /**
     *@group pnlcontroller
     */
    public function test_makeRow_financialeventrawsgroupbymkp()//$q,$mkplist,$hasDetails
    {
        $arraystack = $this->_getMockarraystackByMKP();
        $mkplist=$this->_getMockMKPList();

        $prefcurr='gbp';
        $q=array('price_type'=>'samplepricetype');
        $hasDetails=false;

        $m = $this->getMockBuilder(FinancialEventsReport::class)
                     ->setMethods(['getFinancialEventsRawsSumByMKP'])
                     ->getMock();
        $m->method('getFinancialEventsRawsSumByMKP')
        ->willReturn($arraystack);

        $pnlcontroller = new PnLController;
        $pnlcontroller->setFinancialEventsReportModel($m);
        $result = $pnlcontroller->makeRow_financialeventrawsgroupbymkp($q,$mkplist,$prefcurr,$hasDetails);

        $i0='';
        if($hasDetails){
          $i0='<span class="row-details row-details-close"></span>';
        }
        $this->assertEquals($i0,$result[0]);
        $this->assertEquals((isset($q['price_type']) ? $q['price_type'] : ''),$result[2]);
        $this->assertCount((6+(count($mkplist))),$result);

    }

    /**
     *@group pnlcontroller
     */
    public function test_makeRow_financialeventrawsgroupbycurrency()//$q,$mkplist,$hasDetails
    {
        $arraystack = $this->_getMockarraystackSumByCurrency();
        $mkplist=$this->_getMockMKPList();

        $prefcurr='gbp';
        $q=array('price_type'=>'samplepricetype');
        $hasDetails=false;

        $m = $this->getMockBuilder(FinancialEventsReport::class)
                     ->setMethods(['getFinancialEventsRawsSumByMKP'])
                     ->getMock();
        $m->method('getFinancialEventsRawsSumByMKP')
        ->willReturn($arraystack);

        $pnlcontroller = new PnLController;
        $pnlcontroller->setFinancialEventsReportModel($m);
        $result = $pnlcontroller->makeRow_financialeventrawsgroupbycurrency($q,$mkplist,$prefcurr,$hasDetails);

        $i0='';
        if($hasDetails){
          $i0='<span class="row-details row-details-close"></span>';
        }
        $this->assertEquals($i0,$result[0]);
        $this->assertEquals((isset($q['price_type']) ? $q['price_type'] : ''),$result[2]);
        $this->assertCount((6+(count($mkplist))),$result);

    }

    /**
     *@group pnlcontroller
     */
    public function test_makeRow_financialeventdebtrecoverygroupbycurrency()//$q,$mkplist,$hasDetails
    {
        $arraystack = $this->_getMockarraystackSumByCurrency();
        $mkplist=$this->_getMockMKPList();

        $prefcurr='gbp';
        $q=array('debtrecoverytype'=>'samplepricetype');
        $hasDetails=false;

        $m = $this->getMockBuilder(FinancialEventsReport::class)
                     ->setMethods(['getFinancialEventDebtRecoveryByPostedDateRange'])
                     ->getMock();
        $m->method('getFinancialEventDebtRecoveryByPostedDateRange')
        ->willReturn($arraystack);

        $pnlcontroller = new PnLController;
        $pnlcontroller->setFinancialEventsReportModel($m);
        $result = $pnlcontroller->makeRow_financialeventdebtrecoverygroupbycurrency($q,$mkplist,$prefcurr,$hasDetails);

        $i0='';
        if($hasDetails){
          $i0='<span class="row-details row-details-close"></span>';
        }
        $this->assertEquals($i0,$result[0]);
        $this->assertEquals((isset($q['debtrecoverytype']) ? $q['debtrecoverytype'] : ''),$result[2]);
        $this->assertCount((6+(count($mkplist))),$result);

    }

    /**
     *@group pnlcontroller
     */
      public function test_makeRow_financialeventloanservicinggroupbycurrency()//$q,$mkplist,$hasDetails
    {
        $arraystack = $this->_getMockarraystackSumByCurrency();
        $mkplist=$this->_getMockMKPList();

        $prefcurr='gbp';
        $q=array(
        'sourcebusinesseventtype'=>'samplepricetype',
        'date_from'=>'2017-01-01',
        'date_to'=>'2017-02-01',
        );
        $hasDetails=false;

        $m = $this->getMockBuilder(FinancialEventsReport::class)
                     ->setMethods(['getFinancialEventLoadServicingByPostedDateRange'])
                     ->getMock();
        $m->method('getFinancialEventLoadServicingByPostedDateRange')
        ->willReturn($arraystack);

        $pnlcontroller = new PnLController;
        $pnlcontroller->setFinancialEventsReportModel($m);
        $result = $pnlcontroller->makeRow_financialeventloanservicinggroupbycurrency($q,$mkplist,$prefcurr,$hasDetails);

        $i0='';
        if($hasDetails){
          $i0='<span class="row-details row-details-close"></span>';
        }
        $this->assertEquals($i0,$result[0]);
        $this->assertEquals((isset($q['sourcebusinesseventtype']) ? $q['sourcebusinesseventtype'] : ''),$result[2]);
        $this->assertCount((6+(count($mkplist))),$result);

    }

    /**
     *@group pnlcontroller
     */
    public function test_makeRow_financialeventretrochargegroupbymkp()//$q,$mkplist,$hasDetails
    {
        $arraystack = $this->_getMockarraystackByMKP();
        $mkplist=$this->_getMockMKPList();

        $prefcurr='gbp';
        $q=array('retrochargeeventtype'=>'samplepricetype');
        $hasDetails=false;

        $m = $this->getMockBuilder(FinancialEventsReport::class)
                     ->setMethods(['getFinancialEventRetrochargeByPostedDateRange'])
                     ->getMock();
        $m->method('getFinancialEventRetrochargeByPostedDateRange')
        ->willReturn($arraystack);

        $pnlcontroller = new PnLController;
        $pnlcontroller->setFinancialEventsReportModel($m);
        $result = $pnlcontroller->makeRow_financialeventretrochargegroupbymkp($q,$mkplist,$prefcurr,$hasDetails);

        $i0='';
        if($hasDetails){
          $i0='<span class="row-details row-details-close"></span>';
        }
        $this->assertEquals($i0,$result[0]);
        $this->assertEquals((isset($q['retrochargeeventtype']) ? $q['retrochargeeventtype'] : ''),$result[2]);
        $this->assertCount((6+(count($mkplist))),$result);

    }

    /**
     *@group pnlcontroller
     */
    public function test_makeRow_feetype()//$q,$mkplist,$hasDetails
    {
        $arraystackmkp = $this->_getMockarraystackByMKP();
        $arraystackcurr = $this->_getMockarraystackSumByCurrency();
        $mkplist=$this->_getMockMKPList();

        $prefcurr='gbp';
        $q=array('feetype'=>'samplepricetype','date_from'=>'2017-01-01','date_to'=>'2017-02-01');
        $hasDetails=false;

        $m = $this->getMockBuilder(FinancialEventsReport::class)
                     ->setMethods(['getFinancialEventSAFETReimbursementItemListByPostedDateRange','getFinancialEventRentalTransactionChargeListByPostedDateRange','getFinancialEventsRawsSumByMKP'])
                     ->getMock();
        $m->method('getFinancialEventSAFETReimbursementItemListByPostedDateRange')->willReturn($arraystackcurr);
        $m->method('getFinancialEventRentalTransactionChargeListByPostedDateRange')->willReturn($arraystackcurr);
        $m->method('getFinancialEventsRawsSumByMKP')->willReturn($arraystackmkp);



        $pnlcontroller = new PnLController;
        $pnlcontroller->setFinancialEventsReportModel($m);
        $result = $pnlcontroller->makeRow_feetype($q,$mkplist,$prefcurr,$hasDetails);

        $i0='';
        if($hasDetails){
          $i0='<span class="row-details row-details-close"></span>';
        }
        $this->assertEquals($i0,$result[0]);
        $this->assertEquals((isset($q['feetype']) ? $q['feetype'] : ''),$result[2]);
        $this->assertCount((6+(count($mkplist))),$result);
    }

    /**
     *@group pnlcontroller
     */
    public function test_makeRow_chargetype()//$q,$mkplist,$hasDetails
    {
        $arraystackmkp = $this->_getMockarraystackByMKP();
        $arraystackcurr = $this->_getMockarraystackSumByCurrency();
        $mkplist=$this->_getMockMKPList();

        $prefcurr='gbp';
        $q=array('chargetype'=>'samplepricetype','date_from'=>'2017-01-01','date_to'=>'2017-02-01');
        $hasDetails=false;

        $m = $this->getMockBuilder(FinancialEventsReport::class)
                     ->setMethods(['getFinancialEventSAFETReimbursementItemListByPostedDateRange','getFinancialEventRentalTransactionChargeListByPostedDateRange','getFinancialEventsRawsSumByMKP'])
                     ->getMock();
        $m->method('getFinancialEventSAFETReimbursementItemListByPostedDateRange')->willReturn($arraystackcurr);
        $m->method('getFinancialEventRentalTransactionChargeListByPostedDateRange')->willReturn($arraystackcurr);
        $m->method('getFinancialEventsRawsSumByMKP')->willReturn($arraystackmkp);



        $pnlcontroller = new PnLController;
        $pnlcontroller->setFinancialEventsReportModel($m);
        $result = $pnlcontroller->makeRow_chargetype($q,$mkplist,$prefcurr,$hasDetails);

        $i0='';
        if($hasDetails){
          $i0='<span class="row-details row-details-close"></span>';
        }
        $this->assertEquals($i0,$result[0]);
        $this->assertEquals((isset($q['chargetype']) ? $q['chargetype'] : ''),$result[2]);
        $this->assertCount((6+(count($mkplist))),$result);

    }


    private function _getMockarraystackByMKP(){
      $r = array(
        array(
          "sum" => -153.39,
          "g" => "Amazon.ca"
        ),
        array(
          "sum" => -475.17,
          "g" => "Amazon.co.uk"
        ),
        array(
          "sum" => -554.89,
          "g" => "Amazon.com"
        ),
        array(
          "sum" => -252.34,
          "g" => "Amazon.de"
        ),
        array(
          "sum" => -235.44,
          "g" => "Amazon.es"
        ),
         array(
          "sum" => -256.75,
          "g" => "Amazon.fr"
        ),
        array(
          "sum" => -229.57,
          "g" => "Amazon.it"
        ),
      );
      return $r;
    }

    private function _getMockarraystackSumByCurrency(){
      $r = array(
        array(
          "sum" => -153.39,
          "g" => "CAD"
        ),
        array(
          "sum" => -475.17,
          "g" => "GBP"
        ),
        array(
          "sum" => -554.89,
          "g" => "USD"
        ),
        array(
          "sum" => -252.34,
          "g" => "EUR"
        ),
      );
      return $r;
    }

    private function _getMockMKPList(){
      $mkplist=array(
        array('iso_3166_2'=>'GB','currency_code'=>'GBP'),
        array('iso_3166_2'=>'FR','currency_code'=>'EUR'),
        array('iso_3166_2'=>'DE','currency_code'=>'EUR'),
        array('iso_3166_2'=>'IT','currency_code'=>'EUR'),
        array('iso_3166_2'=>'ES','currency_code'=>'EUR'),
        array('iso_3166_2'=>'US','currency_code'=>'USD'),
        array('iso_3166_2'=>'CA','currency_code'=>'CAD'),
      );
      return $mkplist;
    }


    public function provider_test_marketplacename_to_iso_3166_2(){
      return array(
        array('Amazon.co.uk', 'GB'),
        array('Amazon.fr', 'FR'),
        array('Amazon.de', 'DE'),
        array('Amazon.it', 'IT'),
        array('Amazon.es', 'ES'),
        array('Amazon.com', 'US'),
        array('Amazon.ca', 'CA'),
        array('SOMETHINGDIFF','GB'),
        array(null, 'GB'),
        array('','GB'),
      );
    }

    /**
     *@group pltest
     *@dataProvider provider_test_marketplacename_to_iso_3166_2
     */
    public function test_marketplacename_to_iso_3166_2($mkp,$expected)
    {
      $pnlcontroller = new PnLController;
      $result = $pnlcontroller->marketplacename_to_iso_3166_2($mkp);
      $this->assertEquals($result,$expected);
    }

    public function provider_test_getPreferedCurrencySymbol(){
      return array(
        array('GBP', '£'),
        array('EUR', '€'),
        array('USD', '$'),
        array('CAD', '$')
      );
    }

    /**
     *@group pltest
     *@dataProvider provider_test_getPreferedCurrencySymbol
     */
    public function test_getPreferedCurrencySymbol($currency, $expected){
      $pnl = new PnLController;
      $result = $pnl->getPreferedCurrencySymbol($currency);
      $this->assertEquals($result,$expected);
    }

    public function provider_test_currency_to_iso_3166_2(){
      return array(
        array('GBP', 'GB', array()),
        array('USD', 'US', array()),
        array('CAD', 'CA', array()),
        array(
          'EUR',
          'FR',//expect DE
          array(
          'DE'=>array('Principal'=>0),
          'FR'=>array('Principal'=>1),
          'ES'=>array('Principal'=>0),
          'IT'=>array('Principal'=>0),
          )
        ),
    array(
          'EUR',
          'DE',//expect DE
          array(
          'DE'=>array('Principal'=>1),
          'FR'=>array('Principal'=>0),
          'ES'=>array('Principal'=>0),
          'IT'=>array('Principal'=>0),
          )
        ),
    array(
          'EUR',
          'ES',//expect DE
          array(
          'DE'=>array('Principal'=>0),
          'FR'=>array('Principal'=>0),
          'ES'=>array('Principal'=>1),
          'IT'=>array('Principal'=>0),
          )
        ),
    array(
          'EUR',
          'IT',//expect DE
          array(
          'DE'=>array('Principal'=>0),
          'FR'=>array('Principal'=>0),
          'ES'=>array('Principal'=>0),
          'IT'=>array('Principal'=>1),
        )
        ),
      );
    }

    public function provider_test_calcTableRevenue_from_loan(){
      $pnl_types = [
        'Principal'=>0,
        'LoanAdvance'=>0
      ];
      return array(
        array(
          array(
            'CA'=>$pnl_types,
            'US'=>$pnl_types,
            'DE'=>$pnl_types,
            'GB'=>$pnl_types,
            'FR'=>$pnl_types,
            'IT'=>$pnl_types,
            'ES'=>$pnl_types,
            '2017-05-05'=>0
          ),
          array(
            (object) array('currency'=>'USD', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 10),
            (object) array('currency'=>'CAD', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 10),
            (object) array('currency'=>'GBP', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 10),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 10),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 10),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 10),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 10),
          ),
          'USD',
          array(
            7.40,
            10,
            44.76,
            12.96,
            0,
            0,
            0,
            '2017-05-05'=>75.13
          )
        ),
        array(
          array(
            'CA'=>$pnl_types,
            'US'=>$pnl_types,
            'DE'=>$pnl_types,
            'GB'=>$pnl_types,
            'FR'=>$pnl_types,
            'IT'=>$pnl_types,
            'ES'=>$pnl_types,
            '2017-05-05'=>0
          ),
          array(
            (object) array('currency'=>'USD', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 1),
            (object) array('currency'=>'CAD', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 1),
            (object) array('currency'=>'GBP', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 1),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 1),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 1),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 1),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 1),
          ),
          'USD',
          array(
            .74,
            1,
            4.48,
            1.30,
            0,
            0,
            0,
            '2017-05-05'=>7.51
          )
        ),
        array(
          array(
            'CA'=>$pnl_types,
            'US'=>$pnl_types,
            'DE'=>$pnl_types,
            'GB'=>$pnl_types,
            'FR'=>$pnl_types,
            'IT'=>$pnl_types,
            'ES'=>$pnl_types,
            '2017-05-05'=>0
          ),
          array(
            (object) array('currency'=>'USD', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 2),
            (object) array('currency'=>'CAD', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 2),
            (object) array('currency'=>'GBP', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 2),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 2),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 2),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 2),
            (object) array('currency'=>'EUR', 'posted_date'=>'2017-05-05', 'sourcebusinesseventtype'=>'LoanAdvance', 'amount'=> 2),
          ),
          'USD',
          array(
            1.48,
            2,
            8.95,
            2.59,
            0,
            0,
            0,
            '2017-05-05'=>15.03
          )
          )
        );
      }


        /*
        * @group pltest
         */
        public function testrevenueTable()
        {
        	$pnl = new PnLController;
        	$u = new Auth;
        	Auth::shouldReceive('user')->andReturn((object) ['seller_id' => 1]);
        	$response = $this->call('POST', 'pnlRevTable', array(
    	        '_token' => csrf_token(), 'date_from'=>'2016-11-01', 'date_to'=>date('Y-m-d')
    	    ));
        	//$pnl->getRevenueTableData();
            //$this->assertTrue(true);
        }
        /**
        * @group pltest
         */
        public function testPrefCur(){
        	$pnl = new PnLController;
        	$u = new Auth;
        	Auth::shouldReceive('user')->andReturn((object) ['seller_id' => 1]);
        	$pnl->getPreferedCurrencyForThisSeller();
            //$this->assertTrue(true);
        }


        /**
        * @group pltest
         */
        public function test_getPreferedCurrencyForThisSeller()
        {
          $pnlcontroller = new PnLController;
          $u = new Auth;//use the Auth facade
          Auth::shouldReceive('user')->andReturn((object) ['seller_id' => 1]);//mock the  data inside the Auth
          $result = $pnlcontroller->getPreferedCurrencyForThisSeller();
          //$this->assertArrayHasKey('preferred_currency',$result);
          $this->assertContains(strtoupper($result),['GBP', 'USD', 'CAD', 'EUR']);
        }

        /**
         *@group pltest
         *@dataProvider provider_test_currency_to_iso_3166_2
         */
        public function test_currency_to_iso_3166_2($currency, $expected){
        	$stack['DE']['Principal'] = 0;
        	$stack['FR']['Principal'] = 0;
        	$stack['IT']['Principal'] = 0;
        	$stack['ES']['Principal'] = 0;
        	if($expected == "FR") $stack['FR']['Principal'] = 1;
        	if($expected == "DE") $stack['DE']['Principal'] = 1;
        	if($expected == "IT") $stack['IT']['Principal'] = 1;
        	if($expected == "ES") $stack['ES']['Principal'] = 1;
        	$pnl = new PnLController;
        	$result = $pnl->currency_to_iso_3166_2($currency,$stack);
        	$this->assertEquals($result,$expected);
        }


        /**
          * @group pltest
           *@dataProvider provider_test_calcTableRevenue_from_loan
           */
          public function test_calcTableRevenue_from_loan($stack, $hay, $preferred_currency, $expected){
          	$pnl = new PnLController;
          	$result = $pnl->calcTableRevenue_from_loan($stack, $hay, $preferred_currency);
          	$i=0;
          	foreach ($result as $key => $value) {
          		if(is_array($value)){
      	    		$this->assertEquals(round($value['LoanAdvance'],2), $expected[$i]);
      	    		$i++;
      	    	}
          	}
          }

          /**
          * @group pltest
           *@dataProvider provider_test_calcTableRevenue_from_loan
           */
          public function test_calcGraphRevenue_from_loan($stack, $hay, $preferred_currency, $expected){
          	$pnl = new PnLController;
          	$result = $pnl->calcGraphRevenue_from_loan($stack, $hay, $preferred_currency);
          	foreach ($result as $key => $value) {
          		if(!is_array($value)){
      	    		$this->assertEquals(round($value,2), $expected[$key]);
      	    	}
          	}
          }

          public function provider_test_calcTableRevenue_from_raw(){
            $pnl_types = [
          		'Principal'=>0,
          		'FBAInventoryReimbursement'=>0,
          		'PostageRefund'=>0,
          		'GiftWrap'=>0,
          		'ShippingCharge'=>0,
          		'ReturnShipping'=>0,
          		'FreeReplacementReturnShipping'=>0,
          		'Total'=>0,
          		'Adjustments'=>0,
          		'Others'=>0
          	];
            return array(
              array(
              	array(
      	        	'CA'=>$pnl_types,
      	        	'US'=>$pnl_types,
      	        	'DE'=>$pnl_types,
      	        	'GB'=>$pnl_types,
      	        	'FR'=>$pnl_types,
      	        	'IT'=>$pnl_types,
      	        	'ES'=>$pnl_types,
      	        	//date for graph
      	        	'2017-05-05'=>0,
              	),
              	array(
              		(object) array('currency'=>'USD', 'price_type'=>'Principal', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.com'),
              		(object) array('currency'=>'CAD', 'price_type'=>'Principal', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.ca'),
              		(object) array('currency'=>'GBP', 'price_type'=>'Principal', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.co.uk'),
              		(object) array('currency'=>'EUR', 'price_type'=>'Principal', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.fr'),
              		(object) array('currency'=>'EUR', 'price_type'=>'Principal', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.de'),
              		(object) array('currency'=>'EUR', 'price_type'=>'Principal', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.it'),
              		(object) array('currency'=>'EUR', 'price_type'=>'Principal', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.es'),
              		(object) array('currency'=>'USD', 'price_type'=>'FBAInventoryReimbursement', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.com'),
              		(object) array('currency'=>'CAD', 'price_type'=>'FBAInventoryReimbursement', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.ca'),
              		(object) array('currency'=>'GBP', 'price_type'=>'FBAInventoryReimbursement', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.co.uk'),
              		(object) array('currency'=>'EUR', 'price_type'=>'FBAInventoryReimbursement', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.fr'),
              		(object) array('currency'=>'EUR', 'price_type'=>'FBAInventoryReimbursement', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.de'),
              		(object) array('currency'=>'EUR', 'price_type'=>'FBAInventoryReimbursement', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.it'),
              		(object) array('currency'=>'EUR', 'price_type'=>'FBAInventoryReimbursement', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.es'),
              		(object) array('currency'=>'USD', 'price_type'=>'PostageRefund', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.com'),
              		(object) array('currency'=>'CAD', 'price_type'=>'PostageRefund', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.ca'),
              		(object) array('currency'=>'GBP', 'price_type'=>'PostageRefund', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.co.uk'),
              		(object) array('currency'=>'EUR', 'price_type'=>'PostageRefund', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.fr'),
              		(object) array('currency'=>'EUR', 'price_type'=>'PostageRefund', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.de'),
              		(object) array('currency'=>'EUR', 'price_type'=>'PostageRefund', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.it'),
              		(object) array('currency'=>'EUR', 'price_type'=>'PostageRefund', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.es'),
              		(object) array('currency'=>'USD', 'price_type'=>'GiftWrap', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.com'),
              		(object) array('currency'=>'CAD', 'price_type'=>'GiftWrap', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.ca'),
              		(object) array('currency'=>'GBP', 'price_type'=>'GiftWrap', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.co.uk'),
              		(object) array('currency'=>'EUR', 'price_type'=>'GiftWrap', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.fr'),
              		(object) array('currency'=>'EUR', 'price_type'=>'GiftWrap', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.de'),
              		(object) array('currency'=>'EUR', 'price_type'=>'GiftWrap', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.it'),
              		(object) array('currency'=>'EUR', 'price_type'=>'GiftWrap', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.es'),
              		(object) array('currency'=>'USD', 'price_type'=>'ShippingCharge', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.com'),
              		(object) array('currency'=>'CAD', 'price_type'=>'ShippingCharge', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.ca'),
              		(object) array('currency'=>'GBP', 'price_type'=>'ShippingCharge', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.co.uk'),
              		(object) array('currency'=>'EUR', 'price_type'=>'ShippingCharge', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.fr'),
              		(object) array('currency'=>'EUR', 'price_type'=>'ShippingCharge', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.de'),
              		(object) array('currency'=>'EUR', 'price_type'=>'ShippingCharge', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.it'),
              		(object) array('currency'=>'EUR', 'price_type'=>'ShippingCharge', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.es'),
              		(object) array('currency'=>'USD', 'price_type'=>'ReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.com'),
              		(object) array('currency'=>'CAD', 'price_type'=>'ReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.ca'),
              		(object) array('currency'=>'GBP', 'price_type'=>'ReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.co.uk'),
              		(object) array('currency'=>'EUR', 'price_type'=>'ReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.fr'),
              		(object) array('currency'=>'EUR', 'price_type'=>'ReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.de'),
              		(object) array('currency'=>'EUR', 'price_type'=>'ReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.it'),
              		(object) array('currency'=>'EUR', 'price_type'=>'ReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.es'),
              		(object) array('currency'=>'USD', 'price_type'=>'FreeReplacementReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.com'),
              		(object) array('currency'=>'CAD', 'price_type'=>'FreeReplacementReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.ca'),
              		(object) array('currency'=>'GBP', 'price_type'=>'FreeReplacementReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.co.uk'),
              		(object) array('currency'=>'EUR', 'price_type'=>'FreeReplacementReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.fr'),
              		(object) array('currency'=>'EUR', 'price_type'=>'FreeReplacementReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.de'),
              		(object) array('currency'=>'EUR', 'price_type'=>'FreeReplacementReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.it'),
              		(object) array('currency'=>'EUR', 'price_type'=>'FreeReplacementReturnShipping', 'price_amount'=> 10, 'posted_date'=>'2017-05-05', 'marketplace_name'=>'Amazon.es'),
              	),
              	'USD',
              	array(
              		'CA'=> array(7.40,7.40,7.40,7.40,7.40,7.40,7.40,51.82,14.81,29.61),
              		'US' => array(10,10,10,10,10,10,10,70,20,40),
              		'GB' => array(12.96,12.96,12.96,12.96,12.96,12.96,12.96,90.73, 25.92, 51.85),
              		'DE' => array(11.19,11.19,11.19,11.19,11.19,11.19,11.19,78.34, 22.38, 44.76),
              		'FR' => array(11.19,11.19,11.19,11.19,11.19,11.19,11.19,78.34, 22.38, 44.76),
              		'IT' => array(11.19,11.19,11.19,11.19,11.19,11.19,11.19,78.34, 22.38, 44.76),
              		'ES' => array(11.19,11.19,11.19,11.19,11.19,11.19,11.19,78.34, 22.38, 44.76),
              		'2017-05-05'=>525.90
              	)
              )
            );
          }

      	/**
          * @group pltest
           *@dataProvider provider_test_calcTableRevenue_from_raw/1
           */
          public function test_calcTableRevenue_from_raw($stack, $hay, $preferred_currency, $expected){
          	$pnl = new PnLController;
          	$result = $pnl->calcTableRevenue_from_raw($stack, $hay, $preferred_currency);
          	foreach ($result as $key => $value) {
          		$i=0;
          		if(is_array($value)){
      	    		foreach ($value as $key2 => $val) {
      	    			$this->assertEquals(round($val,2), $expected[$key][$i]);
      	    			$i++;
      	    		}
      	    	}
          	}
          }

      	/**
          * @group pltest
           *@dataProvider provider_test_calcTableRevenue_from_raw
           */
          public function test_calcGraphRevenue_from_raw($stack, $hay, $preferred_currency, $expected){
          	$pnl = new PnLController;
          	$result = $pnl->calcGraphRevenue_from_raw($stack, $hay, $preferred_currency);
          	//print_r($result);
          	foreach ($result as $key => $value) {
          		if(!is_array($value))
          			$this->assertEquals(round($result[$key],2), $expected[$key]);
          	}
          }

    	// /**
      //   * @group pltest
      //    */
      //   public function testrevenueTable()
      //   {
      //   	$pnl = new PnLController;
      //   	$u = new Auth;
      //   	Auth::shouldReceive('user')->andReturn((object) ['seller_id' => 1]);
      //   	$response = $this->call('POST', 'pnlRevTable', array(
    	//         '_token' => csrf_token(), 'date_from'=>'2016-11-01', 'date_to'=>date('Y-m-d')
    	//     ));
      //   	//$pnl->getRevenueTableData();
      //       //$this->assertTrue(true);
      //   }

      	/**
          * @group pltest
           */
          public function test_getRevenueGraphData()
          {
          	$pnl = new PnLController;
          	$u = new Auth;
          	Auth::shouldReceive('user')->andReturn((object) ['seller_id' => 1]);
          	$response = $this->call('POST', 'pnlRevGraph', array(
      	        '_token' => csrf_token(), 'date_from'=>'2016-11-01', 'date_to'=>date('Y-m-d')
      	    ));
          	//$pnl->getRevenueTableData();
              //$this->assertTrue(true);
          }





}
