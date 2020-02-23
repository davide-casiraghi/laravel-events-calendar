<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;

use DavideCasiraghi\LaravelEventsCalendar\Models\Region;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;

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
    public function it_doesnt_displays_the_region_edit_page_to_not_authenticated_user()
    {
        $region = factory(Region::class)->create();
        $response = $this->get("/regions/{$region->id}/edit");
        $response->assertStatus(302)
                 ->assertRedirect('/');
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
    
    /** @test */
    public function it_updates_the_regions_dropdown()
    {
        $continent = factory(Continent::class)->create(['name' => 'Europe']);
        $country = factory(Country::class)->create(['name' => 'Italy', 'continent_id' => $continent->id]);
        $region = factory(Region::class)->create(['name' => 'Lombardy', 'country_id' => $country->id]);
        
        // we need a venue with an event, because the dropdown shows just the active countries
        $eventVenue = factory(EventVenue::class)->create(['country_id' => $country->id, 'region_id' => $region->id]);
        $this->authenticate();
        $eventAttributes = factory(Event::class)->raw([
            'title'=>'event test title',
            'venue_id' => $eventVenue->id,
        ]);
        $response = $this->post('/events', $eventAttributes);
        
        // Get the list of the countries - The country should be present since has an event 
        $response = $this->get("/update_regions_dropdown?country_id={$country->id}/");
        $response->assertStatus(200);
        $response->assertSee("<select name='region_id' id='region_id' class='selectpicker' title='homepage-serach.select_a_region'><option value='1'>Lombardy</option></select>");
    }
}
