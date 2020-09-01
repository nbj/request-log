<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\RequestLog;
use Faker\Generator as Faker;

$factory->define(RequestLog::class, function (Faker $faker) {
    return [
        'client_ip' => $faker->ipv4,
        'user_agent' => $faker->userAgent,
        'method' => $faker->randomElement(['GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'HEAD']),
        'status' => $faker->randomElement([200, 300, 400, 500]),
        'url' => $faker->url,
        'root' => 'some host',
        'path' => 'some path',
        'query_string' => 'some query string',
        'request_headers' => '{}',
        'request_body' => '{}',
        'response_headers' => '{}',
        'response_body' => '{}',
        'response_exception' => '{}',
        'execution_time' => 0.0001,
    ];
});
