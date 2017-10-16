<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\MWSCustomClasses\MWSFetchReportClass;

class MWSFetchReportClassTest extends TestCase
{
    /**
    *@group mwsfrc
    *@dataProvider provider_test_checkForNewColumn
    */
    public function test_checkForNewColumn($thistable,$reportsampledata,$expected)
    {
      $mwsfrc = new MWSFetchReportClass;
      $r = $mwsfrc->checkForNewColumn($thistable,$reportsampledata[0]);
      $this->assertArraySubset($r,$expected);
    }

    public function provider_test_checkForNewColumn(){
      return array(
        array(
          'products',
          array(
            array('id'=>'3','samplecol1'=>'samplevalue1','price'=>'10.5','order_id'=>'123-456-789'),
          ),
          array('samplecol1','order_id')
        ),

        array(
          'returns_reports',
          array(
            array('id'=>'3','samplecol1'=>'samplevalue1','price'=>'10.5','order_id'=>'123-456-789'),
          ),
          array('samplecol1','price')
        ),

        array(
          '',
          array(
            array('id'=>'3','samplecol1'=>'samplevalue1','price'=>'10.5','order_id'=>'123-456-789'),
          ),
          array()
        ),

        array(
          'returns_reports',
          array(
            array(),
          ),
          array()
        ),

      );
    }

}
