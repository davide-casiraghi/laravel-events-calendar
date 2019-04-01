<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendar;

class LaravelEventsCalendarTest extends TestCase
{
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
