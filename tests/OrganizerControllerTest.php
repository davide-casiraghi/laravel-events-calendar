<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\Organizer;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\Http\Controllers\OrganizerController;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendarServiceProvider;

class OrganizerControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadLaravelMigrations(['--database' => 'testbench']);
        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelEventsCalendarServiceProvider::class,
            \Mews\Purifier\PurifierServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelEventsCalendar' => LaravelEventsCalendar::class, // facade called PhpResponsiveQuote and the name of the facade class
            'Purifier' => \Mews\Purifier\Facades\Purifier::class,
        ];
    }

    /***************************************************************/

    /** @test */
    public function it_displays_the_organizers_index_page()
    {
        // Authenticate the admin
        //$this->authenticateAsAdmin();

        $this->get('organizers')
            ->assertViewIs('laravel-events-calendar::organizers.index')
            ->assertStatus(200);
    }

    /** @test */
    /*public function it_displays_the_organizer_create_page()
    {
        $this->get('organizers/create')
            ->assertViewIs('laravel-events-calendar::organizers.create')
            ->assertStatus(200);
    }*/

    /** @test */
    /*public function it_stores_a_valid_organizer()
    {
        $attributes = factory(Organizer::class)->raw();
        $response = $this->post('/organizers', $attributes);
        $organizer = Organizer::first();

        //$this->assertDatabaseHas('organizers', $attributes);
        $response->assertRedirect('/organizers/');
    }*/

    /** @test */
    /*public function it_does_not_store_invalid_organizer()
    {
        $response = $this->post('/organizers', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Organizer::first());
    }*/

    /** @test */
    /*public function it_displays_the_organizer_show_page()
    {
        $organizer = factory(Organizer::class)->create();
        $response = $this->get("/organizers/{$organizer->id}");
        $response->assertViewIs('laravel-events-calendar::organizers.show')
                 ->assertStatus(200);
    }*/

    /** @test */
    /*public function it_displays_the_organizer_edit_page()
    {
        $organizer = factory(Organizer::class)->create();
        $response = $this->get("/organizers/{$organizer->id}/edit");
        $response->assertViewIs('laravel-events-calendar::organizers.edit')
                 ->assertStatus(200);
    }*/

    /** @test */
    /*public function it_updates_valid_organizer()
    {
        // https://www.neontsunami.com/posts/scaffolding-laravel-tests
        $organizer = factory(Organizer::class)->create();
        $attributes = factory(Organizer::class)->raw(['name' => 'Updated']);
        $response = $this->put("/organizers/{$organizer->id}", $attributes);
        $response->assertRedirect('/organizers/');
        $this->assertEquals('Updated', $organizer->fresh()->name);
    }*/

    /** @test */
    /*public function it_does_not_update_invalid_organizer()
    {
        $organizer = factory(Organizer::class)->create(['name' => 'Example']);
        $response = $this->put("/organizers/{$organizer->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $organizer->fresh()->name);
    }*/

    /** @test */
    /*public function it_deletes_organizers()
    {
        $organizer = factory(Organizer::class)->create();
        $response = $this->delete("/organizers/{$organizer->id}");
        $response->assertRedirect('/organizers');
        $this->assertNull($organizer->fresh());
    }*/

    /** @test */
    /*public function it_store_from_organizer_modal()
    {
        $request = new \Illuminate\Http\Request();

        $bio = $this->faker->paragraph;
        $data = [
            'name' => $this->faker->name,
            'bio' => $bio,
            'year_starting_practice' => '2000',
            'year_starting_teach' => '2006',
            'significant_organizers' => $this->faker->paragraph,
            'website' => $this->faker->url,
            'facebook' => 'https://www.facebook.com/'.$this->faker->word,
            'country_id' => $this->faker->numberBetween($min = 1, $max = 253),
          ];

        $request->replace($data);

        $organizerController = new OrganizerController();
        $organizerController->storeFromModal($request);

        $data['bio'] = clean($bio);
        $this->assertDatabaseHas('organizers', $data);
    }*/

    
}
