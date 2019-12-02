<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;

class EventCategoryControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_runs_the_test_factory()
    {
        $eventCategory = factory(EventCategory::class)->create([
                            'name' => 'Regular Jams',
                            'slug' => 'regular-jams',
                        ]);
        $this->assertDatabaseHas('event_category_translations', [
                                'locale' => 'en',
                                'name' => 'Regular Jams',
                                'slug' => 'regular-jams',
                ]);
    }

    /** @test */
    public function it_displays_the_event_categories_index_page()
    {
        $this->authenticateAsAdmin();
        $this->get('eventCategories')
            ->assertViewIs('laravel-events-calendar::eventCategories.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_event_category_create_page()
    {
        $this->authenticateAsAdmin();
        $this->get('eventCategories/create')
            ->assertViewIs('laravel-events-calendar::eventCategories.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_event_category()
    {
        /*$user = User::first();
        auth()->login($user);*/
        $this->authenticateAsAdmin();

        $data = [
            'name' => 'test title',
            'slug' => 'test body',
        ];

        $response = $this
            ->followingRedirects()
            ->post('/eventCategories', $data);

        $this->assertDatabaseHas('event_category_translations', ['locale' => 'en']);
        $response->assertViewIs('laravel-events-calendar::eventCategories.index');
    }

    /** @test */
    public function it_does_not_store_invalid_event_category()
    {
        $this->authenticateAsAdmin();
        $response = $this->post('/eventCategories', []);
        $response->assertSessionHasErrors();
        $this->assertNull(EventCategory::first());
    }

    /** @test */
    public function it_displays_the_event_category_show_page()
    {
        $this->authenticate();

        $eventCategory = factory(EventCategory::class)->create();
        $response = $this->get('/eventCategories/'.$eventCategory->id);
        $response->assertViewIs('laravel-events-calendar::eventCategories.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_event_category_edit_page()
    {
        $this->authenticateAsAdmin();

        $eventCategory = factory(EventCategory::class)->create();
        $response = $this->get("/eventCategories/{$eventCategory->id}/edit");
        $response->assertViewIs('laravel-events-calendar::eventCategories.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_event_category()
    {
        $this->authenticateAsAdmin();
        $eventCategory = factory(EventCategory::class)->create();

        $attributes = ([
            'name' => 'test name updated',
            'slug' => 'test slug updated',
          ]);

        $response = $this->followingRedirects()
                         ->put('/eventCategories/'.$eventCategory->id, $attributes);
        $response->assertViewIs('laravel-events-calendar::eventCategories.index')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_does_not_update_invalid_event_category()
    {
        $this->authenticateAsAdmin();

        $eventCategory = factory(EventCategory::class)->create();
        $response = $this->put('/eventCategories/'.$eventCategory->id, []);
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function it_deletes_event_categories()
    {
        $this->authenticateAsAdmin();

        $eventCategory = factory(EventCategory::class)->create();

        $response = $this->delete('/eventCategories/'.$eventCategory->id);
        $response->assertRedirect('/eventCategories');
    }
}
