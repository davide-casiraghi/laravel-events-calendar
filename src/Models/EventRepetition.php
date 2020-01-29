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

        //$eventRepetition->start_repeat = $dateStart.' '.$timeStart.':00';
        //$eventRepetition->end_repeat = $dateEnd.' '.$timeEnd.':00';

        //create($year = 0, $month = 1, $day = 1, $hour = 0, $minute = 0, $second = 0, $tz = null

        //$eventRepetition->start_repeat = Carbon::createFromFormat('Y-m-d H:i:s', $dateStart." ".$timeStart);
        //$eventRepetition->end_repeat = Carbon::createFromFormat('Y-m-d H:i:s', $dateEnd." ".$timeEnd);
        //dump($timeStart);
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
     * @param  array  $weekDays
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
        $interval = CarbonInterval::days(1);
        $period = CarbonPeriod::create($beginPeriod, $interval, $endPeriod);
        foreach ($period as $day) {  // Iterate for each day of the period
            foreach ($weekDays as $weekDayNumber) { // Iterate for every day of the week (1:Monday, 2:Tuesday, 3:Wednesday ...)
                if (LaravelEventsCalendar::isWeekDay($day->format('Y-m-d'), $weekDayNumber)) {
                    self::saveEventRepetitionOnDB($eventId, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);
                }
            }
        }
    }
}
