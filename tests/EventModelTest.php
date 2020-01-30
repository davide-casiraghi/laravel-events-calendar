<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use Illuminate\Foundation\Testing\WithFaker;

class EventModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_saves_monthly_repeat_same_day_number()
    {    
        $eventId = 1;
        $monthRepeatDatas = [0,25]; //the 28th day of the month
        $startDate = "2019-12-25";
        $repeatUntilDate = "2020-2-1";
        $timeStart = "10:00";
        $timeEnd = "11:00";
        
        Event::saveMonthlyRepeatDates($eventId, $monthRepeatDatas, $startDate, $repeatUntilDate, $timeStart, $timeEnd);
        
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2019-12-25 10:00:00', 'end_repeat' => '2019-12-25 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-01-25 10:00:00', 'end_repeat' => '2020-01-25 11:00:00']);
    }
    
    /** @test */
    public function it_saves_monthly_same_weekday_week_of_the_month()
    {    
        $eventId = 1;
        $monthRepeatDatas = [1,2,2]; // the 2nd Tuesday of the month
        $startDate = "2019-12-18";
        $repeatUntilDate = "2020-2-16";
        $timeStart = "10:00";
        $timeEnd = "11:00";
        
        Event::saveMonthlyRepeatDates($eventId, $monthRepeatDatas, $startDate, $repeatUntilDate, $timeStart, $timeEnd);
        
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2019-12-10 10:00:00', 'end_repeat' => '2019-12-10 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-01-14 10:00:00', 'end_repeat' => '2020-01-14 11:00:00']);
    }
    
    /** @test */
    public function it_saves_monthly_same_day_of_the_month_from_the_end()
    {    
        $eventId = 1;
        $monthRepeatDatas = [2,17]; // the 18th to last day of the month
        $startDate = "2020-02-12";
        $repeatUntilDate = "2020-04-28";
        $timeStart = "10:00";
        $timeEnd = "11:00";
        
        Event::saveMonthlyRepeatDates($eventId, $monthRepeatDatas, $startDate, $repeatUntilDate, $timeStart, $timeEnd);
        
        //$this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-02-12 10:00:00', 'end_repeat' => '2020-02-12 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-03-14 10:00:00', 'end_repeat' => '2020-03-14 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-04-13 10:00:00', 'end_repeat' => '2020-04-13 11:00:00']);
    }
    
    /** @test */
    public function it_saves_monthly_same_weekday_week_of_the_month_from_the_end()
    {    
        $eventId = 1;
        $monthRepeatDatas = [3,1,3]; // 3|1|3 the 2nd to last Wednesday of the month
        $startDate = "2020-02-19";
        $repeatUntilDate = "2020-04-28";
        $timeStart = "10:00";
        $timeEnd = "11:00";
        
        Event::saveMonthlyRepeatDates($eventId, $monthRepeatDatas, $startDate, $repeatUntilDate, $timeStart, $timeEnd);
        
        //$this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-02-19 10:00:00', 'end_repeat' => '2020-02-19 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-03-18 10:00:00', 'end_repeat' => '2020-03-18 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-04-22 10:00:00', 'end_repeat' => '2020-04-22 11:00:00']);
    }
    
    
}
