<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Carbon\Carbon;
//use Orchestra\Testbench\TestCase;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendarServiceProvider;
use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use Illuminate\Support\Facades\DB;

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
            \Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelEventsCalendar' => LaravelEventsCalendar::class, // facade called PhpResponsiveQuote and the name of the facade class
            'LaravelLocalization' => \Mcamara\LaravelLocalization\Facades\LaravelLocalization::class,
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

    /** @test */
    public function it_get_string_from_array_separated_by_comma()
    {
        $testArray = ['first', 'second', 'third'];
        $testString = LaravelEventsCalendar::getStringFromArraySeparatedByComma($testArray);

        $this->assertEquals($testString, 'first, second, third');
    }

    /** @test */
    public function it_check_is_weekday()
    {
        $date = '2019-05-03';
        $dayOfWeekValue = '5';
        $weekdayNumberOfMonth = LaravelEventsCalendar::isWeekDay($date, $dayOfWeekValue);
        $this->assertEquals($weekdayNumberOfMonth, true);

        $date = '2019-05-03';
        $dayOfWeekValue = '7';
        $weekdayNumberOfMonth = LaravelEventsCalendar::isWeekDay($date, $dayOfWeekValue);
        $this->assertEquals($weekdayNumberOfMonth, false);
    }

    /** @test */
    public function it_gets_number_of_the_specified_weekday_in_this_month()
    {
        $timestramp = '1286582400'; // timestamp of 10/09/2010
        $dayOfWeekValue = '3';
        $weekdayNumberOfMonth = LaravelEventsCalendar::weekdayNumberOfMonth($timestramp, $dayOfWeekValue);
        $this->assertEquals($weekdayNumberOfMonth, 1);
    }

    /** @test */
    public function it_gets_week_of_month_from_the_end()
    {
        $timestramp = '1286582400'; // timestamp of10/09/2010
        $weekOfTheMonthFromTheEnd = LaravelEventsCalendar::weekOfMonthFromTheEnd($timestramp);
        $this->assertEquals($weekOfTheMonthFromTheEnd, '4');
    }

    /** @test */
    public function it_gets_the_day_of_the_month_from_the_end()
    {
        $timestramp = '1286582400'; // timestamp of 10/09/2010
        $dayOfMonthFromTheEnd = LaravelEventsCalendar::dayOfMonthFromTheEnd($timestramp);
        $this->assertEquals($dayOfMonthFromTheEnd, 22);
    }

    /** @test */
    public function it_gets_ordinal_indicator()
    {
        $dayOfTheMonthNumber = '15';
        $ordinalIndicator = LaravelEventsCalendar::getOrdinalIndicator($dayOfTheMonthNumber);
        $this->assertEquals($ordinalIndicator, 'th');

        $dayOfTheMonthNumber = '1';
        $ordinalIndicator = LaravelEventsCalendar::getOrdinalIndicator($dayOfTheMonthNumber);
        $this->assertEquals($ordinalIndicator, 'st');
    }

    /** @test */
    public function it_decode_decode_repeat_weekly_on()
    {
        $repeatWeeklyOn = '1';
        $repeatWeeklyDecoded = LaravelEventsCalendar::decodeRepeatWeeklyOn($repeatWeeklyOn);
        $this->assertEquals($repeatWeeklyDecoded, 'Monday');
    }

    /** @test */
    public function it_decode_on_monthly_kind_string()
    {
        $onMonthlyKindString = '0|7';
        $onMonthlyKindDecoded = LaravelEventsCalendar::decodeOnMonthlyKind($onMonthlyKindString);
        $this->assertEquals($onMonthlyKindDecoded, 'the 7th day of the month');

        $onMonthlyKindString = '1|2|4';
        $onMonthlyKindDecoded = LaravelEventsCalendar::decodeOnMonthlyKind($onMonthlyKindString);
        $this->assertEquals($onMonthlyKindDecoded, 'the 2nd Thursday of the month');

        $onMonthlyKindString = '2|20';
        $onMonthlyKindDecoded = LaravelEventsCalendar::decodeOnMonthlyKind($onMonthlyKindString);
        $this->assertEquals($onMonthlyKindDecoded, 'the 21st to last day of the month');

        $onMonthlyKindString = '3|3|4';
        $onMonthlyKindDecoded = LaravelEventsCalendar::decodeOnMonthlyKind($onMonthlyKindString);
        $this->assertEquals($onMonthlyKindDecoded, 'the 4th to last Thursday of the month');
        
        $onMonthlyKindString = '3|0|5';
        $onMonthlyKindDecoded = LaravelEventsCalendar::decodeOnMonthlyKind($onMonthlyKindString);
        $this->assertEquals($onMonthlyKindDecoded, 'the last Friday of the month');
    }
}
