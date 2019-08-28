<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;

class EventCategoryTranslationControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_event_category_translation_create_page()
    {
        $this->authenticateAsAdmin();

        $eventCategoryId = 1;
        $languageCode = 'es';

        $this->get('/eventCategoryTranslations/'.$eventCategoryId.'/'.$languageCode.'/create')
            ->assertViewIs('laravel-events-calendar::eventCategoryTranslations.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_event_category_translation()
    {
        $this->authenticateAsAdmin();
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
    /*public function it_does_not_store_invalid_event_category_translation()
    {
        $this->authenticateAsAdmin();
        $response = $this
            ->followingRedirects()
            ->post('/eventCategoryTranslations/store', []);

        //$response->assertSessionHasErrors();
    }*/

    /** @test */
    public function it_displays_the_event_category_translation_edit_page()
    {
        $this->authenticateAsAdmin();
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
        $this->authenticateAsAdmin();
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

        // Update the translation
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

        // Update with no attributes - to not pass validation
        //$response = $this->followingRedirects()
                        // ->put('/eventCategoryTranslations/update', [])->dump();
                        // ->assertSessionHasErrors();
    }

    /** @test */
    /*public function it_does_not_update_invalid_event_category()
    {
        $this->authenticateAsAdmin();
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

        // Update the translation
        $attributes = ([
            'event_category_translation_id' => 2,
            'language_code' => 'es',
            'name' => '',
          ]);
        $response = $this->followingRedirects()
                         ->put('/eventCategoryTranslations/update', $attributes);
        $response->assertSessionHasErrors();
    }*/

    /** @test */
    public function it_deletes_event_category_translation()
    {
        $this->authenticateAsAdmin();
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
