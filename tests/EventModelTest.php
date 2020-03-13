<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
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
    public function it_gets_active_events_map_geo_json()
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
    
        $activeEventsMapMarkersGeoJSON = Event::getActiveEventsMapGeoJSON();
        
        $this->assertStringContainsString('Boston, 169 Endicott St', $activeEventsMapMarkersGeoJSON);
        $this->assertStringContainsString('"coordinates":["10,0000","20,33333"]', $activeEventsMapMarkersGeoJSON);
    
    }
    
    
    
}
