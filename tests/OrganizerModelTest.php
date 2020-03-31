<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Organizer;
use Illuminate\Foundation\Testing\WithFaker;

class OrganizerModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_gets_the_organizer_creator()
    {
        $this->authenticateAsAdmin();

        // Create a teacher by the administrator, that has id 1
        $attributes = factory(Organizer::class)->raw([
            'name'=>'test organizer',
        ]);
        $this->post('/organizers', $attributes);

        $organizer = Organizer::find(1);

        // Get the user id of the user that create the teacher
        $creatorId = $organizer->user->id;

        $this->assertEquals($creatorId, 1);
    }

    /***************************************************************/
}
