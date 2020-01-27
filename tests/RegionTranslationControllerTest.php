<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Region;
use Illuminate\Foundation\Testing\WithFaker;

class RegionTranslationControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_region_translation_create_page()
    {
        $this->authenticateAsAdmin();

        $regionId = 1;
        $languageCode = 'es';

        $this->get('/regionTranslations/'.$regionId.'/'.$languageCode.'/create')
            ->assertViewIs('laravel-events-calendar::regionTranslations.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_region_translation()
    {
        $this->authenticateAsAdmin();
        $region = factory(Region::class)->create([
            'name' => 'Lombardy',
        ]);

        $data = [
            'region_id' => $region->id,
            'language_code' => 'es',
            'name' => 'Catalunya',
        ];

        $response = $this
            ->followingRedirects()
            ->post('/regionTranslations/store', $data);

        $this->assertDatabaseHas('region_translations', ['locale' => 'es', 'name' => 'Catalunya']);
        $response->assertViewIs('laravel-events-calendar::regions.index');
    }

    /** @test */
    /*public function it_does_not_store_invalid_region_translation()
    {
        $this->authenticateAsAdmin();
        $response = $this
            ->followingRedirects()
            ->post('/regionTranslations/store', []);

        //$response->assertSessionHasErrors();
    }*/

    /** @test */
    public function it_displays_the_region_translation_edit_page()
    {
        $this->authenticateAsAdmin();
        $region = factory(Region::class)->create([
            'name' => 'Catalunya',
        ]);

        $data = [
            'region_id' => $region->id,
            'language_code' => 'es',
            'name' => 'Catalunya',
        ];

        $this->post('/regionTranslations/store', $data);

        $response = $this->get('/regionTranslations/'.$region->id.'/'.'es'.'/edit');
        $response->assertViewIs('laravel-events-calendar::regionTranslations.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_region_translation()
    {
        $this->authenticateAsAdmin();
        $region = factory(Region::class)->create([
            'name' => 'Catalunya',
        ]);

        $data = [
            'region_id' => $region->id,
            'language_code' => 'es',
            'name' => 'Catalunya',
        ];

        $this->post('/regionTranslations/store', $data);

        // Update the translation
        $attributes = ([
            'region_translation_id' => 2,
            'language_code' => 'es',
            'name' => 'Catalunya',
        ]);
        $response = $this->followingRedirects()
                         ->put('/regionTranslations/update', $attributes);
        $response->assertViewIs('laravel-events-calendar::regions.index')
                 ->assertStatus(200);
        $this->assertDatabaseHas('region_translations', ['locale' => 'es', 'name' => 'Catalunya']);

        // Update with no attributes - to not pass validation
        //$response = $this->followingRedirects()
                        // ->put('/regionTranslations/update', [])->dump();
                        // ->assertSessionHasErrors();
    }

    /** @test */
    /*public function it_does_not_update_invalid_region()
    {
        $this->authenticateAsAdmin();
        $region = factory(Region::class)->create([
                            'name' => 'Regular Jams',
                            'slug' => 'regular-jams',
                        ]);

        $data = [
            'region_id' => $region->id,
            'language_code' => 'es',
            'name' => 'Spanish category name',
        ];

        $this->post('/regionTranslations/store', $data);

        // Update the translation
        $attributes = ([
            'region_translation_id' => 2,
            'language_code' => 'es',
            'name' => '',
          ]);
        $response = $this->followingRedirects()
                         ->put('/regionTranslations/update', $attributes);
        $response->assertSessionHasErrors();
    }*/

    /** @test */
    public function it_deletes_region_translation()
    {
        $this->authenticateAsAdmin();
        $region = factory(Region::class)->create();

        $data = [
            'region_id' => $region->id,
            'language_code' => 'es',
            'name' => 'Catalunya',
        ];

        $this->post('/regionTranslations/store', $data);

        $response = $this->delete('/regionTranslations/destroy/2');
        $response->assertRedirect('/regions');
    }
}
