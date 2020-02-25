<?php

use Carbon\Carbon;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition;
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

$factory->define(EventRepetition::class, function (Faker $faker) {
    $date_start_timestamp = rand(1895589603, 1924447203);
    $date_start = Carbon::parse($date_start_timestamp);
    $date_end = $date_start->addDay()->toDateString();

    return [
        'event_id' => rand(10, 100),
        'start_repeat' => $date_start,
        'end_repeat' => $date_end,
    ];
});
