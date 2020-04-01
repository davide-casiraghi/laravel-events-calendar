<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;
use Illuminate\Foundation\Testing\WithFaker;

class ConsoleCommandsTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_execute_retrieve_gps_coordinates_command()
    {
        $this->authenticateAsAdmin();

        // Create a teacher by the administrator, that has id 1
        $attributes = factory(EventVenue::class)->raw([
            'continent_id' => 1,
            'country_id' => 1,
            'city' => 'Berlin',
            'address' => 'Hasenheide, 54',
            //'zip_code' => $this->faker->postcode,
        ]);

        Continent::insert([
            'name' => 'Europe',
            'code' => 'EU',
        ]);
        Country::insert([
            'name' => 'Germany',
            'code' => 'DE',
            'continent_id' => 1,
        ]);
        $response = $this->post('/eventVenues', $attributes);

        $aa = $this->artisan('retrieve-all-gps-coordinates')

         // @ TODO: still to figure out how to check the command output
         //->expectsQuestion('What is your name?', 'Taylor Otwell')
         //->expectsQuestion('Which language do you program in?', 'PHP')
         //->expectsOutput('52.48789')
         //dd($this->output());

         //check if the command run succesfully
         ->assertExitCode(0);
    }

    /***************************************************************/
}
