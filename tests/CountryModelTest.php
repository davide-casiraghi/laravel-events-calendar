<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;

use Illuminate\Foundation\Testing\WithFaker;

class CountryModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_gets_active_countries()
    {
        $this->authenticate();
        
        $countries = [];
        $countries[] = factory(Country::class)->create(['name' => 'Slovenia']);
        $countries[] = factory(Country::class)->create(['name' => 'Italia']);
        
        // Create a VENUE in Slovenia
            $eventVenue = factory(EventVenue::class)->create(['country_id' => 1]); 
        
        // Create an EVENT in the Slovenian Venue
            $attributes = factory(Event::class)->raw([  
                'venue_id'=> $eventVenue->id,
            ]);
            $response = $this->post('/events', $attributes);   
        
        $activeCountries = Country::getActiveCountries();
    
        // Check if Italy and Slovenia are active countries 
            $sloveniaPresent = $activeCountries->contains('id', 1);
            $italiaPresent = $activeCountries->contains('id', 2);
        
        $this->assertEquals($sloveniaPresent, true);
        $this->assertEquals($italiaPresent, false);    
    }
    
}
