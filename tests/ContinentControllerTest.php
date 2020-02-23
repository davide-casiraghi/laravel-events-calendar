<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;

use Illuminate\Foundation\Testing\WithFaker;

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
    public function it_doesnt_displays_the_continent_edit_page_to_not_authenticated_user()
    {
        $continent = factory(Continent::class)->create();
        $response = $this->get("/continents/{$continent->id}/edit");
        $response->assertStatus(302)
                 ->assertRedirect('/');
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
    
    
    /** @test */
    public function it_updates_the_continents_dropdown()
    {
        $continent = factory(Continent::class)->create(['name' => 'Europe']);
        $country = factory(Country::class)->create(['name' => 'Italy', 'continent_id' => $continent->id]);
        
        $continent2 = factory(Continent::class)->create(['name' => 'Africa']);
        $country2 = factory(Country::class)->create(['name' => 'Morocco', 'continent_id' => $continent2->id]);

        // Select the first country and assert that the ID of the first continent is returned
        $response = $this->get("/update_continents_dropdown?country_id={$country->id}/");
        $response->assertStatus(200);
        $response->assertSee("1");
        
        // Select the second country and assert that the ID of the second continent is returned
        $response = $this->get("/update_continents_dropdown?country_id={$country2->id}/");
        $response->assertStatus(200);
        $response->assertSee("2");
    }
}
