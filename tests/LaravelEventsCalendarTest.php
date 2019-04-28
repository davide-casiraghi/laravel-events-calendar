<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendarServiceProvider;
use Carbon\Carbon;


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
            'PhpResponsiveJumbotronImage' => PhpResponsiveJumbotronImage::class, // facade called PhpResponsiveQuote and the name of the facade class
        ];
    }

    /***************************************************************/

    /** @test */
    public function it_format_datepicker_date_for_mysql()
    {
        
        $todaysMysqlDateFormat = Carbon::now()->format('Y-m-d');

        $startDateMysqlDateFormat = LaravelEventsCalendar::formatDatePickerDateForMysql(null);
        $this->assertEquals($startDateMysqlDateFormat, $todaysMysqlDateFormat);

        $startDateFromDatePicker = Carbon::now()->format('d/m/Y');
        $startDateMysqlDateFormat = LaravelEventsCalendar::formatDatePickerDateForMysql($startDateFromDatePicker);
        $this->assertEquals($startDateMysqlDateFormat, $todaysMysqlDateFormat);
    }
}
