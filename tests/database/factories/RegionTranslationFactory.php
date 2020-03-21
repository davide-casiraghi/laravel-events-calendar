<?php

use DavideCasiraghi\LaravelEventsCalendar\Models\RegionTranslation;
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

$factory->define(RegionTranslation::class, function (Faker $faker) {
    $region_name = $faker->name;
    $slug = Str::slug($region_name, '-').rand(10000, 100000);

    return [
        'name' => $region_name,
        'slug' => $slug,
        'region_id' => 1,
        'locale' => 'en',
    ];
});
