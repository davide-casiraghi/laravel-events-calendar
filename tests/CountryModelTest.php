<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;
use Illuminate\Foundation\Testing\WithFaker;

class CountryModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_gets_all_the_countries()
    {
        $this->authenticate();

        $countries = [];
        $countries[] = factory(Country::class)->create(['name' => 'Slovenia']);
        $countries[] = factory(Country::class)->create(['name' => 'Italia']);

        $countries = Country::getCountries();
        $countriesArray = $countries->toArray();

        $this->assertTrue(in_array('Slovenia', $countriesArray)); // Slovenia
        $this->assertTrue(in_array('Italia', $countriesArray)); // Slovenia
    }

    /***************************************************************/

    /** @test */
    public function it_caches_the_countries()
    {
        $this->authenticate();

        $countries = [];
        $countries[] = factory(Country::class)->create(['name' => 'Slovenia']);
        $countries[] = factory(Country::class)->create(['name' => 'Italia']);

        $countries = Country::getCountries(); // Retrieve countries, so it stores the value in the cache

        $res = Country::where('id', 1)->delete();

        $countries = Country::getCountries();
        $countriesArray = $countries->toArray();

        $this->assertTrue(in_array('Slovenia', $countriesArray));
        $this->assertTrue(in_array('Italia', $countriesArray));
    }

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

        $this->assertTrue($activeCountries->contains('id', 1)); // Slovenia
        $this->assertFalse($activeCountries->contains('id', 2)); // Italy
    }

    /** @test */
    public function it_gets_active_countries_by_continent()
    {
        $this->authenticate();

        $continents = [];
        $continents[] = factory(Continent::class)->create(['name' => 'Europe', 'code'=> 'EU']);
        $continents[] = factory(Continent::class)->create(['name' => 'Africa', 'code'=> 'AF']);

        $countries = [];
        $countries[] = factory(Country::class)->create(['name' => 'Slovenia', 'continent_id' => $continents[0]->id]);
        $countries[] = factory(Country::class)->create(['name' => 'Italy', 'continent_id' => $continents[0]->id]);
        $countries[] = factory(Country::class)->create(['name' => 'Morocco', 'continent_id' => $continents[1]->id]);

        $eventVenues = [];
        $eventVenues[] = factory(EventVenue::class)->create(['country_id' => 1]);  //Venue in Slovenia
        $eventVenues[] = factory(EventVenue::class)->create(['country_id' => 3]); // Venue in Morocco

        // Create an EVENT in the Slovenian Venue
        $attributes = factory(Event::class)->raw([
            'venue_id'=> $eventVenues[0]->id,
        ]);
        $response = $this->post('/events', $attributes);

        // Create an EVENT in the Moroccan Venue
        $attributes = factory(Event::class)->raw([
            'venue_id'=> $eventVenues[1]->id,
        ]);
        $response = $this->post('/events', $attributes);

        $activeCountriesbyContinent = Country::getActiveCountriesByContinent($continents[0]->id);

        $this->assertTrue($activeCountriesbyContinent->contains('name', 'Slovenia'));
        $this->assertFalse($activeCountriesbyContinent->contains('name', 'Italy'));
        $this->assertFalse($activeCountriesbyContinent->contains('name', 'Morocco'));
    }
}
