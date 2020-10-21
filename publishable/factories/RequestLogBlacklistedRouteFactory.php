<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Cego\RequestLog\Models\RequestLogBlacklistedRoute;

$factory->define(RequestLogBlacklistedRoute::class, function (Faker $faker) {
    return [
        'path' => $faker->url,
    ];
});
