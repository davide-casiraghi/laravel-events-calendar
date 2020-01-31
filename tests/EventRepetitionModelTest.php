<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition;
use Illuminate\Foundation\Testing\WithFaker;

class EventRepetitionModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_saves_event_repetition_on_db()
    {
        $eventId = 1;
        $dateStart = '2019-12-18';
        $dateEnd = '2019-12-18';
        $timeStart = '10:00';
        $timeEnd = '11:00';

        EventRepetition::saveEventRepetitionOnDB($eventId, $dateStart, $dateEnd, $timeStart, $timeEnd);

        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2019-12-18 10:00:00', 'end_repeat' => '2019-12-18 11:00:00']);
    }

    /***************************************************************/

    /** @test */
    public function it_saves_weekly_repeats_on_db()
    {
        $eventId = 1;
        $weekDays = [1, 4];
        $dateStart = '2019-12-1';
        $repeatUntilDate = '2020-12-15';
        $timeStart = '10:00';
        $timeEnd = '11:00';

        EventRepetition::saveWeeklyRepeatDates($eventId, $weekDays, $dateStart, $repeatUntilDate, $timeStart, $timeEnd);

        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2019-12-02 10:00:00', 'end_repeat' => '2019-12-02 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2019-12-05 10:00:00', 'end_repeat' => '2019-12-05 11:00:00']);
    }
    
    /***************************************************************/

    /** @test */
    public function it_saves_multiple_repeat_dates_on_db()
    {
        $eventId = 1;
        $singleDaysRepeatDatas = ['19/03/2020','20/05/2020'];
        $startDate = '2019-12-1';
        $timeStart = '10:00';
        $timeEnd = '11:00';

        EventRepetition::saveMultipleRepeatDates($eventId, $singleDaysRepeatDatas, $startDate, $timeStart, $timeEnd);

        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2019-12-01 10:00:00', 'end_repeat' => '2019-12-01 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-03-19 10:00:00', 'end_repeat' => '2020-03-19 11:00:00']);
        $this->assertDatabaseHas('event_repetitions', ['event_id' => $eventId, 'start_repeat' => '2020-05-20 10:00:00', 'end_repeat' => '2020-05-20 11:00:00']);
    }
    
}
