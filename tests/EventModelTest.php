<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Carbon\Carbon;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;
use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use Illuminate\Foundation\Testing\WithFaker;

class EventModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_gets_active_events()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw(['title'=>'test title']);
        $this->post('/events', $attributes);

        $activeEvents = Event::getActiveEvents();
        $this->assertEquals($activeEvents[0]->title, 'test title');
    }

    /***************************************************************/

    /** @test */
    public function it_caches_active_events()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw(['title'=>'test title']);
        $this->post('/events', $attributes);

        $activeEvents = Event::getActiveEvents();
        $this->assertEquals($activeEvents[0]->title, 'test title');

        $res = Event::where('id', 1)->delete();

        // If I clean the cache the test should fail
        //Artisan::call('cache:clear');

        $activeEvents = Event::getActiveEvents();
        $this->assertEquals($activeEvents[0]->title, 'test title');
    }

    /***************************************************************/

    /** @test */
    public function it_gets_filtered_events()
    {
        $this->authenticate();
        $teacher = factory(Teacher::class)->create();
        $eventVenue = factory(EventVenue::class)->create();

        $eventAttributes = factory(Event::class)->raw([
            'title'=>'test title',
            'multiple_teachers' => $teacher->id,
            'venue_id' => $eventVenue->id,
        ]);
        $response = $this->post('/events', $eventAttributes);

        $eventCreated = Event::first();

        $filters = [
            'keywords' => 'test title',
            'endDate' => '2030-02-02',
            'category' => '1',
            'teacher' => null,  //use just integer 1,  no such function: json_contains
            'country' => $eventVenue->country_id,
            'continent' => $eventVenue->continent_id,
            'region' => $eventVenue->region_id,
            'city' => $eventVenue->city,
            'venue' => $eventVenue->name,
        ];

        $itemPerPage = 20;

        $events = Event::getEvents($filters, $itemPerPage);
        //dd($events[0]);
        $this->assertEquals($events[0]->title, 'test title');
    }

    /***************************************************************/

    /** @test */
    public function it_gets_unfiltered_events()
    {
        $this->authenticate();

        $eventAttributes = factory(Event::class)->raw([
            'title'=>'test title',
        ]);
        $response = $this->post('/events', $eventAttributes);

        $eventCreated = Event::first();

        $filters = [
            'keywords' => '',
            'endDate' => '',
            'category' => '',
            'teacher' => '',
            'country' => '',
            'continent' => '',
            'region' => '',
            'city' => '',
            'venue' => '',
        ];

        $itemPerPage = 20;

        $events = Event::getEvents($filters, $itemPerPage);
        //dd($events[0]);
        $this->assertEquals($events[0]->title, 'test title');
    }

    /***************************************************************/

    /** @test */
    public function it_gets_active_events_map_markers_data_from_db()
    {
        $this->authenticate();

        $eventVenue = factory(EventVenue::class)->create([
            'lat' => '10,0000',
            'lng' => '20,33333',
            'address' => '169 Endicott St',
            'city' => 'Boston',
        ]);

        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'venue_id' => $eventVenue->id,
        ]);
        $this->post('/events', $attributes);

        $activeEvents = Event::getActiveEventsMapMarkersDataFromDb();

        $this->assertEquals($activeEvents[0]->title, 'test title');
        $this->assertEquals($activeEvents[0]->lat, '10,0000');
        $this->assertEquals($activeEvents[0]->lng, '20,33333');
    }

    /***************************************************************/

    /** @test */
    public function it_caches_active_events_map_markers_data_from_db()
    {
        $this->authenticate();

        $eventVenue = factory(EventVenue::class)->create([
            'lat' => '10,0000',
            'lng' => '20,33333',
            'address' => '169 Endicott St',
            'city' => 'Boston',
        ]);

        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'venue_id' => $eventVenue->id,
        ]);
        $this->post('/events', $attributes);

        $activeEvents = Event::getActiveEventsMapMarkersDataFromDb();

        $res = Event::where('id', 1)->delete();

        $activeEvents = Event::getActiveEventsMapMarkersDataFromDb();

        $this->assertEquals($activeEvents[0]->title, 'test title');
        $this->assertEquals($activeEvents[0]->lat, '10,0000');
        $this->assertEquals($activeEvents[0]->lng, '20,33333');
    }

    /***************************************************************/

    /** @test */
    public function it_gets_active_events_map_geo_json()
    {
        $this->authenticate();

        $eventCategory = factory(EventCategory::class)->create([
            'id' => 6,
            'name' => 'Festival',
            'slug' => 'festival',
        ]);

        $eventVenue = factory(EventVenue::class)->create([
            'name' => 'Venue Name Test',
            'lat' => '10,0000',
            'lng' => '20,33333',
            'address' => '169 Endicott St',
            'city' => 'Boston',
        ]);

        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'slug' => 'test-title',
            'venue_id' => $eventVenue->id,
            'category_id' => 6,
        ]);
        $this->post('/events', $attributes);

        $activeEventsMapMarkersGeoJSON = Event::getActiveEventsMapGeoJSON();

        $this->assertStringContainsString('Boston', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('169 Endicott St', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('"coordinates":["20,33333","10,0000"]', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('redIcon', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('Festival', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('event\/'.Event::find(1)->slug.'\/1', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('Venue Name Test', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('10 Jan 2022', $activeEventsMapMarkersGeoJSON);
    }

    /***************************************************************/

    /** @test */
    public function it_caches_active_events_map_geo_json()
    {
        $this->authenticate();

        $eventCategory = factory(EventCategory::class)->create([
            'id' => 6,
            'name' => 'Festival',
            'slug' => 'festival',
        ]);

        $eventVenue = factory(EventVenue::class)->create([
            'name' => 'Venue Name Test',
            'lat' => '10,0000',
            'lng' => '20,33333',
            'address' => '169 Endicott St',
            'city' => 'Boston',
        ]);

        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'slug' => 'test-title',
            'venue_id' => $eventVenue->id,
            'category_id' => 6,
        ]);
        $this->post('/events', $attributes);

        $activeEventsMapMarkersGeoJSON = Event::getActiveEventsMapGeoJSON();

        $res = Event::where('id', 1)->delete();

        // If I clean the cache the test should fail
        //Artisan::call('cache:clear');
        $activeEventsMapMarkersGeoJSON = Event::getActiveEventsMapGeoJSON();

        $this->assertStringContainsString('Boston', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('169 Endicott St', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('"coordinates":["20,33333","10,0000"]', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('redIcon', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('Festival', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('Venue Name Test', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('10 Jan 2022', $activeEventsMapMarkersGeoJSON);
    }

    /***************************************************************/

    /** @test */
    public function it_gets_if_event_is_active()
    {
        $this->authenticate();

        // Event in the future
        $attributes = factory(Event::class)->raw([
            'title'=>'test title 1',
            'startDate' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'endDate' => Carbon::now()->addDays(3)->format('Y-m-d'),
        ]);
        $this->post('/events', $attributes);

        // Event in the past
        $attributes = factory(Event::class)->raw([
            'title'=>'test title 2',
            'startDate' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'endDate' => Carbon::now()->subDays(3)->format('Y-m-d'),
        ]);
        $this->post('/events', $attributes);

        $event1 = Event::find(1);
        $event1ActiveState = $event1->isActive();
        $this->assertEquals($event1ActiveState, true);

        $event2 = Event::find(2);
        $event2ActiveState = $event2->isActive();
        $this->assertEquals($event2ActiveState, false);
    }
}
