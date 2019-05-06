<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;

class ContinentControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_continents_index_page()
    {
        $this->authenticateAsAdmin();
        $this->get('continents')
            ->assertViewIs('laravel-events-calendar::continents.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_continent_create_page()
    {
        $this->authenticateAsAdmin();
        $this->get('continents/create')
            ->assertViewIs('laravel-events-calendar::continents.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_continent()
    {
        $this->authenticateAsAdmin();

        $attributes = factory(Continent::class)->raw();
        $response = $this->post('/continents', $attributes);
        $continent = Continent::first();

        //$this->assertDatabaseHas('continents', $attributes);
        $response->assertRedirect('/continents/');
    }

    /** @test */
    public function it_does_not_store_invalid_continent()
    {
        $this->authenticateAsAdmin();

        $response = $this->post('/continents', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Continent::first());
    }

    /** @test */
    public function it_displays_the_continent_show_page()
    {
        $this->authenticateAsAdmin();

        $continent = factory(Continent::class)->create();
        $response = $this->get("/continents/{$continent->id}");
        $response->assertViewIs('laravel-events-calendar::continents.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_continent_edit_page()
    {
        $this->authenticateAsAdmin();

        $continent = factory(Continent::class)->create();
        $response = $this->get("/continents/{$continent->id}/edit");
        $response->assertViewIs('laravel-events-calendar::continents.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_continent()
    {
        // https://www.neontsunami.com/posts/scaffolding-laravel-tests
        $this->authenticateAsAdmin();

        $continent = factory(Continent::class)->create();
        $attributes = factory(Continent::class)->raw(['name' => 'Updated']);

        $response = $this->put("/continents/{$continent->id}", $attributes);
        $response->assertRedirect('/continents/');
        $this->assertEquals('Updated', $continent->fresh()->name);
    }

    /** @test */
    public function it_does_not_update_invalid_continent()
    {
        $this->authenticateAsAdmin();

        $continent = factory(Continent::class)->create(['name' => 'Example']);
        $response = $this->put("/continents/{$continent->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $continent->fresh()->name);
    }

    /** @test */
    public function it_deletes_continents()
    {
        $this->authenticateAsAdmin();

        $continent = factory(Continent::class)->create();
        $response = $this->delete("/continents/{$continent->id}");
        $response->assertRedirect('/continents');
        $this->assertNull($continent->fresh());
    }
}
