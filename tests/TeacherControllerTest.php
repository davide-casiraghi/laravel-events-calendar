<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Carbon\Carbon;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendarServiceProvider;
//use DavideCasiraghi\LaravelEventsCalendar\Http\Controllers\JumbotronImageController;

class TeacherControllerTest extends TestCase
{
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
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelEventsCalendar' => LaravelEventsCalendar::class, // facade called PhpResponsiveQuote and the name of the facade class
        
        ];
    }

    /***************************************************************/

    /** @test */
    public function the_route_teacher_index_can_be_accessed()
    {
        // Authenticate the admin
        //$this->authenticateAsAdmin();
        
        $this->get('teachers')
            ->assertViewIs('laravel-events-calendar::teachers.index')
            ->assertStatus(200);
    }

    /** @test */
    /*public function the_route_teacher_create_can_be_accessed()
    {
        $this->get('jumbotron-images/create')
            ->assertViewIs('laravel-events-calendar::teachers.create')
            ->assertStatus(200);
    }*/
}
