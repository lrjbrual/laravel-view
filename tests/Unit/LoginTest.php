<?php
// Created By: Jun Rhy Crodua
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(){
        parent::setUp();
        $this->seed('DatabaseSeeder');
    }

    public function tearDown(){
        parent::tearDown();
    }

    /**
     * @group login
     * @return void
     */
    public function testSellerCanLogin()
    {
        $data = array(
            'email' => 'admin@ls.com',
            'password' => '123',
            'remember' => '',
            '_token' => csrf_token()
        );

        $response = $this->call('POST', 'login', $data);
        $response->assertStatus(302);
    }
}
