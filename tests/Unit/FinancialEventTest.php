<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers\Crons\UpdateFinancialEventsController;
use App\UniversalModel;

class FinancialEventTest extends TestCase
{

    public function provider_test_insert_DebtRecovery()
    {
      return array(
        array(''),
        array(
          array(
            //1st record
            array(
              'DebtRecoveryType'=>'sampletype',
              'RecoveryAmount'=>array(
                'CurrencyCode'=>'GBP',
                'Amount'=>'10.99'
              ),
              'OverPaymentCredit'=>array(
                'CurrencyCode'=>'GBP',
                'Amount'=>'20.99'
              ),
              'DebtRecoveryItemList'=>array(
                //1strecordofDebtRecoveryItemList
                array(
                  'RecoveryAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'10.99'),
                  'OriginalAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'10.99'),
                  'GroupBeginDate'=>'2017-02-27T10:10:36Z',
                  'GroupEndDate'=>'2017-02-27T10:10:36Z',
                ),

                //2ndrecordofDebtRecoveryItemList
                array(
                  'RecoveryAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'55.99'),
                  'OriginalAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'44.99'),
                  'GroupBeginDate'=>'2017-02-27T10:10:36Z',
                  'GroupEndDate'=>'2017-02-27T10:10:36Z',
                ),
                //2ndrecordofDebtRecoveryItemList
                array(
                  'RecoveryAmount'=>array('CurrencyCode'=>'','Amount'=>''),
                  'OriginalAmount'=>array('CurrencyCode'=>'','Amount'=>''),
                  'GroupBeginDate'=>'',
                  'GroupEndDate'=>'',
                ),
                array(),
              ),
              'ChargeInstrumentList'=>array(
                array(
                  'Description'=>'sampledesc',
                  'Tail'=>'whatistail?',
                  'Amount'=>'22',
                  'CurrencyCode'=>'GBP',
                ),
                array(
                  'Description'=>'anothersma',
                  'Tail'=>'nanikore',
                  'Amount'=>'16',
                  'CurrencyCode'=>'GBP',
                ),
                array(),
                array(
                  'Description'=>'',
                  'Tail'=>'',
                  'Amount'=>'',
                  'CurrencyCode'=>'',
                ),
              ),
            ),
            //1st record
            array(
              'DebtRecoveryType'=>'sampletype',
              'RecoveryAmount'=>array(
                'CurrencyCode'=>'GBP',
                'Amount'=>'10.99'
              ),
              'OverPaymentCredit'=>array(
                'CurrencyCode'=>'GBP',
                'Amount'=>'20.99'
              ),
              'DebtRecoveryItemList'=>array(
                //1strecordofDebtRecoveryItemList
                array(
                  'RecoveryAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'10.99'),
                  'OriginalAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'10.99'),
                  'GroupBeginDate'=>'2017-02-27T10:10:36Z',
                  'GroupEndDate'=>'2017-02-27T10:10:36Z',
                ),

                //2ndrecordofDebtRecoveryItemList
                array(
                  'RecoveryAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'55.99'),
                  'OriginalAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'44.99'),
                  'GroupBeginDate'=>'2017-02-27T10:10:36Z',
                  'GroupEndDate'=>'2017-02-27T10:10:36Z',
                ),
                //2ndrecordofDebtRecoveryItemList
                array(
                  'RecoveryAmount'=>array('CurrencyCode'=>'','Amount'=>''),
                  'OriginalAmount'=>array('CurrencyCode'=>'','Amount'=>''),
                  'GroupBeginDate'=>'',
                  'GroupEndDate'=>'',
                ),
                array(),
              ),
              'ChargeInstrumentList'=>array(
                array(
                  'Description'=>'sampledesc',
                  'Tail'=>'whatistail?',
                  'Amount'=>'22',
                  'CurrencyCode'=>'GBP',
                ),
                array(
                  'Description'=>'anothersma',
                  'Tail'=>'nanikore',
                  'Amount'=>'16',
                  'CurrencyCode'=>'GBP',
                ),
                array(),
                array(
                  'Description'=>'',
                  'Tail'=>'',
                  'Amount'=>'',
                  'CurrencyCode'=>'',
                ),
              ),
            ),
          )
        ),
      );
    }

    /**
    * @group financialevent
    * @dataProvider provider_test_insert_DebtRecovery
    */
    public function test_insert_DebtRecovery($mockinputarray)
    {
        $c = new UpdateFinancialEventsController;

        $reflection = new \ReflectionClass(get_class($c));
        $method = $reflection->getMethod('_insert_DebtRecovery');
        $method->setAccessible(true);

        $u = new UniversalModel;
        $result = $method->invokeArgs($c,array($u,$mockinputarray,1));

        if($result!=false){
          foreach($result as $id){
            $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_debt_recoveries', ['id' => $id]);
          }
        }
    }


    public function provider_test_insert_DebtRecoveryItemList()
    {
      return array(
        array(''),
        array(
          array(
            //1strecordofDebtRecoveryItemList
            array(
              'RecoveryAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'10.99'),
              'OriginalAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'10.99'),
              'GroupBeginDate'=>'2017-02-27T10:10:36Z',
              'GroupEndDate'=>'2017-02-27T10:10:36Z',
            ),

            //2ndrecordofDebtRecoveryItemList
            array(
              'RecoveryAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'55.99'),
              'OriginalAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'44.99'),
              'GroupBeginDate'=>'2017-02-27T10:10:36Z',
              'GroupEndDate'=>'2017-02-27T10:10:36Z',
            ),

            array(),

            //2ndrecordofDebtRecoveryItemList
            array(
              'RecoveryAmount'=>array('CurrencyCode'=>'','Amount'=>''),
              'OriginalAmount'=>array('CurrencyCode'=>'','Amount'=>''),
              'GroupBeginDate'=>'',
              'GroupEndDate'=>'',
            ),
          ),


        ),
      );
    }


    /**
     * @group financialevent
     * @dataProvider provider_test_insert_DebtRecoveryItemList
     */
    public function test_insert_DebtRecoveryItemList($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_DebtRecoveryItemList');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,1,$mockinputarray));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_debt_recovery_item_lists', ['id' => $id]);
        }
      }
    }


    public function provider_test_insert_DebtRecoveryChargeInstrumentList()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'Description'=>'sampledesc',
              'Tail'=>'whatistail?',
              'Amount'=>'22',
              'CurrencyCode'=>'GBP',
            ),
            array(
              'Description'=>'anothersma',
              'Tail'=>'nanikore',
              'Amount'=>'16',
              'CurrencyCode'=>'GBP',
            ),
            array(),
            array(
              'Description'=>'',
              'Tail'=>'',
              'Amount'=>'',
              'CurrencyCode'=>'',
            ),
          )
        ),
      );
    }

    /**
     * @group financialevent
     * @dataProvider provider_test_insert_DebtRecoveryChargeInstrumentList
     */
    public function test_insert_DebtRecoveryChargeInstrumentList($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_DebtRecoveryChargeInstrumentList');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,1,$mockinputarray));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_debt_recovery_charge_instrument_lists', ['id' => $id]);
        }
      }
    }




    public function provider_test_insert_LoanServicing()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'CurrencyCode' => 'GBP',
              'Amount' => '67.58',
              'SourceBusinessEventType' => 'LoanAdvance',
            ),
            array(
              'CurrencyCode' => 'GBP',
              'Amount' => '92.44',
              'SourceBusinessEventType' => 'LoanRefund',
            ),
            array(
              'CurrencyCode' => '',
              'Amount' => '',
              'SourceBusinessEventType' => '',
            ),
            array(),
          )
        ),
      );
    }

    /**
     * @group financialevent
     * @dataProvider provider_test_insert_LoanServicing
     */
    public function test_insert_LoanServicing($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_LoanServicing');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,$mockinputarray,1));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_loan_servicings', ['id' => $id]);
        }
      }
    }


    public function provider_test_insert_ServiceFee()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'AmazonOrderId' => '123456789',
              'FeeReason' => 'sameple rreassonn',
              'FeeList' => array(
                array(
                  'FeeType'=>'FBADisposalFee',
                  'Amount'=>'55.55',
                  'CurrencyCode'=>'GBP',
                ),
                array(
                  'FeeType'=>'FBADisposalFee',
                  'Amount'=>'11.12',
                  'CurrencyCode'=>'GBP',
                ),
                array(
                  'FeeType'=>'FBADisposalFee',
                  'Amount'=>'33.00',
                  'CurrencyCode'=>'GBP',
                ),

              ),
              'SellerSKU' => 'asd-123',
              'FnSKU' => 'fnasd-123',
              'FeeDescription' => 'descsdsesc',
              'ASIN' => 'FFF111',
            ),
            array(),
          ),
        ),
        array(
          array(
            array(
              'AmazonOrderId' => '000000000',
              'FeeReason' => 'qyerpioasdm askdm asd',
              'FeeList' => array(
                array(
                  'FeeType'=>'FBADisposalFee',
                  'Amount'=>'4.11',
                  'CurrencyCode'=>'GBP',
                ),
                array(
                  'FeeType'=>'FBADisposalFee',
                  'Amount'=>'3.22',
                  'CurrencyCode'=>'GBP',
                ),
                array(
                  'FeeType'=>'FBADisposalFee',
                  'Amount'=>'4.44',
                  'CurrencyCode'=>'GBP',
                ),

              ),
              'SellerSKU' => 'ddd-222',
              'FnSKU' => 'fnxxx-555',
              'FeeDescription' => 'lorem ipsum',
              'ASIN' => 'ggg111',
            ),

            array(
              'AmazonOrderId' => 'aannoo',
              'FeeReason' => 'tthhheerrr',
              'FeeList' => array(),
              'SellerSKU' => '333-5555',
              'FnSKU' => '5555-8888',
              'FeeDescription' => 'bgbbfbfb',
              'ASIN' => 'hhhhh555',
            ),
            array(
              'AmazonOrderId' => 'xxxxx',
              'FeeReason' => 'xxxxx',
              'SellerSKU' => '333-5555',
              'FnSKU' => '5555-8888',
              'FeeDescription' => 'xxxx',
              'ASIN' => 'xxxx',
            ),
          ),
        ),

      );
    }
    /**
     * @group financialevent
     * @dataProvider provider_test_insert_ServiceFee
     */
    public function test_insert_ServiceFee($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_ServiceFee');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,$mockinputarray,1));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_service_fees', ['id' => $id]);
        }
      }
    }


    public function provider_test_insert_ServiceFeeFeeList()
    {
      return array(
        array(''),
        array(
          array(
              array(
                'FeeType'=>'FBADisposalFee',
                'Amount'=>'55.55',
                'CurrencyCode'=>'GBP',
              ),
              array(
                'FeeType'=>'FBADisposalFee',
                'Amount'=>'11.12',
                'CurrencyCode'=>'GBP',
              ),
          )
        ),
        array(
          array(
            array(
              'FeeType'=>'FBADisposalFee',
              'Amount'=>'33.00',
              'CurrencyCode'=>'GBP',
            ),
            array(
              'FeeType'=>'',
              'Amount'=>'',
              'CurrencyCode'=>'',
            ),
            array(),
          ),
        ),
      );

    }


    /**
     * @group financialevent
     * @dataProvider provider_test_insert_ServiceFeeFeeList
     */
    public function test_insert_ServiceFeeFeeList($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_ServiceFeeFeeList');
      $method->setAccessible(true);


      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,1,$mockinputarray));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_service_fee_fee_lists', ['id' => $id]);
        }
      }
    }


    public function provider_test_insert_PerformanceBondRefund()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'MarketplaceCountryCode'=>'UK',
              'CurrencyCode'=>'GBP',
              'Amount'=>'32.33',
              'ProductGroupList'=>'samplegourplist',
            ),
          ),
        ),
        array(
          array(
            array(
              'MarketplaceCountryCode'=>'FR',
              'CurrencyCode'=>'EUR',
              'Amount'=>'17.99',
              'ProductGroupList'=>'agrouplist',
            ),
            array(),
            array(
              'MarketplaceCountryCode'=>'',
              'CurrencyCode'=>'',
              'Amount'=>'',
              'ProductGroupList'=>'',
            ),
          ),
        ),
      );

    }

    /**
     * @group financialevent
     * @dataProvider provider_test_insert_PerformanceBondRefund
     */
    public function test_insert_PerformanceBondRefund($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_PerformanceBondRefund');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,$mockinputarray,1));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_performance_bond_refunds', ['id' => $id]);
        }
      }
    }



    public function provider_test_insert_RentalTransaction()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'AmazonOrderId'=>'AMZ123123',
              'RentalEventType'=>'RentalLostItemReimbursement',
              'ExtensionLength'=>'5',
              'PostedDate'=>'2017-03-01T09:55:39Z',
              'MarketplaceName'=>'smaplemkpname',
              'RentalInitialValue'=>array('CurrencyCode'=>'GBP','Amount'=>'10.01'),
              'RentalReimbursement'=>array('CurrencyCode'=>'GBP','Amount'=>'20.91'),
              'RentalChargeList'=>array(),
              'RentalFeeList'=>array(),
            ),
            array(
              'AmazonOrderId'=>'ASD45454',
              'RentalEventType'=>'RentalHandlingFee ',
              'ExtensionLength'=>'15',
              'PostedDate'=>'2017-03-03T13:22:11Z',
              'MarketplaceName'=>'ccccccccccc',
              'RentalInitialValue'=>array('CurrencyCode'=>'GBP','Amount'=>'55.22'),
              'RentalReimbursement'=>array('CurrencyCode'=>'GBP','Amount'=>'2.33'),
              'RentalChargeList'=>array(
                array(
                  'ChargeType'=>'Tax',
                  'Amount'=>'1.09',
                  'CurrencyCode'=>'GBP',
                ),
                array(),
                array(
                  'ChargeType'=>'',
                  'Amount'=>'',
                  'CurrencyCode'=>'',
                ),
              ),
              'RentalFeeList'=>array(
                array(
                  'FeeType'=>'FBALongTermStorageFee',
                  'Amount'=>'2.55',
                  'CurrencyCode'=>'GBP',
                ),
                array(),
                array(
                  'FeeType'=>'',
                  'Amount'=>'',
                  'CurrencyCode'=>'',
                ),
              ),





            ),
            array(),
          ),
        ),

        array(
          array(
            array(
              'AmazonOrderId'=>'PPQ685858',
              'RentalEventType'=>'RentalLostItemReimbursement',
              'ExtensionLength'=>'23',
              'PostedDate'=>'2017-04-01T01:35:49Z',
              'MarketplaceName'=>'jhhhhhh',
              'RentalInitialValue'=>array('CurrencyCode'=>'GBP','Amount'=>'28.01'),
              'RentalReimbursement'=>array('CurrencyCode'=>'GBP','Amount'=>'36.91'),
              'RentalChargeList'=>array(),
              'RentalFeeList'=>array(
                array(
                  'FeeType'=>'',
                  'Amount'=>'',
                  'CurrencyCode'=>'',
                ),
              ),
            ),
            array(
              'AmazonOrderId'=>'',
              'RentalEventType'=>'',
              'ExtensionLength'=>'',
              'PostedDate'=>'',
              'MarketplaceName'=>'',
              'RentalInitialValue'=>array('CurrencyCode'=>'','Amount'=>''),
              'RentalReimbursement'=>array('CurrencyCode'=>'','Amount'=>''),
              'RentalChargeList'=>array(
                array(
                  'ChargeType'=>'',
                  'Amount'=>'',
                  'CurrencyCode'=>'',
                ),
              ),
              'RentalFeeList'=>array(),
            ),
          ),
        ),

      );

    }

    /**
     * @group financialevent
     * @dataProvider provider_test_insert_RentalTransaction
     */
    public function test_insert_RentalTransaction($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_RentalTransaction');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,$mockinputarray,1));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_rental_transactions', ['id' => $id]);
        }
      }
    }




    public function provider_test_insert_RentalTransactionRentalChargeList()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'ChargeType'=>'Tax',
              'Amount'=>'1.09',
              'CurrencyCode'=>'GBP',
            ),
            array(),
            array(
              'ChargeType'=>'',
              'Amount'=>'',
              'CurrencyCode'=>'',
            ),
          ),
        ),
        array(
          array(
            array(
              'ChargeType'=>'ShippingCharge',
              'Amount'=>'2.55',
              'CurrencyCode'=>'GBP',
            ),
            array(),
            array(
              'ChargeType'=>'',
              'Amount'=>'',
              'CurrencyCode'=>'',
            ),
          ),
        ),
      );

    }

    /**
     * @group financialevent
     * @dataProvider provider_test_insert_RentalTransactionRentalChargeList
     */
    public function test_insert_RentalTransactionRentalChargeList($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_RentalTransactionRentalChargeList');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,1,$mockinputarray));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_rental_transaction_rental_charge_lists', ['id' => $id]);
        }
      }
    }



    public function provider_test_insert_RentalTransactionRentalFeeList()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'FeeType'=>'FBAInboundTransportationFee',
              'Amount'=>'1.09',
              'CurrencyCode'=>'GBP',
            ),
            array(),
            array(
              'FeeType'=>'',
              'Amount'=>'',
              'CurrencyCode'=>'',
            ),
          ),
        ),
        array(
          array(
            array(
              'FeeType'=>'FBALongTermStorageFee',
              'Amount'=>'2.55',
              'CurrencyCode'=>'GBP',
            ),
            array(),
            array(
              'FeeType'=>'',
              'Amount'=>'',
              'CurrencyCode'=>'',
            ),
          ),
        ),
      );

    }

    /**
     * @group financialevent
     * @dataProvider provider_test_insert_RentalTransactionRentalFeeList
     */
    public function test_insert_RentalTransactionRentalFeeList($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_RentalTransactionRentalFeeList');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,1,$mockinputarray));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_rental_transaction_rental_fee_lists', ['id' => $id]);
        }
      }
    }


    public function provider_test_insert_Retrocharge()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'RetrochargeEventType'=>'Retrocharge',
              'AmazonOrderId'=>'QWE123',
              'PostedDate'=>'2017-03-01T09:55:39Z',
              'BaseTax'=>array('CurrencyCode'=>'GBP','Amount'=>'9.99'),
              'ShippingTax'=>array('CurrencyCode'=>'GBP','Amount'=>'2.33'),
              'MarketplaceName'=>'amazon.co.uk',
            ),
            array(),
            array(
              'RetrochargeEventType'=>'',
              'AmazonOrderId'=>'',
              'PostedDate'=>'',
              'BaseTax'=>array('CurrencyCode'=>'','Amount'=>''),
              'ShippingTax'=>array('CurrencyCode'=>'','Amount'=>''),
              'MarketplaceName'=>'',
            ),
          ),
        ),
        array(
          array(
            array(),
            array(
              'RetrochargeEventType'=>'RetrochargeReversal',
              'AmazonOrderId'=>'FFF666',
              'PostedDate'=>'2017-03-01T09:55:39Z',
              'BaseTax'=>array('CurrencyCode'=>'EUR','Amount'=>'32'),
              'ShippingTax'=>array('CurrencyCode'=>'EUR','Amount'=>'12'),
              'MarketplaceName'=>'amazon.de',
            ),
          ),

        ),
      );

    }


    /**
     * @group financialevent
     * @dataProvider provider_test_insert_Retrocharge
     */
    public function test_insert_Retrocharge($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_Retrocharge');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,$mockinputarray,1));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_retrocharges', ['id' => $id]);
        }
      }
    }




























    // -----------------------------------------------------
    public function provider_test_insert_SAFETReimbursement()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'PostedDate'=>'2017-03-01T09:55:39Z',
              'SAFETClaimId'=>'smaplemkpname',
              'ReimbursedAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'10.01'),
              'ReimbursedAmount'=>array('CurrencyCode'=>'GBP','Amount'=>'20.91'),
              'SAFETReimbursementItemList'=>array(),
              'RentalFeeList'=>array(),
            ),
            array(),
          ),
        ),

      );

    }

    /**
     * @group financialevent
     * @dataProvider provider_test_insert_SAFETReimbursement
     *
     */
    public function test_insert_SAFETReimbursement($mockinputarray)
    {
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_SAFETReimbursement');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,$mockinputarray,1));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_s_a_f_e_t_reimbursements', ['id' => $id]);
        }
      }
    }




    public function provider_test_insert_SAFETReimbursementItemList()
    {
      return array(
        array(''),
        array(
          array(
            array(
              'ChargeType'=>'Tax',
              'Amount'=>'1.09',
              'CurrencyCode'=>'GBP',
            ),
            array(),
            array(
              'ChargeType'=>'',
              'Amount'=>'',
              'CurrencyCode'=>'',
            ),
          ),
        ),
        array(
          array(
            array(
              'ChargeType'=>'ShippingCharge',
              'Amount'=>'2.55',
              'CurrencyCode'=>'GBP',
            ),
            array(),
            array(
              'ChargeType'=>'',
              'Amount'=>'',
              'CurrencyCode'=>'',
            ),
          ),
        ),
      );

    }

    /**
     * @group financialevent
     * @dataProvider provider_test_insert_SAFETReimbursementItemList
     */
    public function test_insert_SAFETReimbursementItemList($mockinputarray)
    {
      //samplecomment
      $c = new UpdateFinancialEventsController;

      $reflection = new \ReflectionClass(get_class($c));
      $method = $reflection->getMethod('_insert_SAFETReimbursementItemList');
      $method->setAccessible(true);

      $u = new UniversalModel;
      $result = $method->invokeArgs($c,array($u,1,$mockinputarray));

      if($result!=false){
        foreach($result as $id){
          $this->assertDatabaseHas( env('DB_DATABASE2') . '.financial_event_s_a_f_e_t_reimbursement_item_lists', ['id' => $id]);
        }
      }
    }

}
