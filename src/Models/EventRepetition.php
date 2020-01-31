<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use Illuminate\Database\Eloquent\Model;

class EventRepetition extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_repetitions';

    /***************************************************************************/

    protected $fillable = [
        'event_id', 'start_repeat', 'end_repeat',
    ];

    /*public function user()
    {
        return $this->belongsTo('DavideCasiraghi\LaravelEventsCalendar\Models\Event', 'event_id', 'id');
    }*/

    /***************************************************************************/

    /**
     * Get for each event the first event repetition in the near future (JUST THE QUERY to use as SUBQUERY).
     * Parameters are Start date and End date of the interval
     * Return the query string,.
     * @param  string $searchStartDate
     * @param  string $searchEndDate
     * @return string
     */
    public static function getLastestEventsRepetitionsQuery($searchStartDate, $searchEndDate)
    {
        $ret = self::
                     selectRaw('event_id, MIN(id) AS rp_id, start_repeat, end_repeat')
                     ->when($searchStartDate, function ($query, $searchStartDate) {
                         return $query->where('event_repetitions.start_repeat', '>=', $searchStartDate);
                     })
                     ->when($searchEndDate, function ($query, $searchEndDate) {
                         return $query->where('event_repetitions.end_repeat', '<=', $searchEndDate);
                     })
                     ->groupBy('event_id');

        return $ret;
    }

    /***************************************************************************/

    /**
     * Save event repetition in the DB.
     * $dateStart and $dateEnd are in the format Y-m-d
     * $timeStart and $timeEnd are in the format H:i:s.
     * @param  int $eventId
     * @param  string $dateStart
     * @param  string $dateEnd
     * @param  string $timeStart
     * @param  string $timeEnd
     * @return void
     */
    public static function saveEventRepetitionOnDB(int $eventId, string $dateStart, string $dateEnd, string $timeStart, string $timeEnd)
    {
        $eventRepetition = new self();
        $eventRepetition->event_id = $eventId;

        $eventRepetition->start_repeat = Carbon::createFromFormat('Y-m-d H:i', $dateStart.' '.$timeStart);
        $eventRepetition->end_repeat = Carbon::createFromFormat('Y-m-d H:i', $dateEnd.' '.$timeEnd);

        $eventRepetition->save();
    }

    /***************************************************************************/

    /**
     * Save all the weekly repetitions in the event_repetitions table.
     * $dateStart and $dateEnd are in the format Y-m-d
     * $timeStart and $timeEnd are in the format H:i:s.
     * $weekDays - $request->get('repeat_weekly_on_day').
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @param  array|null  $weekDays
     * @param  string  $startDate
     * @param  string  $repeatUntilDate
     * @param  string  $timeStart
     * @param  string  $timeEnd
     * @return void
     */
    public static function saveWeeklyRepeatDates(int $eventId, array $weekDays, string $startDate, string $repeatUntilDate, string $timeStart, string $timeEnd)
    {
        $beginPeriod = Carbon::createFromFormat('Y-m-d', $startDate);
        $endPeriod = Carbon::createFromFormat('Y-m-d', $repeatUntilDate);
        //$interval = CarbonInterval::days(1);
        $interval = CarbonInterval::make('1day');
        $period = CarbonPeriod::create($beginPeriod, $interval, $endPeriod);
        foreach ($period as $day) {  // Iterate for each day of the period
            foreach ($weekDays as $weekDayNumber) { // Iterate for every day of the week (1:Monday, 2:Tuesday, 3:Wednesday ...)
                if (LaravelEventsCalendar::isWeekDay($day->format('Y-m-d'), $weekDayNumber)) {
                    self::saveEventRepetitionOnDB($eventId, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);
                }
            }
        }
    }

    /***************************************************************************/

    /**
     * Save all the weekly repetitions in the event_repetitions table
     * useful: http://thisinterestsme.com/php-get-first-monday-of-month/.
     *
     * @param  int  $eventId
     * @param  array   $monthRepeatDatas - explode of $request->get('on_monthly_kind')
     *                      0|28 the 28th day of the month
     *                      1|2|2 the 2nd Tuesday of the month
     *                      2|17 the 18th to last day of the month
     *                      3|1|3 the 2nd to last Wednesday of the month
     * @param  string  $startDate (Y-m-d)
     * @param  string  $repeatUntilDate (Y-m-d)
     * @param  string  $timeStart (H:i:s)
     * @param  string  $timeEnd (H:i:s)
     * @return void
     */
    public static function saveMonthlyRepeatDates(int $eventId, array $monthRepeatDatas, string $startDate, string $repeatUntilDate, string $timeStart, string $timeEnd)
    {
        $month = Carbon::createFromFormat('Y-m-d', $startDate);
        $end = Carbon::createFromFormat('Y-m-d', $repeatUntilDate);
        $weekdayArray = [Carbon::MONDAY, Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY];

        //$timeStart = $timeStart.":00";
        //$timeEnd = $timeEnd.":00";

        switch ($monthRepeatDatas[0]) {
            case '0':  // Same day number - eg. "the 28th day of the month"
                while ($month < $end) {
                    $day = $month;
                    //dump("ee_3");
                    //dump($timeStart);
                    self::saveEventRepetitionOnDB($eventId, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);
                    $month = $month->addMonth();
                }
                break;
            case '1':  // Same weekday/week of the month - eg. the "1st Monday"
                $numberOfTheWeek = $monthRepeatDatas[1]; // eg. 1(first) | 2(second) | 3(third) | 4(fourth) | 5(fifth)
                $weekday = $weekdayArray[$monthRepeatDatas[2] - 1]; // eg. monday | tuesday | wednesday

                while ($month < $end) {
                    $month_number = (int) Carbon::parse($month)->isoFormat('M');
                    $year_number = (int) Carbon::parse($month)->isoFormat('YYYY');

                    $day = Carbon::create($year_number, $month_number, 30, 0, 0, 0)->nthOfMonth($numberOfTheWeek, $weekday);  // eg. Carbon::create(2014, 5, 30, 0, 0, 0)->nthOfQuarter(2, Carbon::SATURDAY);
                    //dump("ee_4");
                    self::saveEventRepetitionOnDB($eventId, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);

                    $month = $month->addMonth();
                }
                break;
            case '2':  // Same day of the month (from the end) - the 3rd to last day (0 if last day, 1 if 2nd to last day, 2 if 3rd to last day)
                $dayFromTheEnd = $monthRepeatDatas[1];
                while ($month < $end) {
                    $month_number = (int) Carbon::parse($month)->isoFormat('M');
                    $year_number = (int) Carbon::parse($month)->isoFormat('YYYY');

                    $day = Carbon::create($year_number, $month_number, 1, 0, 0, 0)->lastOfMonth()->subDays($dayFromTheEnd);

                    self::saveEventRepetitionOnDB($eventId, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);
                    $month = $month->addMonth();
                }
                break;
            case '3':  // Same weekday/week of the month (from the end) - the last Friday - (0 if last Friday, 1 if the 2nd to last Friday, 2 if the 3nd to last Friday)
                $weekday = $weekdayArray[$monthRepeatDatas[2] - 1]; // eg. monday | tuesday | wednesday
                $weeksFromTheEnd = $monthRepeatDatas[1];

                while ($month < $end) {
                    $month_number = (int) Carbon::parse($month)->isoFormat('M');
                    $year_number = (int) Carbon::parse($month)->isoFormat('YYYY');

                    $day = Carbon::create($year_number, $month_number, 1, 0, 0, 0)->lastOfMonth($weekday)->subWeeks($weeksFromTheEnd);
                    //dump("ee_2");
                    self::saveEventRepetitionOnDB($eventId, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);
                    $month = $month->addMonth();
                }
                break;
        }
    }

    /***************************************************************************/

    /**
     * Save multiple single dates in the event_repetitions table
     * useful: http://thisinterestsme.com/php-get-first-monday-of-month/.
     * $singleDaysRepeatDatas - explode of $request->get('multiple_dates') - eg. ["19/03/2020","20/05/2020","29/05/2020"]
     * $startDate (Y-m-d)
     * $timeStart (H:i:s)
     * $timeEnd (H:i:s).
     *
     * @param  int  $eventId
     * @param  array   $singleDaysRepeatDatas
     * @param  string  $startDate
     * @param  string  $timeStart
     * @param  string  $timeEnd
     * @return void
     */
    public static function saveMultipleRepeatDates(int $eventId, array $singleDaysRepeatDatas, string $startDate, string $timeStart, string $timeEnd)
    {
        $day = Carbon::createFromFormat('Y-m-d', $startDate);

        self::saveEventRepetitionOnDB($eventId, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);

        foreach ($singleDaysRepeatDatas as $key => $singleDayRepeatDatas) {
            $day = Carbon::createFromFormat('d/m/Y', $singleDayRepeatDatas);

            self::saveEventRepetitionOnDB($eventId, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);
        }
    }

    /***************************************************************************/

    /**
     * Delete all the previous repetitions from the event_repetitions table.
     *
     * @param int $eventId
     * @return void
     */
    public static function deletePreviousRepetitions($eventId)
    {
        self::where('event_id', $eventId)->delete();
    }

    /***************************************************************************/

    /**
     * Return Start and End dates of the first repetition of an event - By Event ID.
     *
     * @param int $eventId
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition
     */
    public static function getFirstEventRpDatesByEventId($eventId)
    {
        $ret = self::
                select('start_repeat', 'end_repeat')
                ->where('event_id', $eventId)
                ->first();

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return Start and End dates of the first repetition of an event - By Repetition ID.
     *
     * @param int $repetitionId
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition
     */
    public static function getFirstEventRpDatesByRepetitionId($repetitionId)
    {
        $ret = self::
                select('start_repeat', 'end_repeat')
                ->where('id', $repetitionId)
                ->first();

        return $ret;
    }
}
