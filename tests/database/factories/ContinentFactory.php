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

$factory->define(DavideCasiraghi\LaravelEventsCalendar\Models\Continent::class, function (Faker $faker) {
    $continent_name = $faker->name;

    return [
        'name' => $continent_name,
        'code' => $faker->stateAbbr,
    ];
});
