<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Carbon\Carbon;
//use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendarServiceProvider;

class LaravelEventsCalendarTest extends TestCase
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

        //$this->artisan('db:seed', ['--class' => 'ContinentsTableSeeder']);
        //$this->artisan('db:seed', ['--database'=>'testbench','--class'=>'ContinentsTableSeeder']);
        // "php artisan db:seed --class='Hsd\\Catalog\\CatalogSeeder' --database='codeception'"

        //$this->artisan('db:seed', ['--database'=>'testbench','--class'=>'LaravelEventsCalendar\\LaravelEventsCalendar\\ContinentsTableSeeder']);
        //$this->artisan('db:seed', ['--database'=>'testbench','--class'=>'ContinentsTableSeeder', '--path'=>'/database/seeds/']);

        //$this->seed('ContinentsTableSeeder');
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
    public function it_runs_the_migrations()
    {

        // Shows all the tables in the sqlite DB
        /*$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
        $tables = array_map('current',$tables);
        dd($tables);*/

        Teacher::insert([
            'name' => 'John Smith',
            'slug' => 'john_smith',
        ]);

        $teacher = Teacher::where('name', '=', 'John Smith')->first();

        $this->assertEquals('John Smith', $teacher->name);
    }

    /** @test */
    public function it_format_datepicker_date_for_mysql()
    {
        $todaysMysqlDateFormat = Carbon::now()->format('Y-m-d');

        $startDateMysqlDateFormat = LaravelEventsCalendar::formatDatePickerDateForMysql(null, 1);
        $this->assertEquals($startDateMysqlDateFormat, $todaysMysqlDateFormat);
        
        $startDateMysqlDateFormat = LaravelEventsCalendar::formatDatePickerDateForMysql(null, 0);
        $this->assertEquals($startDateMysqlDateFormat, null);

        $startDateFromDatePicker = Carbon::now()->format('d/m/Y');
        $startDateMysqlDateFormat = LaravelEventsCalendar::formatDatePickerDateForMysql($startDateFromDatePicker, 1);
        $this->assertEquals($startDateMysqlDateFormat, $todaysMysqlDateFormat);
    }
}
