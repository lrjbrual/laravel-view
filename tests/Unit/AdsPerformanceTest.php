<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers\Trendle\AdsPerformanceController;
use Auth;
use App\User;
use Mockery;
use App;

class AdsPerformanceTest extends TestCase
{
	/**
	* @group adstest
	*/
	public function test_getAdData()
    {	
        $user =  User::where('email','=', 'admin@ls.com')->first();
        $this->actingAs($user);
     	$response = $this->call('POST',"getAdData" ,array('_token'=>csrf_token()));
     	$response->assertStatus(200);
    }
	/**
	* @group adstest
	*/
	public function test_getAdFilters()
	{
		$user =  User::where('email','=', 'admin@ls.com')->first();
		$this->actingAs($user);
     	$response = $this->call('POST','getAdFilters',array('_token'=>csrf_token()));
     	$response->assertStatus(200);
	}
	/**
	* @group adstest
	*/
	public function test_getAdFilterData()
	{
		$user =  User::where('email','=', 'admin@ls.com')->first();
		$this->actingAs($user);
     	$response = $this->call('POST','getAdFilterData',array('_token'=>csrf_token(), 'id'=>1));
     	$response->assertStatus(200);
	}

	public function provider_test_addAdFilter(){
      return array(
        array(
        	array(
        	'filter_name' => "test name",
        	'filter_ctr' => "0-6",
        	)
        ),
        array(
        	array(
        	'filter_name' => "test name 2",
        	'filter_ctr' => "0-7",
        	'country' => 'ca-us-uk',
        	)
        ),

      );
    }
	/**
	* @group adstest
    * @dataProvider provider_test_addAdFilter
	*/
	public function test_addAdFilter($data)
	{
		$data['_token'] = csrf_token();
		$user =  User::where('email','=', 'admin@ls.com')->first();
		$this->actingAs($user);
     	$response = $this->call('POST','addAdFilter',$data);
     	$response->assertStatus(200);
	}

	public function provider_test_updateAdFilter(){
      return array(
        array(
            array(
            'id' => 1,
            'filter_name' => "test name",
            'filter_ctr' => "0-6",
            )
        ),
        array(
            array(
            'id' => 1,
            'filter_name' => "test name 2",
            'filter_ctr' => "0-7",
            'country' => 'ca-us-uk',
            )
        ),

      );
    }
    /**
    * @group adstest
    * @dataProvider provider_test_updateAdFilter
    */
    public function test_updateAdFilter($data)
    {
        $data['_token'] = csrf_token();
        $user =  User::where('email','=', 'admin@ls.com')->first();
        $this->actingAs($user);
        $response = $this->call('POST','updateAdFilter',$data);
        $response->assertStatus(200);
    }

    public function provider_test_updateAdComment(){
      return array(
        array(
            array(
            'id' => 1,
            'comment' => "test name"
            )
        ),
        array(
            array(
            'id' => 1,
            'comment' => null
            )
        ),
        array(
            array(
            'id' => 1,
            'comment' => ''
            )
        ),

      );
    }
    /**
    * @group adstest
    * @dataProvider provider_test_updateAdComment
    */
    public function test_updateAdComment($data)
    {
        $data['_token'] = csrf_token();
        $user =  User::where('email','=', 'admin@ls.com')->first();
        $this->actingAs($user);
        $response = $this->call('POST','updateAdComment',$data);
        $response->assertStatus(200);
    }

    public function provider_test_getStartEndDate(){
        return array(
            array('01/06/2017', '08/06/2017', array('date_start'=>'2017-06-01', 'date_end'=>'2017-06-08')),
            array(null, null, array('date_start'=>date('Y-m-d', strtotime('-30 days')), 'date_end'=>date('Y-m-d'))),
            array(null, '01/06/2017', array('date_start'=>date('Y-m-d', strtotime('-30 days2017-06-01')), 'date_end'=>'2017-06-01'))
        );
    }
    /**
    * @group adstest
    * @dataProvider provider_test_getStartEndDate
    */
    public function test_getStartEndDate($s, $e, $ex){
        $ads = new AdsPerformanceController;
        $res = $ads->getStartEndDate($s, $e);
        $this->assertEquals($res['date_start'],$ex['date_start']); //start date assertion
        $this->assertEquals($res['date_end'],$ex['date_end']); //end date assertion
    }

    public function provider_test_calculateCR(){
        return array(
            array(
                array(6,40,8,20,6,10),
                90,
                array(6.67, 44.44, 8.89, 22.22, 6.67, 11.11)
            ),
            array(
                array(9,40,11,20,9,10),
                99,
                array(9.09, 40.40, 11.11, 20.20, 9.09, 10.10)
            ),
            array(
                array(6,15,4,8,6,null),
                42,
                array(14.29, 35.71, 9.52, 19.05, 14.29, 0)
            )
        );
    }
    /**
    * @group adstest
    * @dataProvider provider_test_calculateCR
    */
    public function test_calculateCR($data=array(), $clicks, $ex){
        $ads = new AdsPerformanceController;
        $res = $ads->calculateCR($data, $clicks);
        for($x=0; $x<count($res); $x++) $this->assertEquals($res[$x], $ex[$x]);
    }

    public function provider_test_setGraphDates(){
        return array(
            array(
                '01/06/2017', 
                '08/06/2017', 
                array('2017-06-01'=>0, 
                    '2017-06-02'=>0,
                    '2017-06-03'=>0,
                    '2017-06-04'=>0,
                    '2017-06-05'=>0,
                    '2017-06-06'=>0,
                    '2017-06-07'=>0,
                    '2017-06-08'=>0,)
            ),
            array(
                null, 
                null, 
                array(
                    date('Y-m-d', strtotime('-30 days'))=>0,
                    date('Y-m-d', strtotime('-29 days'))=>0,
                    date('Y-m-d', strtotime('-28 days'))=>0,
                    date('Y-m-d', strtotime('-27 days'))=>0,
                    date('Y-m-d', strtotime('-26 days'))=>0,
                    date('Y-m-d', strtotime('-25 days'))=>0,
                    date('Y-m-d', strtotime('-24 days'))=>0,
                    date('Y-m-d', strtotime('-23 days'))=>0,
                    date('Y-m-d', strtotime('-22 days'))=>0,
                    date('Y-m-d', strtotime('-21 days'))=>0,
                    date('Y-m-d', strtotime('-20 days'))=>0,
                    date('Y-m-d', strtotime('-19 days'))=>0,
                    date('Y-m-d', strtotime('-18 days'))=>0,
                    date('Y-m-d', strtotime('-17 days'))=>0,
                    date('Y-m-d', strtotime('-16 days'))=>0,
                    date('Y-m-d', strtotime('-15 days'))=>0,
                    date('Y-m-d', strtotime('-14 days'))=>0,
                    date('Y-m-d', strtotime('-13 days'))=>0,
                    date('Y-m-d', strtotime('-12 days'))=>0,
                    date('Y-m-d', strtotime('-11 days'))=>0,
                    date('Y-m-d', strtotime('-10 days'))=>0,
                    date('Y-m-d', strtotime('-9 days'))=>0,
                    date('Y-m-d', strtotime('-8 days'))=>0,
                    date('Y-m-d', strtotime('-7 days'))=>0,
                    date('Y-m-d', strtotime('-6 days'))=>0,
                    date('Y-m-d', strtotime('-5 days'))=>0,
                    date('Y-m-d', strtotime('-4 days'))=>0,
                    date('Y-m-d', strtotime('-3 days'))=>0,
                    date('Y-m-d', strtotime('-2 days'))=>0,
                    date('Y-m-d', strtotime('-1 days'))=>0,
                    date('Y-m-d')=>0,
                    )
            ),
        );
    }
    /**
    * @group adstest
    * @dataProvider provider_test_setGraphDates
    */
    public function test_setGraphDates($s, $e, $ex){
        $ads = new AdsPerformanceController;
        $dates = $ads->getStartEndDate($s, $e);
        $res = $ads->setGraphDates($dates);
        foreach ($res as $key => $value) {
            $this->assertArrayHasKey($key, $ex);
            $this->assertEquals($value, $ex[$key]);
        }
    }

    public function provider_test_setupGraphData(){
        return array(
            array(
                array('date_start'=>'2017-06-01', 'date_end'=>'2017-06-08'),
                (object) array(
                    (object) array(
                        'posted_date'=>'2017-06-01',
                        'clicks' => 5,
                        'total_spend' => 5,
                        'acos' => 5,
                        'average_cpc' => 5,
                        'impressions' => 5,
                        'ctr' => 5,
                        'other_sku_units_product_sales_within_1_week_of_click' => 5,
                        'same_sku_units_product_sales_within_1_week_of_click' => 0
                    ),
                    (object) array(
                        'posted_date'=>'2017-06-02',
                        'clicks' => 5,
                        'total_spend' => 5,
                        'acos' => 5,
                        'average_cpc' => 5,
                        'impressions' => 5,
                        'ctr' => 5,
                        'other_sku_units_product_sales_within_1_week_of_click' => 5,
                        'same_sku_units_product_sales_within_1_week_of_click' => 0
                    ),
                    (object) array(
                        'posted_date'=>'2017-06-04',
                        'clicks' => 5,
                        'total_spend' => 5,
                        'acos' => 5,
                        'average_cpc' => 5,
                        'impressions' => 5,
                        'ctr' => 5,
                        'other_sku_units_product_sales_within_1_week_of_click' => 5,
                        'same_sku_units_product_sales_within_1_week_of_click' => 0
                    ),
                    (object) array(
                        'posted_date'=>'2017-06-08',
                        'clicks' => 5,
                        'total_spend' => 5,
                        'acos' => 5,
                        'average_cpc' => 5,
                        'impressions' => 5,
                        'ctr' => 5,
                        'other_sku_units_product_sales_within_1_week_of_click' => 5,
                        'same_sku_units_product_sales_within_1_week_of_click' => 0
                    ),
                ),
                array(
                    'clicks_t' => 20,
                    'total_spend_t' => 20,
                    'acos_t' => 20,
                    'average_cpc_t' => 20,
                    'impressions_t' => 20,
                    'ctr_t' => 20,
                    'cr_t' => 100,
                    'revenue_t' => 20
                )
            ),
            array(
                array('date_start'=>'2017-06-05', 'date_end'=>'2017-06-08'),
                (object) array(
                    (object) array(
                        'posted_date'=>'2017-06-05',
                        'clicks' => 10,
                        'total_spend' => 10,
                        'acos' => 10,
                        'average_cpc' => 10,
                        'impressions' => 10,
                        'ctr' => 10,
                        'other_sku_units_product_sales_within_1_week_of_click' => 0,
                        'same_sku_units_product_sales_within_1_week_of_click' => 10
                    ),
                    (object) array(
                        'posted_date'=>'2017-06-06',
                        'clicks' => 10,
                        'total_spend' => 10,
                        'acos' => 10,
                        'average_cpc' => 10,
                        'impressions' => 10,
                        'ctr' => 10,
                        'other_sku_units_product_sales_within_1_week_of_click' => 0,
                        'same_sku_units_product_sales_within_1_week_of_click' => 10
                    ),
                    (object) array(
                        'posted_date'=>'2017-06-07',
                        'clicks' => 10,
                        'total_spend' => 10,
                        'acos' => 10,
                        'average_cpc' => 10,
                        'impressions' => 10,
                        'ctr' => 10,
                        'other_sku_units_product_sales_within_1_week_of_click' => 0,
                        'same_sku_units_product_sales_within_1_week_of_click' => 10
                    ),
                    (object) array(
                        'posted_date'=>'2017-06-08',
                        'clicks' => 10,
                        'total_spend' => 10,
                        'acos' => 10,
                        'average_cpc' => 10,
                        'impressions' => 10,
                        'ctr' => 10,
                        'other_sku_units_product_sales_within_1_week_of_click' => 0,
                        'same_sku_units_product_sales_within_1_week_of_click' => 10
                    ),
                ),
                array(
                    'clicks_t' => 40,
                    'total_spend_t' => 40,
                    'acos_t' => 40,
                    'average_cpc_t' => 40,
                    'impressions_t' => 40,
                    'ctr_t' => 40,
                    'cr_t' => 100,
                    'revenue_t' => 40
                )
            )
        );
    }
    /**
    * @group adstest
    * @dataProvider provider_test_setupGraphData
    */
	public function test_setupGraphData($dates, $raw_data, $ex){
        $ads = new AdsPerformanceController;
        $raw = (object) $raw_data;
        $res = $ads->setupGraphData($dates, $raw);
        $this->assertEquals($res['total_spend_t'], $ex['total_spend_t']);
        $this->assertEquals($res['clicks_t'], $ex['clicks_t']);
        $this->assertEquals($res['acos_t'], $ex['acos_t']);
        $this->assertEquals($res['average_cpc_t'], $ex['average_cpc_t']);
        $this->assertEquals($res['impressions_t'], $ex['impressions_t']);
        $this->assertEquals($res['ctr_t'], $ex['ctr_t']);
        $this->assertEquals($res['cr_t'], $ex['cr_t']);
        $this->assertEquals($res['revenue_t'], $ex['revenue_t']);
    }

    public function test_getAdGraph(){
        $user =  User::where('email','=', 'admin@ls.com')->first();
        $this->actingAs($user);
        $response = $this->call('POST','getAdGraph',array('_token'=>csrf_token()));
        $response->assertStatus(200);
    }
}
