<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;

use Illuminate\Foundation\Testing\WithFaker;

class EventVenueModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/
    
    /** @test */
    public function it_gets_the_event_venue_creator()
    {
        $this->authenticateAsAdmin();
        
        // Create a teacher by the administrator, that has id 1
        $attributes = factory(EventVenue::class)->raw();

        Continent::insert([
            'name' => 'Europe',
            'code' => 'EU',
        ]);
        Country::insert([
            'name' => 'Italy',
            'code' => 'IT',
            'continent_id' => 1,
        ]);

        $response = $this->post('/eventVenues', $attributes);
        $eventVenue = EventVenue::first();
        
        // Get the user id of the user that create the teacher
        $creatorId = $eventVenue->user->id;
        
        $this->assertEquals($creatorId, 1);
    }
    
    /***************************************************************/
    
}
