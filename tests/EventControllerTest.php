<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Http\Controllers\EventController;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition;

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
        $user = User::first();
        auth()->login($user);
        $attributes = factory(Event::class)->raw(['title'=>'test title']);
        
        $response = $this->post('/events', $attributes);
        $response->assertRedirect('/events/');
        $this->assertDatabaseHas('events', ['title' => 'test title']);
    }

    /** @test */
    public function it_does_not_store_invalid_event()
    {
        $user = User::first();
        auth()->login($user);
        $response = $this->post('/events', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Event::first());
    }

    /** @test */
    public function it_displays_the_event_edit_page()
    {
        $attributes = factory(Event::class)->raw();
        $user = User::first();
        auth()->login($user);
        $this->post('/events', $attributes);

        //dd(app()->getLocale());

        $response = $this->get('/events/1/edit');
        $response->assertViewIs('laravel-events-calendar::events.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_event()
    {
        $attributes = factory(Event::class)->raw();
        $user = User::first();
        auth()->login($user);
        $this->post('/events', $attributes);

        $attributes['name'] = 'Updated';

        $response = $this->put('/events/1', $attributes);
        $response->assertRedirect('/events/');
        //$this->assertEquals('Updated', $event->fresh()->name);
    }

    /** @test */
    public function it_does_not_update_invalid_event()
    {
        $attributes = factory(Event::class)->raw();
        $user = User::first();
        auth()->login($user);
        $this->post('/events', $attributes);

        $response = $this->put('/events/1', []);
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function it_deletes_events()
    {
        $attributes = factory(Event::class)->raw();
        $user = User::first();
        auth()->login($user);
        $this->post('/events', $attributes);

        $response = $this->delete('/events/1');
        $response->assertRedirect('/events');
    }
    
    /** @test */
    public function it_gets_an_event_by_slug_and_test_event_show_single_repetition()
    {
        $user = User::first();
        auth()->login($user);
        $attributes = factory(Event::class)->raw(['slug'=>'test-slug']);
        $this->post('/events', $attributes);
        
        $eventSaved = Event::first();
        
        //$this->assertDatabaseHas('events', ['slug' => $eventSaved->slug]);
        
        $response = $this->get("/event/".$eventSaved->slug);
        $response->assertViewIs('laravel-events-calendar::events.show')
                 ->assertStatus(200);
    }
    
    /** @test */
    public function it_gets_an_event_by_slug_and_repetition()
    {
        $user = User::first();
        auth()->login($user);
        $attributes = factory(Event::class)->raw(['slug'=>'test-slug']);
        $this->post('/events', $attributes);
        
        $eventSaved = Event::first();
        $eventRepetitionSaved = EventRepetition::first();
        
        //$this->assertDatabaseHas('events', ['slug' => $eventSaved->slug]);
        
        $response = $this->get("/event/".$eventSaved->slug."/".$eventRepetitionSaved->id);
        $response->assertViewIs('laravel-events-calendar::events.show')
                 ->assertStatus(200);
    }
    
    
    
}
