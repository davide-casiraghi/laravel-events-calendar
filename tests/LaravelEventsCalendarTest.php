<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Capsule\Manager as Capsule;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendar;

class LaravelEventsCalendarTest extends TestCase
{
    /**
     * Create the tables this model needs for testing.
     */
    /*public static function setUpBeforeClass() : void
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        Capsule::schema()->create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id');
            $table->integer('created_by')->nullable();

            $table->string('title');
            $table->text('description');
            $table->string('image')->nullable();
            $table->integer('venue_id');
            $table->integer('organized_by')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('website_event_link')->nullable();
            $table->string('facebook_event_link')->nullable();
            $table->string('status')->default('2')->nullable();

            $table->integer('repeat_type');
            $table->dateTime('repeat_until')->nullable();
            $table->string('repeat_weekly_on')->nullable();
            $table->string('repeat_monthly_on')->nullable();
            $table->string('on_monthly_kind')->nullable();

            $table->integer('sc_country_id')->nullable();
            $table->string('sc_country_name')->nullable();
            $table->string('sc_city_name')->nullable();
            $table->string('sc_venue_name')->nullable();
            $table->string('sc_teachers_id')->nullable();
            $table->string('sc_teachers_names')->nullable();
            $table->integer('sc_continent_id')->nullable();

            $table->string('slug');

            $table->timestamps();
        });

        Event::create([
            'title' => 'aaa',
            'category_id' => 1,
            'description' => 'aaa',
            'venue_id' => 1,
            'repeat_type' => 1,
            'slug' => 'aaa',
        ]);

    }*/

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
