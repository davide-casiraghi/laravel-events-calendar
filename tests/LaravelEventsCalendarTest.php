<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendar;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class LaravelEventsCalendarTest extends TestCase
{
    /** @test */
    public function it_prepare_start_date()
    {    
        $todaysMysqlDateFormat = Carbon::now()->format('Y-m-d');
        
        $startDateMysqlDateFormat = LaravelEventsCalendar::prepareStartDate(null);
        $this->assertEquals($startDateMysqlDateFormat, $todaysMysqlDateFormat);
        
        $startDateFromDatePicker = Carbon::now()->format('d/m/Y');
        $startDateMysqlDateFormat = LaravelEventsCalendar::prepareStartDate($startDateFromDatePicker);
        $this->assertEquals($startDateMysqlDateFormat, $todaysMysqlDateFormat);
    }
}
