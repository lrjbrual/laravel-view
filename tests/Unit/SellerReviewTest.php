<?php
// Created By: Jun Rhy Crodua
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Artisan;

class SellerReviewTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(){
        parent::setUp();
        $this->seed('DatabaseSeeder');
        Artisan::call('currency:manage', ['action' => 'add', 'currency' => 'gbp,eur,usd,cad']);
        Artisan::call('currency:update', ['--openexchangerates' => 'default']);
    }

    public function tearDown(){
        parent::tearDown();
    }

    /**
     * @group seller_review
     * @return void
     */
    public function testSellerReview()
    {

    }
}
