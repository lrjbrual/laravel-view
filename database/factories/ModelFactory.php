<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('123'),
        'remember_token' => str_random(10),
        'access' => 1,
        'is_verified' => 1,
        'is_inHouse' => 1,
        'is_admin' => 0,
        'is_active' => 1,
        'seller_id' => 1,
        'created_at' => null,
        'updated_at' => null,
    ];

});
