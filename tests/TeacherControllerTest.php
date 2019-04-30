<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendarServiceProvider;

//use DavideCasiraghi\LaravelEventsCalendar\Http\Controllers\JumbotronImageController;

class TeacherControllerTest extends TestCase
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

        //$this->artisan('db:seed', ['--class' => 'ContinentsTableSeeder']);
        //$this->artisan('db:seed', ['--database'=>'testbench','--class'=>'ContinentsTableSeeder']);

        //$this->artisan('db:seed', ['--database'=>'testbench','--class'=>'LaravelEventsCalendar\\LaravelEventsCalendar\\ContinentsTableSeeder']);
        //$this->artisan('db:seed', ['--database'=>'testbench','--class'=>'ContinentsTableSeeder', '--path'=>'/database/seeds/']);
        //$this->seed('ContinentsTableSeeder');
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
    public function it_displays_the_teachers_index_page()
    {
        // Authenticate the admin
        //$this->authenticateAsAdmin();

        $this->get('teachers')
            ->assertViewIs('laravel-events-calendar::teachers.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_teacher_create_page()
    {
        $this->get('teachers/create')
            ->assertViewIs('laravel-events-calendar::teachers.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_teacher()
    {
        $attributes = factory(Teacher::class)->raw();
        $response = $this->post('/teachers', $attributes);
        $teacher = Teacher::first();

        //$this->assertDatabaseHas('teachers', $attributes);
        $response->assertRedirect('/teachers/');
    }

    /** @test */
    public function it_does_not_store_invalid_teacher()
    {
        $response = $this->post('/teachers', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Teacher::first());
    }

    /** @test */
    public function it_displays_the_teacher_show_page()
    {
        $teacher = factory(Teacher::class)->create();
        $response = $this->get("/teachers/{$teacher->id}");
        $response->assertViewIs('laravel-events-calendar::teachers.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_teacher_edit_page()
    {
        $teacher = factory(Teacher::class)->create();
        $response = $this->get("/teachers/{$teacher->id}/edit");
        $response->assertViewIs('laravel-events-calendar::teachers.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_teacher()
    {
        // https://www.neontsunami.com/posts/scaffolding-laravel-tests
        $teacher = factory(Teacher::class)->create();
        $attributes = factory(Teacher::class)->raw(['name' => 'Updated']);
        $response = $this->put("/teachers/{$teacher->id}", $attributes);
        $response->assertRedirect('/teachers/');
        $this->assertEquals('Updated', $teacher->fresh()->name);
    }

    /** @test */
    public function it_does_not_update_invalid_teacher()
    {
        $teacher = factory(Teacher::class)->create(['name' => 'Example']);
        $response = $this->put("/teachers/{$teacher->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $teacher->fresh()->name);
    }

    /** @test */
    public function it_deletes_teachers()
    {
        $teacher = factory(Teacher::class)->create();
        $response = $this->delete("/teachers/{$teacher->id}");
        $response->assertRedirect('/teachers');
        $this->assertNull($teacher->fresh());
    }
}
