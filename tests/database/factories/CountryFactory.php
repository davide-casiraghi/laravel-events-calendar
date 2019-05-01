<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(DavideCasiraghi\LaravelEventsCalendar\Models\Country::class, function (Faker $faker) {
    $country_name = $faker->country;

    return [
        'name' => $country_name,
        'code' => $faker->countryCode,
        'continent_id' => 1,
    ];
});
