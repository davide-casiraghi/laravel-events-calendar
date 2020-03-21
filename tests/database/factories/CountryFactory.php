<?php

use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
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

$factory->define(Country::class, function (Faker $faker) {
    $country_name = $faker->country;

    return [
        'name' => $country_name,
        'code' => $faker->unique()->countryCode,
        'continent_id' => 1,
    ];
});
