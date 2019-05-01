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

$factory->define(DavideCasiraghi\LaravelEventsCalendar\Models\EventCategoryTranslation::class, function (Faker $faker) {
    $event_category_name = $faker->name;
    $slug = Str::slug($event_category_name, '-').rand(10000, 100000);
    return [
        'name' => $event_category_name,
        'slug' => $slug,
        'event_category_id' => 1,
        'locale' => 'en'
    ];
});
