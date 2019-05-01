<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;

class EventControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_events_index_page()
    {
        // Authenticate the admin
        //$this->authenticateAsAdmin();

        $this->get('events')
            ->assertViewIs('laravel-events-calendar::events.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_event_create_page()
    {
        $this->get('events/create')
            ->assertViewIs('laravel-events-calendar::events.create')
            ->assertStatus(200);
    }

    /* @test */
    /*public function it_stores_a_valid_organizer()
    {
        $attributes = factory(Organizer::class)->raw();

        $user = User::first();
        auth()->login($user);

        $response = $this->post('/organizers', $attributes);
        $organizer = Organizer::first();

        //$this->assertDatabaseHas('organizers', $attributes);
        $response->assertRedirect('/organizers/');
    }*/

    /* @test */
    /*public function it_does_not_store_invalid_organizer()
    {
        $response = $this->post('/organizers', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Organizer::first());
    }*/

    /* @test */
    /*public function it_displays_the_organizer_show_page()
    {
        $organizer = factory(Organizer::class)->create();
        $response = $this->get("/organizers/{$organizer->id}");
        $response->assertViewIs('laravel-events-calendar::organizers.show')
                 ->assertStatus(200);
    }*/

    /* @test */
    /*public function it_displays_the_organizer_edit_page()
    {
        $organizer = factory(Organizer::class)->create();
        $response = $this->get("/organizers/{$organizer->id}/edit");
        $response->assertViewIs('laravel-events-calendar::organizers.edit')
                 ->assertStatus(200);
    }*/

    /* @test */
    /*public function it_updates_valid_organizer()
    {
        // https://www.neontsunami.com/posts/scaffolding-laravel-tests
        $organizer = factory(Organizer::class)->create();
        $attributes = factory(Organizer::class)->raw(['name' => 'Updated']);

        $user = User::first();
        auth()->login($user);

        $response = $this->put("/organizers/{$organizer->id}", $attributes);
        $response->assertRedirect('/organizers/');
        $this->assertEquals('Updated', $organizer->fresh()->name);
    }*/

    /* @test */
    /*public function it_does_not_update_invalid_organizer()
    {
        $organizer = factory(Organizer::class)->create(['name' => 'Example']);
        $response = $this->put("/organizers/{$organizer->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $organizer->fresh()->name);
    }*/

    /* @test */
    /*public function it_deletes_organizers()
    {
        $organizer = factory(Organizer::class)->create();
        $response = $this->delete("/organizers/{$organizer->id}");
        $response->assertRedirect('/organizers');
        $this->assertNull($organizer->fresh());
    }*/
}
