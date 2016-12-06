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

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'host'     => $faker->domainName,
        'user'     => $faker->userName,
        'realm'    => $faker->randomElement(['pam', 'pve', $faker->domainName]),
        'password' => $faker->password,
    ];
});

$factory->define(App\Server::class, function (Faker\Generator $faker) {
    return [
        'user_id'        => 1,
        'unique_id'      => $faker->slug,
        'name'           => $faker->domainName,
        'region_id'      => 1,
        'server_size_id' => 1,
        'vmid'           => $faker->numberBetween(100, 999),
        'key_id'         => null,
        'password'       => $faker->password,
        'status'         => $faker->randomElement(['pending', 'creating', 'active', 'destroying', 'rebooting']),
        'external_ip'    => $faker->ipv4,
        'internal_ip'    => $faker->localIpv4,
    ];
});
