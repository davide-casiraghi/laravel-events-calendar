<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;

class EventControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_events_index_page()
    {
        $this->authenticate();
        $this->get('events')
            ->assertViewIs('laravel-events-calendar::events.index')
            ->assertStatus(200);
    }
    
    /** @test */
    public function it_displays_the_events_index_page_with_filter_selected()
    {
        $this->authenticate();
        
        $eventVenue = factory(EventVenue::class)->create();
        $eventAttributes = factory(Event::class)->raw([
            'title'=>'test title',
            'venue_id' => $eventVenue->id,
        ]);
        $response = $this->post('/events', $eventAttributes);

        $getAttributes = ([
            'keywords' => $eventAttributes['title'],
            'category_id' => $eventAttributes['category_id'],
            'country_id' => $eventVenue->country_id,
        ]);
        
        /*$this->get('events', $getAttributes)
            ->assertViewIs('laravel-events-calendar::events.index')
            ->assertStatus(200);*/
            
        $this->json('GET',  '/events',$getAttributes)
             ->assertViewIs('laravel-events-calendar::events.index')
             ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_event_create_page()
    {
        $this->authenticate();
        $this->get('events/create')
            ->assertViewIs('laravel-events-calendar::events.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_event_with_no_repetitions()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw(['title'=>'test title']);

        $response = $this->post('/events', $attributes);
        $response->assertRedirect('/events/');
        $this->assertDatabaseHas('events', ['title' => 'test title']);
    }

    /** @test */
    public function it_stores_a_valid_event_with_weekly_repetitions()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'repeat_type' => '2',
            'startDate' => '10/01/2020',
            'endDate' => '10/01/2020',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'repeat_until' => '10/10/2020',
            'repeat_weekly_on_day' => ['3', '6'],
        ]);

        $response = $this->post('/events', $attributes);
        $response->assertRedirect('/events/');
        $this->assertDatabaseHas('events', ['title' => 'test title']);
        $this->assertDatabaseHas('event_repetitions', ['id' => 1]);
        $this->assertDatabaseHas('event_repetitions', ['id' => 2]);
    }

    /** @test */
    public function it_stores_a_valid_event_with_monthly_repetitions_same_day_number()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'repeat_type' => '3',
            'startDate' => '10/01/2020',
            'endDate' => '10/01/2020',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'repeat_until' => '10/10/2020',
            'on_monthly_kind' => '0|7',
        ]);

        $response = $this->post('/events', $attributes);
        $response->assertRedirect('/events/');
        $this->assertDatabaseHas('events', ['title' => 'test title']);
        $this->assertDatabaseHas('event_repetitions', ['id' => 1]);
        $this->assertDatabaseHas('event_repetitions', ['id' => 2]);
    }

    /** @test */
    public function it_stores_a_valid_event_with_monthly_repetitions_same_weekday_week_of_the_month()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'repeat_type' => '3',
            'startDate' => '10/01/2020',
            'endDate' => '10/01/2020',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'repeat_until' => '10/10/2020',
            'on_monthly_kind' => '1|2|4',
        ]);

        $response = $this->post('/events', $attributes);
        $response->assertRedirect('/events/');
        $this->assertDatabaseHas('events', ['title' => 'test title']);
        $this->assertDatabaseHas('event_repetitions', ['id' => 1]);
        $this->assertDatabaseHas('event_repetitions', ['id' => 2]);
    }

    /** @test */
    public function it_stores_a_valid_event_with_monthly_repetitions_day_of_the_month()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'repeat_type' => '3',
            'startDate' => '10/01/2020',
            'endDate' => '10/01/2020',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'repeat_until' => '10/10/2020',
            'on_monthly_kind' => '2|17',
        ]);

        $response = $this->post('/events', $attributes);
        $response->assertRedirect('/events/');
        $this->assertDatabaseHas('events', ['title' => 'test title']);
        $this->assertDatabaseHas('event_repetitions', ['id' => 1]);
        $this->assertDatabaseHas('event_repetitions', ['id' => 2]);
    }

    /** @test */
    public function it_stores_a_valid_event_with_monthly_repetitions_same_weekday_week_of_the_month_from_end()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'repeat_type' => '3',
            'startDate' => '10/01/2020',
            'endDate' => '10/01/2020',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'repeat_until' => '10/10/2020',
            'on_monthly_kind' => '3|1|3',
        ]);

        $response = $this->post('/events', $attributes);
        $response->assertRedirect('/events/');
        $this->assertDatabaseHas('events', ['title' => 'test title']);
        $this->assertDatabaseHas('event_repetitions', ['id' => 1]);
        $this->assertDatabaseHas('event_repetitions', ['id' => 2]);
    }

    /** @test */
    /*
    public function it_stores_a_valid_event_with_multiple_days_repetitions()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
                        'title' => 'test title',
                        'repeat_type' => '3',
                        'startDate' => '10/01/2020',
                        'endDate' => '10/01/2020',
                        'time_start' => '10:00',
                        'time_end' => '12:00',
                        'repeat_until' => '10/10/2020',
                        'multiple_dates' => '.13/04/2020,14/04/2020,15/04/2020,16/04/2020,17/04/2020,18/04/2020,21/05/2020,22/05/2020,23/05/2020,24/05/2020,27/06/2020,28/06/2020,29/02/2020,01/03/2020',
                    ]);

                    $response = $this->post('/events', $attributes);
                    $response->assertRedirect('/events/');




        //$this->assertDatabaseHas('events', ['title' => 'test title']);
        //$this->assertDatabaseHas('event_repetitions', ['id' => 1]);
        //$this->assertDatabaseHas('event_repetitions', ['id' => 2]);
    }
    */

    /** @test */
    public function it_does_not_store_invalid_event()
    {
        $this->authenticate();
        $response = $this->post('/events', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Event::first());
    }

    /** @test */
    public function it_displays_the_event_edit_page()
    {
        $this->authenticateAsAdmin();
        $attributes = factory(Event::class)->raw();
        $this->post('/events', $attributes);
        //dd($attributes);
        //dd(app()->getLocale());

        $response = $this->get('/events/1/edit');
        $response->assertViewIs('laravel-events-calendar::events.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_doesnt_displays_the_event_edit_page_to_not_authenticated_user()
    {
        $attributes = factory(Event::class)->raw();
        $this->post('/events', $attributes);

        $response = $this->get('/events/1/edit');
        $response->assertStatus(500);
    }

    /** @test */
    public function it_updates_valid_event()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw();
        $this->post('/events', $attributes);

        $attributes['name'] = 'Updated';

        $response = $this->put('/events/1', $attributes);
        $response->assertRedirect('/events/');
        //$this->assertEquals('Updated', $event->fresh()->name);
    }

    /** @test */
    public function it_does_not_update_invalid_event()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw();
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
        $this->authenticate();
        $attributes = factory(Event::class)->raw(['slug'=>'test-slug']);
        $this->post('/events', $attributes);

        $eventSaved = Event::first();

        //$this->assertDatabaseHas('events', ['slug' => $eventSaved->slug]);

        $response = $this->get('/event/'.$eventSaved->slug);
        $response->assertViewIs('laravel-events-calendar::events.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_gets_an_event_by_slug_and_repetition()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw(['slug'=>'test-slug']);
        $this->post('/events', $attributes);

        $eventSaved = Event::first();
        $eventRepetitionSaved = EventRepetition::first();

        //$this->assertDatabaseHas('events', ['slug' => $eventSaved->slug]);

        $response = $this->get('/event/'.$eventSaved->slug.'/'.$eventRepetitionSaved->id);
        $response->assertViewIs('laravel-events-calendar::events.show')
                 ->assertStatus(200);

        // passing an eventRepetitionID that doesnt exist to trigger - If not found get the first repetion of the event in the future.
        $response = $this->get('/event/'.$eventSaved->slug.'/'.'222');
        $response->assertViewIs('laravel-events-calendar::events.show')
                ->assertStatus(200);
    }

    /** @test */
    public function it_gets_a_weekly_event_by_slug_and_repetition()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'repeat_type' => '2',   // weekly
            'startDate' => '10/01/2020',
            'endDate' => '10/01/2020',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'repeat_until' => '10/10/2020',
            'repeat_weekly_on_day' => ['3', '6'],
        ]);
        $this->post('/events', $attributes);

        $eventSaved = Event::first();
        $eventRepetitionSaved = EventRepetition::first();

        $response = $this->get('/event/'.$eventSaved->slug.'/'.$eventRepetitionSaved->id);
        $response->assertViewIs('laravel-events-calendar::events.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_gets_a_monthy_event_by_slug_and_repetition()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'repeat_type' => '3',   // monthy
            'startDate' => '10/01/2020',
            'endDate' => '10/01/2020',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'repeat_until' => '10/10/2020',
            'on_monthly_kind' => '0|7',
        ]);
        $this->post('/events', $attributes);

        $eventSaved = Event::first();
        $eventRepetitionSaved = EventRepetition::first();
        //dd($eventRepetitionSaved);
        $response = $this->get('/event/'.$eventSaved->slug.'/'.$eventRepetitionSaved->id);
        $response->assertViewIs('laravel-events-calendar::events.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_gets_a_multiple_days_event_by_slug_and_repetition()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw([
            'title' => 'test title',
            'repeat_type' => '4',   // multiple days
            'startDate' => '10/01/2020',
            'endDate' => '10/01/2020',
            'time_start' => '10:00',
            'time_end' => '12:00',
            'multiple_dates' => '11/03/2020,26/03/2020,21/03/2020',
        ]);
        $this->post('/events', $attributes);

        $eventSaved = Event::first();

        $eventRepetitionSaved = EventRepetition::first();

        $response = $this->get('/event/'.$eventSaved->slug.'/'.$eventRepetitionSaved->id);
        $response->assertViewIs('laravel-events-calendar::events.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_generate_monthly_select_options_html()
    {
        $request = $this->call('GET', '/event/monthSelectOptions', ['day' => '10/09/2010'])
            ->assertStatus(200)
            ->assertSee("<select name='on_monthly_kind' id='on_monthly_kind' class='selectpicker' title='Select start date first'><option value='0|10'>the 10th day of the month</option><option value='1|2|5'>the 2nd Friday of the month</option><option value='2|20'>the 21st to last day of the month</option><option value='3|2|5'>the 3rd to last Friday of the month</option></select>");
    }

    /** @test */
    public function it_displays_the_report_misuse_thankyou_page()
    {
        $this->get('/misuse/thankyou')
            ->assertViewIs('laravel-events-calendar::emails.report-thankyou')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_mail_to_organizer_sent()
    {
        $this->get('/mailToOrganizer/sent')
            ->assertViewIs('laravel-events-calendar::emails.contact.organizer-sent')
            ->assertStatus(200);
    }

    /** @test */
    /* Test in standby - because it fails on Scrutinizer */
    /*public function it_sends_mail_to_organizer()
    {
        $requestAttributes = [
            'user_email' => 'sender_email@testemail.com',
            'user_name' => 'Test user name',
            'subject' => 'Request from the Global CI Calendar',
            'message' => 'test message',
            'event_title' => 'Event test title',
            'event_id' => 1,
            'contact_email' => 'to_email@gmail.com',
        ];

        $request = $this->followingRedirects()
                        ->call('POST', '/mailToOrganizer', $requestAttributes)
                        ->assertViewIs('laravel-events-calendar::emails.contact.organizer-sent');
    }*/

    /** @test */
    /* Test in standby - because it fails on Scrutinizer */
    /*public function it_sends_mail_report_misuse()
    {
        $requestAttributes = [
            'reason' => '1',
            'message' => 'test message',
            'event_title' => 'Event test title',
            'event_id' => 1,
            'created_by' => 1,
        ];

        $request = $this->followingRedirects()
                        ->call('POST', '/misuse', $requestAttributes)
                        ->assertViewIs('laravel-events-calendar::emails.report-thankyou');

        $requestAttributes['reason'] = '2';
        $request = $this->followingRedirects()
                        ->call('POST', '/misuse', $requestAttributes)
                        ->assertViewIs('laravel-events-calendar::emails.report-thankyou');

        $requestAttributes['reason'] = '3';
        $request = $this->followingRedirects()
                        ->call('POST', '/misuse', $requestAttributes)
                        ->assertViewIs('laravel-events-calendar::emails.report-thankyou');

        $requestAttributes['reason'] = '4';
        $request = $this->followingRedirects()
                        ->call('POST', '/misuse', $requestAttributes)
                        ->assertViewIs('laravel-events-calendar::emails.report-thankyou');
    }*/

    /** @test */
    public function it_gets_the_event_repetitions()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw();
        $this->post('/events', $attributes);

        $eventCreated = Event::first();
        $eventRepetition = $eventCreated->eventRepetitions();

        $this->assertEquals($eventRepetition->first()->event_id, 1);
    }

    /** @test */
    /*public function it_gets_creator_email()
    {
        $this->authenticate();
        $attributes = factory(Event::class)->raw();
        $this->post('/events', $attributes);

        $eventCreated = Event::first();
        $eventRepetition = $eventCreated->eventRepetitions();

        $this->assertEquals($eventRepetition->first()->event_id, 1);
    }*/
}
