<?php

use DavideCasiraghi\LaravelEventsCalendar\Models\Region;
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

$factory->define(Region::class, function (Faker $faker) {
    $region_name = $faker->name;
    $slug = Str::slug($region_name, '-').rand(10000, 100000);
    $timezone = '+2:00';
    $country_id = 1;

    return [
        'name:en' => $region_name,
        'slug:en' => $slug,
        'timezone' => $timezone,
        'country_id' => $country_id,
    ];
});
