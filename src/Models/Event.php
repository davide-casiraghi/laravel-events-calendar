<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Event extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'events';

    /***************************************************************************/

    protected $fillable = [
        'title',
        'description',
        'organized_by',
        'category_id',
        'venue_id',
        'image',
        'facebook_event_link',
        'website_event_link',
        'status',
        'repeat_type',
        'repeat_until',
        'repeat_weekly_on',
        'repeat_monthly_on',
        'on_monthly_kind',
        'multiple_dates',
    ];

    /***************************************************************************/

    /**
     * Get the teachers for the event.
     */
    public function teachers()
    {
        return $this->belongsToMany('DavideCasiraghi\LaravelEventsCalendar\Models\Teacher', 'event_has_teachers', 'event_id', 'teacher_id');
    }

    /***************************************************************************/

    /**
     * Get the organizers for the event.
     */
    public function organizers()
    {
        return $this->belongsToMany('DavideCasiraghi\LaravelEventsCalendar\Models\Organizer', 'event_has_organizers', 'event_id', 'organizer_id');
    }

    /***************************************************************************/

    /**
     * Get the organizers for the event.
     */
    public function eventRepetitions()
    {
        return $this->hasMany('DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition', 'event_id');
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
        EventRepetition::where('event_id', $eventId)->delete();
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
        $ret = EventRepetition::
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
        $ret = EventRepetition::
                select('start_repeat', 'end_repeat')
                ->where('id', $repetitionId)
                ->first();

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return the all the active events.
     *
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Event
     */
    public static function getActiveEvents()
    {
        $cacheExpireMinutes = 15; // Set the duration time of the cache

        $ret = Cache::remember('active_events', $cacheExpireMinutes, function () {
            date_default_timezone_set('Europe/Rome');
            $searchStartDate = date('Y-m-d', time());
            $lastestEventsRepetitionsQuery = EventRepetition::getLastestEventsRepetitionsQuery($searchStartDate, null);

            return self::
                        select('title', 'countries.name AS country_name', 'countries.id AS country_id', 'countries.continent_id AS continent_id', 'event_venues.city AS city')
                        ->join('event_venues', 'event_venues.id', '=', 'events.venue_id')
                        ->join('countries', 'countries.id', '=', 'event_venues.country_id')
                        ->joinSub($lastestEventsRepetitionsQuery, 'event_repetitions', function ($join) {
                            $join->on('events.id', '=', 'event_repetitions.event_id');
                        })
                        ->get();
        });

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return the active events based on the search keys provided.
     *
     * @param array $filters
     * @param int $itemPerPage
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Event
     */
    //$keywords, $category, $city, $country, $continent, $teacher, $venue, $startDate, $endDate,
    public static function getEvents(array $filters, $itemPerPage)
    {
        if (! array_key_exists('startDate', $filters) || ! $filters['startDate']) {
            $filters['startDate'] = Carbon::now()->format('Y-m-d');
        }

        // Sub-Query Joins - https://laravel.com/docs/5.7/queries
        $lastestEventsRepetitionsQuery = EventRepetition::getLastestEventsRepetitionsQuery($filters['startDate'], $filters['endDate']);

        // Retrieve the events that correspond to the selected filters
        if ($filters['keywords'] || $filters['category'] || $filters['city'] || $filters['country'] || $filters['region'] || $filters['continent'] || $filters['teacher'] || $filters['venue'] || $filters['endDate']) {

        //$start = microtime(true);
            //DB::enableQueryLog();
            $ret = self::
                    select('events.title', 'events.category_id', 'events.slug', 'event_venues.name as venue_name', 'event_venues.city as city_name', 'countries.name as country_name', 'events.sc_teachers_names', 'event_repetitions.start_repeat', 'event_repetitions.end_repeat')
                    ->when($filters['keywords'], function ($query, $keywords) {
                        return $query->where('title', 'like', '%'.$keywords.'%');
                    })
                    ->when($filters['category'], function ($query, $category) {
                        return $query->where('category_id', '=', $category);
                    })
                    ->when($filters['teacher'], function ($query, $teacher) {
                        return $query->whereRaw('json_contains(sc_teachers_id, \'["'.$teacher.'"]\')');
                    })
                    ->when($filters['country'], function ($query, $country) {
                        return $query->where('event_venues.country_id', '=', $country);
                    })
                    ->when($filters['region'], function ($query, $region) {
                        return $query->where('event_venues.region_id', '=', $region);
                    })
                    ->when($filters['continent'], function ($query, $continent) {
                        return $query->where('event_venues.continent_id', '=', $continent);  //sc_continent_id
                    })
                    ->when($filters['city'], function ($query, $city) {
                        return $query->where('event_venues.city', 'like', '%'.$city.'%');
                    })
                    ->when($filters['venue'], function ($query, $venue) {
                        return $query->where('event_venues.name', 'like', '%'.$venue.'%');
                    })
                    ->joinSub($lastestEventsRepetitionsQuery, 'event_repetitions', function ($join) {
                        $join->on('events.id', '=', 'event_repetitions.event_id');
                    })

                    ->leftJoin('event_venues', 'events.venue_id', '=', 'event_venues.id')
                    ->leftJoin('continents', 'event_venues.continent_id', '=', 'continents.id')
                    ->leftJoin('countries', 'event_venues.country_id', '=', 'countries.id')
                    ->leftJoin('regions', 'event_venues.region_id', '=', 'regions.id')
                    //->leftJoin('region_translations', 'regions.id', '=', 'region_translations.region_id')

                    ->orderBy('event_repetitions.start_repeat', 'asc')
                    ->paginate($itemPerPage);
        //dd(DB::getQueryLog());

        //$time = microtime(true) - $start;
        //dd($time);
        }
        // If no filter selected retrieve all the events
        else {
            $ret = self::
                        select('events.title', 'events.category_id', 'events.slug', 'event_venues.name as venue_name', 'event_venues.city as city_name', 'countries.name as country_name', 'events.sc_teachers_names', 'event_repetitions.start_repeat', 'event_repetitions.end_repeat')
                        ->joinSub($lastestEventsRepetitionsQuery, 'event_repetitions', function ($join) {
                            $join->on('events.id', '=', 'event_repetitions.event_id');
                        })
                        ->leftJoin('event_venues', 'events.venue_id', '=', 'event_venues.id')
                        ->leftJoin('continents', 'event_venues.continent_id', '=', 'continents.id')
                        ->leftJoin('countries', 'event_venues.country_id', '=', 'countries.id')
                        ->leftJoin('regions', 'event_venues.region_id', '=', 'regions.id')
                        ->leftJoin('region_translations', 'regions.id', '=', 'region_translations.region_id')

                        ->orderBy('event_repetitions.start_repeat', 'asc')
                        ->paginate($itemPerPage);

            // It works, but I don't use it now to develop
                /*$cacheExpireMinutes = 15;
                $events = Cache::remember('all_events', $cacheExpireTime, function () {
                    return DB::table('events')->latest()->paginate(20);
                });*/
        }

        return $ret;
    }

    /***************************************************************************/

    /*
     * Format the start date to be used in the search query.
     * If the start date is null return today's date.
     *
     * @param string $DatePickerStartDate
     * @return string
     */
    /*public static function prepareStartDate($DatePickerStartDate)
    {
        if ($DatePickerStartDate) {
            list($tid, $tim, $tiy) = explode('/', $DatePickerStartDate);
            $ret = "$tiy-$tim-$tid";
        } else {
            // If no start date selected the search start from today's date
            date_default_timezone_set('Europe/Rome');
            $ret = date('Y-m-d', time());
        }

        return $ret;
    }*/

    /***************************************************************************/

    /*
     * Format the end date to be used in the search query.
     *
     * @param string $DatePickerEndDate
     * @return string
     */
    /*public static function prepareEndDate($DatePickerEndDate)
    {
        if ($DatePickerEndDate) {
            list($tid, $tim, $tiy) = explode('/', $DatePickerEndDate);
            $ret = "$tiy-$tim-$tid";
        } else {
            $ret = null;
        }

        return $ret;
    }*/
}
