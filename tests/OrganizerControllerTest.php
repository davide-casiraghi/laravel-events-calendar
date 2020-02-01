<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Http\Controllers\OrganizerController;
use DavideCasiraghi\LaravelEventsCalendar\Models\Organizer;
use Illuminate\Foundation\Testing\WithFaker;

class OrganizerControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_organizers_index_page()
    {
        $this->authenticateAsAdmin();
        $this->get('organizers')
            ->assertViewIs('laravel-events-calendar::organizers.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_organizers_index_page_with_search_keywords()
    {
        $this->authenticateAsAdmin();
        $request = $this->call('GET', 'organizers', ['keywords' => 'test keywords'])
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_organizer_create_page()
    {
        $this->authenticateAsAdmin();
        $this->get('organizers/create')
            ->assertViewIs('laravel-events-calendar::organizers.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_organizer()
    {
        $this->authenticateAsAdmin();
        $attributes = factory(Organizer::class)->raw();

        $response = $this->post('/organizers', $attributes);
        $organizer = Organizer::first();

        //$this->assertDatabaseHas('organizers', $attributes);
        $response->assertRedirect('/organizers/');
    }

    /** @test */
    public function it_does_not_store_invalid_organizer()
    {
        $this->authenticateAsAdmin();
        $response = $this->post('/organizers', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Organizer::first());
    }

    /** @test */
    public function it_displays_the_organizer_show_page()
    {
        $this->authenticate();
        $organizer = factory(Organizer::class)->create();
        $response = $this->get("/organizers/{$organizer->id}");
        $response->assertViewIs('laravel-events-calendar::organizers.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_organizer_edit_page()
    {
        $this->authenticateAsAdmin();
        $organizer = factory(Organizer::class)->create();
        $response = $this->get("/organizers/{$organizer->id}/edit");
        $response->assertViewIs('laravel-events-calendar::organizers.edit')
                 ->assertStatus(200);
    }
    
    /** @test */
    public function it_doesnt_displays_the_organizer_edit_page_to_not_authenticated_user()
    {
        $organizer = factory(Organizer::class)->create();
        $response = $this->get("/organizers/{$organizer->id}/edit");
        $response->assertStatus(500);
    }

    /** @test */
    public function it_updates_valid_organizer()
    {
        $this->authenticateAsAdmin();
        // https://www.neontsunami.com/posts/scaffolding-laravel-tests
        $organizer = factory(Organizer::class)->create();
        $attributes = factory(Organizer::class)->raw(['name' => 'Updated']);

        $response = $this->put("/organizers/{$organizer->id}", $attributes);
        $response->assertRedirect('/organizers/');
        $this->assertEquals('Updated', $organizer->fresh()->name);
    }

    /** @test */
    public function it_does_not_update_invalid_organizer()
    {
        $this->authenticateAsAdmin();
        $organizer = factory(Organizer::class)->create(['name' => 'Example']);
        $response = $this->put("/organizers/{$organizer->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $organizer->fresh()->name);
    }

    /** @test */
    public function it_deletes_organizers()
    {
        $this->authenticateAsAdmin();
        $organizer = factory(Organizer::class)->create();
        $response = $this->delete("/organizers/{$organizer->id}");
        $response->assertRedirect('/organizers');
        $this->assertNull($organizer->fresh());
    }

    /** @test */
    public function it_opens_an_organizers_modal()
    {
        $this->authenticateAsAdmin();
        $this->get('/create-organizer/modal/')
            ->assertViewIs('laravel-events-calendar::organizers.modal')
            ->assertStatus(200);
    }

    /** @test */
    public function it_store_from_organizer_modal()
    {
        $this->authenticateAsAdmin();
        $request = new \Illuminate\Http\Request();

        $description = $this->faker->paragraph;
        $data = [
            'name' => $this->faker->name,
            'website' => $this->faker->url,
            'description' => $description,
            'email' => $this->faker->email,
            'phone' => $this->faker->e164PhoneNumber,
        ];
        $request->replace($data);

        $organizerController = new OrganizerController();
        $organizerController->storeFromModal($request);

        $data['description'] = clean($description);
        $this->assertDatabaseHas('organizers', $data);
    }
}
