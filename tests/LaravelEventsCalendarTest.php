<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\LaravelEventsCalendar;
use PHPUnit\Framework\TestCase;

class LaravelEventsCalendarTest extends TestCase
{
    /** @test */
    public function it_prepare_start_date()
    {
        $startDate = '2019-4-1';
        
        //$startDatePrepared = "1-4-2019";
        $startDatePrepared = LaravelEventsCalendar::prepareStartDate(null);
        $this->assertEquals($startDatePrepared, '2019-04-01');
    }
}
