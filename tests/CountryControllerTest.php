<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;

class CountryControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_countries_index_page()
    {
        // Authenticate the admin
        //$this->authenticateAsAdmin();

        $this->get('countries')
            ->assertViewIs('laravel-events-calendar::countries.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_countries_index_page_with_search_keywords()
    {
        // Authenticate the admin
        //$this->authenticateAsAdmin();

        $request = $this->call('GET', 'countries', ['keywords' => 'test keywords'])
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_country_create_page()
    {
        $this->get('countries/create')
            ->assertViewIs('laravel-events-calendar::countries.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_country()
    {
        $attributes = factory(Country::class)->raw();

        $user = User::first();
        auth()->login($user);

        $response = $this->post('/countries', $attributes);
        $country = Country::first();

        //$this->assertDatabaseHas('countries', $attributes);
        $response->assertRedirect('/countries/');
    }

    /** @test */
    public function it_does_not_store_invalid_country()
    {
        $response = $this->post('/countries', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Country::first());
    }

    /** @test */
    public function it_displays_the_country_show_page()
    {
        $country = factory(Country::class)->create();
        $response = $this->get("/countries/{$country->id}");
        $response->assertViewIs('laravel-events-calendar::countries.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_country_edit_page()
    {
        $country = factory(Country::class)->create();
        $response = $this->get("/countries/{$country->id}/edit");
        $response->assertViewIs('laravel-events-calendar::countries.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_country()
    {
        // https://www.neontsunami.com/posts/scaffolding-laravel-tests
        $country = factory(Country::class)->create();
        $attributes = factory(Country::class)->raw(['name' => 'Updated']);

        $user = User::first();
        auth()->login($user);

        $response = $this->put("/countries/{$country->id}", $attributes);
        $response->assertRedirect('/countries/');
        $this->assertEquals('Updated', $country->fresh()->name);
    }

    /** @test */
    public function it_does_not_update_invalid_country()
    {
        $country = factory(Country::class)->create(['name' => 'Example']);
        $response = $this->put("/countries/{$country->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $country->fresh()->name);
    }

    /** @test */
    public function it_deletes_countrys()
    {
        $country = factory(Country::class)->create();
        $response = $this->delete("/countries/{$country->id}");
        $response->assertRedirect('/countries');
        $this->assertNull($country->fresh());
    }
}
