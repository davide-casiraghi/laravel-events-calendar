<?php

use Faker\Generator as Faker;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(EventVenue::class, function (Faker $faker) {
    $name = $faker->name;
    $slug = Str::slug($name, '-').rand(10000, 100000);

    return [
        'created_by' => 1,
        'name' => $name,
        'slug' => $slug,
        'description' => $faker->paragraph,
        'website' => $faker->url,
        'continent_id' => 1,
        'country_id' => 1,
        'region_id' => 1,
        'city' => $faker->city,
        'address' => $faker->streetAddress,
        'zip_code' => $faker->postcode,
        'lat' => '51.212034',
        'lng' => '4.432853',
        //'state_province' => $faker->state,
    ];
});
