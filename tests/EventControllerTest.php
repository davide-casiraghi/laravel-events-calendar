<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Http\Controllers\EventController;

use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategoryTranslation;

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

    /** @test */
    public function it_stores_a_valid_event()
    {   
        $eventCategoryId = EventCategory::insertGetId([
        ]);

        EventCategoryTranslation::insert([
            'event_category_id' => $eventCategoryId,
            'name' => 'test name',
            'slug' => 'test slug',
            'locale' => 'en',
        ]);
        
        $attributes = factory(Event::class)->raw();
        //dd($attributes);
        $user = User::first();
        auth()->login($user);

        $response = $this->post('/events', $attributes);
        $response->assertRedirect('/events/');
        //$this->assertDatabaseHas('events', $attributes);
        
    }

    /* @test */
    /*public function it_does_not_store_invalid_event()
    {
        $response = $this->post('/events', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Event::first());
    }*/

    /* @test */
    /*public function it_displays_the_event_show_page()
    {
        $event = factory(Event::class)->create();
        $response = $this->get("/events/{$event->id}");
        $response->assertViewIs('laravel-events-calendar::events.show')
                 ->assertStatus(200);
    }*/

    /* @test */
    /*public function it_displays_the_event_edit_page()
    {
        $event = factory(Event::class)->create();
        $response = $this->get("/events/{$event->id}/edit");
        $response->assertViewIs('laravel-events-calendar::events.edit')
                 ->assertStatus(200);
    }*/

    /* @test */
    /*public function it_updates_valid_event()
    {
        // https://www.neontsunami.com/posts/scaffolding-laravel-tests
        $event = factory(Event::class)->create();
        $attributes = factory(Event::class)->raw(['name' => 'Updated']);

        $user = User::first();
        auth()->login($user);

        $response = $this->put("/events/{$event->id}", $attributes);
        $response->assertRedirect('/events/');
        $this->assertEquals('Updated', $event->fresh()->name);
    }*/

    /* @test */
    /*public function it_does_not_update_invalid_event()
    {
        $event = factory(Event::class)->create(['name' => 'Example']);
        $response = $this->put("/events/{$event->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $event->fresh()->name);
    }*/

    /* @test */
    /*public function it_deletes_events()
    {
        $event = factory(Event::class)->create();
        $response = $this->delete("/events/{$event->id}");
        $response->assertRedirect('/events');
        $this->assertNull($event->fresh());
    }*/
}