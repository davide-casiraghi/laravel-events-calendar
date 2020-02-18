<?php

use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;
use DavideCasiraghi\LaravelEventsCalendar\Models\Organizer;
use DavideCasiraghi\LaravelEventsCalendar\Models\Region;
use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use Faker\Generator as Faker;
use Illuminate\Foundation\Auth\User;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Event::class, function (Faker $faker) {
    $continent = Continent::first();
    if ($continent == null) {
        $continent = factory(Continent::class)->create();
    }
    $country = Country::first();
    if ($country == null) {
        $country = factory(Country::class)->create();
    }
    $country = Region::first();
    if ($country == null) {
        $country = factory(Region::class)->create();
    }
    $eventCategory = factory(EventCategory::class)->create();

    $venue = factory(EventVenue::class)->create();

    //$user = factory(\DavideCasiraghi\LaravelEventsCalendar\Models\User::class)->create();
    $user = User::first();

    // Generate two teachers and get the IDs eg (3, 4, 5)
    $teachers = factory(Teacher::class, 2)->create();
    $teachers_id = '';
    $i = 0;
    $len = count($teachers);
    foreach ($teachers as $key => $teacher) {
        $teachers_id .= $teacher->id;
        if ($i != $len - 1) {  // not last
            $teachers_id .= ', ';
        }
        $i++;
    }

    // Generate two organizers and get the IDs eg (3, 4, 5)
    $organizers = factory(Organizer::class, 2)->create();
    $organizers_id = '';
    $i = 0;
    $len = count($organizers);
    foreach ($organizers as $key => $organizer) {
        $organizers_id .= $organizer->id;
        if ($i != $len - 1) {  // not last
            $organizers_id .= ', ';
        }
        $i++;
    }

    $title = $this->faker->sentence($nbWords = 3);

    return [
        'title' => $title,
        'category_id' => '1',
        'description' => $this->faker->paragraph,
        'created_by' => $user->id,
        'slug' => Str::slug($title, '-').rand(100000, 1000000),
        //'multiple_teachers' => $teachers_id,
        //'multiple_organizers' => $organizers_id,
        'venue_id' => $venue->id,
        'startDate' => '10/01/2022',
        'endDate' => '12/01/2022',
        //'time_start' => '6:00 PM',
        //'time_end' => '8:00 PM',
        'repeat_type' => '1',
        'facebook_event_link' => 'https://www.facebook.com/'.$this->faker->word,
        'website_event_link' => $this->faker->url,
        'repeat_weekly_on_day' => null,
    ];
});
