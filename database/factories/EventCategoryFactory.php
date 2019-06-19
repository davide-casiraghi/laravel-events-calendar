<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;

$factory->define(EventCategory::class, function (Faker $faker) {
    $event_category_name = $faker->name;
    $slug = Str::slug($event_category_name, '-').rand(10000, 100000);

    return [
        'name:en' => $event_category_name,
        'slug:en' => $slug,
    ];
});
