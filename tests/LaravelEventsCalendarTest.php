<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Carbon\Carbon;
//use Orchestra\Testbench\TestCase;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendarServiceProvider;
use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $dateDatepickerFormat = '14/10/2019';
        $dateMysqlFormat = '2019-10-14';

        $outputFormatDatePickerDateForMysql = LaravelEventsCalendar::formatDatePickerDateForMysql($dateDatepickerFormat, 1);
        $this->assertEquals($outputFormatDatePickerDateForMysql, $dateMysqlFormat);

        $outputFormatDatePickerDateForMysql = LaravelEventsCalendar::formatDatePickerDateForMysql('', 1);
        $this->assertEquals($outputFormatDatePickerDateForMysql, $todaysMysqlDateFormat);

        $outputFormatDatePickerDateForMysql = LaravelEventsCalendar::formatDatePickerDateForMysql('', 0);
        $this->assertEquals($outputFormatDatePickerDateForMysql, '');
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
        $timestramp = '1569974400'; // timestamp of 2/10/2019
        $weekOfTheMonthFromTheEnd = LaravelEventsCalendar::weekOfMonthFromTheEnd($timestramp);
        $this->assertEquals($weekOfTheMonthFromTheEnd, '5');

        $timestramp = '1570579200'; // timestamp of 9/10/2019
        $weekOfTheMonthFromTheEnd = LaravelEventsCalendar::weekOfMonthFromTheEnd($timestramp);
        $this->assertEquals($weekOfTheMonthFromTheEnd, '4');

        $timestramp = '1571184000'; // timestamp of 16/10/2019
        $weekOfTheMonthFromTheEnd = LaravelEventsCalendar::weekOfMonthFromTheEnd($timestramp);
        $this->assertEquals($weekOfTheMonthFromTheEnd, '3');

        $timestramp = '1571788800'; // timestamp of 23/10/2019
        $weekOfTheMonthFromTheEnd = LaravelEventsCalendar::weekOfMonthFromTheEnd($timestramp);
        $this->assertEquals($weekOfTheMonthFromTheEnd, '2');

        $timestramp = '1572397200'; // timestamp of 30/10/2019
        $weekOfTheMonthFromTheEnd = LaravelEventsCalendar::weekOfMonthFromTheEnd($timestramp);
        $this->assertEquals($weekOfTheMonthFromTheEnd, '1');
    }

    /** @test */
    public function it_gets_the_day_of_the_month_from_the_end()
    {
        $timestramp = '1286582400'; // timestamp of 10/09/2010
        $dayOfMonthFromTheEnd = LaravelEventsCalendar::dayOfMonthFromTheEnd($timestramp);
        $this->assertEquals($dayOfMonthFromTheEnd, 22);
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

    /** @test */
    public function it_gets_venue_gps_coordinates()
    {
        // To test manually
        //https://developer.mapquest.com/documentation/tools/latitude-longitude-finder/
        
        $address = 'Italy, Milano, via Dante, 15';
        $venuesCoordinates = LaravelEventsCalendar::getVenueGpsCoordinates($address);
        $this->assertSame(intval($venuesCoordinates['lat']), 45);
        $this->assertSame(intval($venuesCoordinates['lng']), 9);

        // https://www.mapquestapi.com/geocoding/v1/address?key=Ad5KVnAISxX6aHyj6fAnHcKeh30n4W60&location=Germany,%20Hasenheide,%2054+Berlin
        $address = 'Germany, Berlin, Hasenheide, 54';
        $venuesCoordinates = LaravelEventsCalendar::getVenueGpsCoordinates($address);
        $this->assertSame(intval($venuesCoordinates['lat']), 52);
        $this->assertSame(intval($venuesCoordinates['lng']), 13);

        // https://www.mapquestapi.com/geocoding/v1/address?key=Ad5KVnAISxX6aHyj6fAnHcKeh30n4W60&location=Canada,Powell River+Lasqueti%20Island+V0R%202J0
        $address = 'Canada, Powell River, Lasqueti Island, V0R 2J0';
        $venuesCoordinates = LaravelEventsCalendar::getVenueGpsCoordinates($address);

        $this->assertSame(intval($venuesCoordinates['lat']), 49);
        $this->assertSame(intval($venuesCoordinates['lng']), -124);

        // https://www.mapquestapi.com/geocoding/v1/address?key=Ad5KVnAISxX6aHyj6fAnHcKeh30n4W60&location=Germany,Stuttgart+Mercedesstra%C3%9Fe,%209
        $address = 'Germany, Stuttgart, Mercedes Strasse, 9';
        $venuesCoordinates = LaravelEventsCalendar::getVenueGpsCoordinates($address);

        $this->assertSame(intval($venuesCoordinates['lat']), 48);
        $this->assertSame(intval($venuesCoordinates['lng']), 9);
    }

    /** @test */
    public function it_gets_report_misuse_reason_description()
    {
        $description = LaravelEventsCalendar::getReportMisuseReasonDescription(1);
        $this->assertSame($description, 'Not about Contact Improvisation');

        $description = LaravelEventsCalendar::getReportMisuseReasonDescription(2);
        $this->assertSame($description, 'Contains wrong informations');

        $description = LaravelEventsCalendar::getReportMisuseReasonDescription(3);
        $this->assertSame($description, 'It is not translated in english');

        $description = LaravelEventsCalendar::getReportMisuseReasonDescription(4);
        $this->assertSame($description, 'Other (specify in the message)');
    }

    /** @test */
    public function it_gets_collection_ids_separated_by_comma()
    {
        $teachers = collect();
        $teachers->add(factory(Teacher::class)->create());
        $teachers->add(factory(Teacher::class)->create());

        $stringWithIds = LaravelEventsCalendar::getCollectionIdsSeparatedByComma($teachers);
        $this->assertSame($stringWithIds, '1,2');
    }

    /** @test */
    public function it_gets_map_marker_color_icon()
    {
        $iconColor = LaravelEventsCalendar::getMapMarkerIconColor(3);
        $this->assertSame($iconColor, 'orangeIcon');
    }

    /** @test */
    public function it_cleans_string()
    {
        $cleanedString = LaravelEventsCalendar::cleanString('Köln');
        $this->assertSame($cleanedString, 'Koln');

        $cleanedString = LaravelEventsCalendar::cleanString('Højbjerg');
        $this->assertSame($cleanedString, 'Hojbjerg');
    }

    /** @test */
    public function it_uploads_an_image_on_server()
    {
        $this->authenticateAsAdmin();
        // Delete directory
        //dd(Storage::directories('public/images')); // List directories
        $directory = 'public/images/teachers_profile/';
        Storage::deleteDirectory($directory);

        // Symulate the upload
        $local_test_file = __DIR__.'/test-files/large-avatar.png';
        $uploadedFile = new \Illuminate\Http\UploadedFile(
                $local_test_file,
                'large-avatar.png',
                'image/png',
                null,
                0,
                true
            );

        // Call the function uploadImageOnServer()
        $imageFile = $uploadedFile;
        $imageName = $imageFile->hashName();
        $imageSubdir = 'teachers_profile';
        $imageWidth = 968;
        $thumbWidth = 300;

        LaravelEventsCalendar::uploadImageOnServer($imageFile, $imageName, $imageSubdir, $imageWidth, $thumbWidth);

        // Leave this lines here - they can be very useful for new tests
        //$directory = "/";
        //dump(Storage::allDirectories($directory));
        //dd(Storage::allFiles($directory));

        $filePath = 'public/images/'.$imageSubdir.'/'.$imageName;

        Storage::assertExists($filePath);
    }
}
