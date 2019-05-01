<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;

class EventCategoryControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_event_categories_index_page()
    {
        // Authenticate the admin
        //$this->authenticateAsAdmin();

        $this->get('eventCategories')->dump();
            //->assertViewIs('laravel-events-calendar::eventCategories.index')
            //->assertStatus(200);
    }

    /** @test */
    /*public function it_displays_the_event_category_create_page()
    {
        $this->get('eventCategories/create')
            ->assertViewIs('laravel-events-calendar::eventCategories.create')
            ->assertStatus(200);
    }*/

    /** @test */
    /*public function it_stores_a_valid_event_category()
    {
        $attributes = factory(EventCategory::class)->raw();

        $user = User::first();
        auth()->login($user);

        $response = $this->post('/eventCategories', $attributes);
        $eventCategory = EventCategory::first();

        //$this->assertDatabaseHas('eventCategories', $attributes);
        $response->assertRedirect('/eventCategories/');
    }*/

    /** @test */
    /*public function it_does_not_store_invalid_event_category()
    {
        $response = $this->post('/eventCategories', []);
        $response->assertSessionHasErrors();
        $this->assertNull(EventCategory::first());
    }*/

    /** @test */
    /*public function it_displays_the_event_category_show_page()
    {
        $eventCategory = factory(EventCategory::class)->create();
        $response = $this->get("/eventCategories/{$eventCategory->id}");
        $response->assertViewIs('laravel-events-calendar::eventCategories.show')
                 ->assertStatus(200);
    }*/

    /** @test */
    /*public function it_displays_the_event_category_edit_page()
    {
        $eventCategory = factory(EventCategory::class)->create();
        $response = $this->get("/eventCategories/{$eventCategory->id}/edit");
        $response->assertViewIs('laravel-events-calendar::eventCategories.edit')
                 ->assertStatus(200);
    }*/

    /** @test */
    /*public function it_updates_valid_event_category()
    {
        // https://www.neontsunami.com/posts/scaffolding-laravel-tests
        $eventCategory = factory(EventCategory::class)->create();
        $attributes = factory(EventCategory::class)->raw(['name' => 'Updated']);

        $user = User::first();
        auth()->login($user);

        $response = $this->put("/eventCategories/{$eventCategory->id}", $attributes);
        $response->assertRedirect('/eventCategories/');
        $this->assertEquals('Updated', $eventCategory->fresh()->name);
    }*/

    /** @test */
    /*public function it_does_not_update_invalid_event_category()
    {
        $eventCategory = factory(EventCategory::class)->create(['name' => 'Example']);
        $response = $this->put("/eventCategories/{$eventCategory->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $eventCategory->fresh()->name);
    }*/

    /** @test */
    /*public function it_deletes_event_categorys()
    {
        $eventCategory = factory(EventCategory::class)->create();
        $response = $this->delete("/eventCategories/{$eventCategory->id}");
        $response->assertRedirect('/eventCategories');
        $this->assertNull($eventCategory->fresh());
    }*/
}
