<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Region;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;

class RegionControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_runs_the_test_factory()
    {
        $region = factory(Region::class)->create([
            'name' => 'Lombardia',
            'slug' => 'lombardia',
        ]);
        $this->assertDatabaseHas('region_translations', [
            'locale' => 'en',
            'name' => 'Lombardia',
            'slug' => 'lombardia',
        ]);
    }

    /** @test */
    public function it_displays_the_regions_index_page()
    {
        $this->authenticateAsAdmin();
        $this->get('regions')
            ->assertViewIs('laravel-events-calendar::regions.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_region_create_page()
    {
        $this->authenticateAsAdmin();
        $this->get('regions/create')
            ->assertViewIs('laravel-events-calendar::regions.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_region()
    {
        /*$user = User::first();
        auth()->login($user);*/
        $this->authenticateAsAdmin();

        $data = [
            'name' => 'Lombardia',
            'slug' => 'lombardia',
            'country_id' => 1,
            'timezone' => '+2:00',
        ];

        $response = $this
            ->followingRedirects()
            ->post('/regions', $data);

        $this->assertDatabaseHas('region_translations', ['locale' => 'en']);
        $response->assertViewIs('laravel-events-calendar::regions.index');
    }

    /** @test */
    public function it_does_not_store_invalid_region()
    {
        $this->authenticateAsAdmin();
        $response = $this->post('/regions', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Region::first());
    }

    /** @test */
    public function it_displays_the_region_show_page()
    {
        $this->authenticate();

        $region = factory(Region::class)->create();
        $response = $this->get('/regions/'.$region->id);
        $response->assertViewIs('laravel-events-calendar::regions.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_region_edit_page()
    {
        $this->authenticateAsAdmin();

        $region = factory(Region::class)->create();
        $response = $this->get("/regions/{$region->id}/edit");
        $response->assertViewIs('laravel-events-calendar::regions.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_region()
    {
        $this->authenticateAsAdmin();
        $region = factory(Region::class)->create();

        $attributes = ([
            'name' => 'test name updated',
            'slug' => 'test slug updated',
            'country_id' => 1,
            'timezone' => '+2:00',
        ]);

        $response = $this->followingRedirects()
                         ->put('/regions/'.$region->id, $attributes);
        $response->assertViewIs('laravel-events-calendar::regions.index')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_does_not_update_invalid_region()
    {
        $this->authenticateAsAdmin();

        $region = factory(Region::class)->create();
        $response = $this->put('/regions/'.$region->id, []);
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function it_deletes_regions()
    {
        $this->authenticateAsAdmin();

        $region = factory(Region::class)->create();

        $response = $this->delete('/regions/'.$region->id);
        $response->assertRedirect('/regions');
    }
}
