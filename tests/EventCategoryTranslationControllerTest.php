<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;

class EventCategoryTranslationControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_event_category_translation_create_page()
    {
        $user = User::first();
        auth()->login($user);

        $eventCategoryId = 1;
        $languageCode = 'es';

        $this->get('/eventCategoryTranslations/'.$eventCategoryId.'/'.$languageCode.'/create')
            ->assertViewIs('laravel-events-calendar::eventCategoryTranslations.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_event_category_translation()
    {
        $user = User::first();
        auth()->login($user);

        $eventCategory = factory(EventCategory::class)->create([
                            'name' => 'Regular Jams',
                            'slug' => 'regular-jams',
                        ]);

        $data = [
            'event_category_id' => $eventCategory->id,
            'language_code' => 'es',
            'name' => 'Spanish category name',
        ];

        $response = $this
            ->followingRedirects()
            ->post('/eventCategoryTranslations/store', $data);

        $this->assertDatabaseHas('event_category_translations', ['locale' => 'es', 'name' => 'Spanish category name']);
        $response->assertViewIs('laravel-events-calendar::eventCategories.index');
    }

    /** @test */
    public function it_does_not_store_invalid_event_category_translation()
    {
        $user = User::first();
        auth()->login($user);

        $response = $this
            ->followingRedirects()
            ->post('/eventCategoryTranslations/store', []);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function it_displays_the_event_category_translation_edit_page()
    {
        $user = User::first();
        auth()->login($user);

        $eventCategory = factory(EventCategory::class)->create([
                            'name' => 'Regular Jams',
                            'slug' => 'regular-jams',
                        ]);

        $data = [
            'event_category_id' => $eventCategory->id,
            'language_code' => 'es',
            'name' => 'Spanish category name',
        ];

        $this->post('/eventCategoryTranslations/store', $data);

        $response = $this->get('/eventCategoryTranslations/'.$eventCategory->id.'/'.'es'.'/edit');
        $response->assertViewIs('laravel-events-calendar::eventCategoryTranslations.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_event_category_translation()
    {
        $user = User::first();
        auth()->login($user);

        $eventCategory = factory(EventCategory::class)->create([
                            'name' => 'Regular Jams',
                            'slug' => 'regular-jams',
                        ]);

        $data = [
            'event_category_id' => $eventCategory->id,
            'language_code' => 'es',
            'name' => 'Spanish category name',
        ];

        $this->post('/eventCategoryTranslations/store', $data);

        $attributes = ([
            'event_category_translation_id' => 2,
            'language_code' => 'es',
            'name' => 'Spanish category name updated',
          ]);

        $response = $this->followingRedirects()
                         ->put('/eventCategoryTranslations/update', $attributes);
        $response->assertViewIs('laravel-events-calendar::eventCategories.index')
                 ->assertStatus(200);
        $this->assertDatabaseHas('event_category_translations', ['locale' => 'es', 'name' => 'Spanish category name updated']);
    }

    /** @test */
    /*public function it_does_not_update_invalid_event_category()
    {
        $user = User::first();
        auth()->login($user);

        $eventCategory = factory(EventCategory::class)->create();
        $response = $this->put('/eventCategories/'.$eventCategory->id, []);
        $response->assertSessionHasErrors();
    }*/

    /** @test */
    public function it_deletes_event_category_translation()
    {
        $user = User::first();
        auth()->login($user);

        $eventCategory = factory(EventCategory::class)->create();

        $data = [
            'event_category_id' => $eventCategory->id,
            'language_code' => 'es',
            'name' => 'Spanish category name',
        ];

        $this->post('/eventCategoryTranslations/store', $data);

        $response = $this->delete('/eventCategoryTranslations/destroy/2');
        $response->assertRedirect('/eventCategories');
    }
}
