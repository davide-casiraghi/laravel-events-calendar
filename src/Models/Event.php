<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Carbon\Carbon;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // to remove
use Illuminate\Support\Str;

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
     * Get the user that owns the event. eg. $event->user.
     */
    public function user()
    {
        return $this->belongsTo('\Illuminate\Foundation\Auth\User', 'created_by');
    }

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
     * Return an array with active events datas.
     *
     * @return array
     */
    public static function getActiveEvents()
    {
        $seconds = 86400; // One day (this cache tag get invalidates also on event save)

        $ret = Cache::remember('active_events', $seconds, function () {
            date_default_timezone_set('Europe/Rome');
            $searchStartDate = date('Y-m-d', time());
            $lastestEventsRepetitionsQuery = EventRepetition::getLastestEventsRepetitionsQuery($searchStartDate, null);

            return self::
                    select('events.id as id', 'title', 'countries.name AS country_name', 'countries.id AS country_id', 'countries.continent_id AS continent_id', 'event_venues.city AS city', 'events.repeat_until', 'events.category_id', 'events.created_by', 'events.repeat_type')
                    ->join('event_venues', 'event_venues.id', '=', 'events.venue_id')
                    ->join('countries', 'countries.id', '=', 'event_venues.country_id')
                    ->joinSub($lastestEventsRepetitionsQuery, 'event_repetitions', function ($join) {
                        $join->on('events.id', '=', 'event_repetitions.event_id');
                    })
                    ->get();

            /* EVERY TIME THIS QUERY CHANGE REMEMBER TO FLUSH THE CACHE
            (php artisan cache:clear) */
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

    /**
     * Return a cached JSON with active events map markers.
     *
     * @return array
     */
    public static function getActiveEventsMapGeoJSON()
    {
        $cacheExpireMinutes = 1440; // Set the duration time of the cache (1 day - 1440 minutes) - this cache tag get invalidates also on event save

        $eventsMapGeoJSONArrayCached = Cache::remember('active_events_map_markers_json', $cacheExpireMinutes, function () {
            $eventsData = self::getActiveEventsMapMarkersDataFromDb();
            $eventsMapGeoJSONArray = [];
            foreach ($eventsData as $key => $eventData) {
                //dd($eventData);

                // Generates event link
                $nextEventRepetitionId = EventRepetition::getFirstEventRpIdByEventId($eventData->id);
                $eventLinkformat = 'event/%s/%s';   //event/{{$event->slug}}/{{$event->rp_id}}
                $eventLink = sprintf($eventLinkformat, $eventData->event_slug, $nextEventRepetitionId);

                // Get Next event occurrence date
                $nextDateOccurence = EventRepetition::getFirstFutureEventRpDatesByEventId($eventData->id);
                if (! empty($nextDateOccurence)) {
                    $nextDate = Carbon::parse($nextDateOccurence->start_repeat)->isoFormat('D MMM YYYY');
                } else {
                    $nextDate = '';
                }

                $address = (! empty($eventData->address)) ? ', '.$eventData->address : '';
                $city = (! empty($eventData->city)) ? $eventData->city : '';

                // Add one element to the Geo array
                $eventsMapGeoJSONArray[] = [
                    'type' => 'Feature',
                    'id' => $eventData->id,
                    'properties' => [
                        'Title' => $eventData->title,
                        'Category' => EventCategory::getCategoryName($eventData->category_id),
                        'VenueName' => EventVenue::getVenueName($eventData->venue_id),
                        'City' => $city,
                        'Address' => $address,
                        'Link' => $eventLink,
                        'NextDate' => $nextDate,
                        'IconColor' => LaravelEventsCalendar::getMapMarkerIconColor($eventData->category_id),
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$eventData->lng, $eventData->lat],
                    ],
                ];
            }

            /* EVERY TIME THIS CHANGE REMEMBER TO FLUSH THE CACHE
            (php artisan cache:clear) */

            return $eventsMapGeoJSONArray;
        });

        $ret = json_encode($eventsMapGeoJSONArrayCached);

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return an array with active events map markers.
     *
     * @return array
     */
    public static function getActiveEventsMapMarkersDataFromDb()
    {
        $seconds = 86400; // One day (this cache tag get invalidates also on event save)

        $ret = Cache::remember('active_events_map_markers_db_data', $seconds, function () {
            date_default_timezone_set('Europe/Rome');
            $searchStartDate = Carbon::now()->format('Y-m-d');
            $lastestEventsRepetitionsQuery = EventRepetition::getLastestEventsRepetitionsQuery($searchStartDate, null);

            return self::
                    select('events.id AS id',
                    'events.title AS title',
                    'events.slug AS event_slug',
                    'event_venues.id AS venue_id',
                    'event_venues.city AS city',
                    'event_venues.address AS address',
                    'event_venues.lat AS lat',
                    'event_venues.lng AS lng',
                    'events.repeat_until',
                    'events.category_id',
                    'events.created_by',
                    'events.repeat_type'
                    )
            ->join('event_venues', 'event_venues.id', '=', 'events.venue_id')
            ->join('countries', 'countries.id', '=', 'event_venues.country_id')
            ->joinSub($lastestEventsRepetitionsQuery, 'event_repetitions', function ($join) {
                $join->on('events.id', '=', 'event_repetitions.event_id');
            })
            ->get();

            /* EVERY TIME THIS QUERY CHANGE REMEMBER TO FLUSH THE CACHE
            (php artisan cache:clear) */
        });

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return true if an event is in the future.
     *
     * @return array
     */
    public function isActive(): bool
    {
        $firstEventRepetitionIdInFuture = EventRepetition::getFirstEventRpIdByEventId($this->id);

        if (! empty($firstEventRepetitionIdInFuture)) {
            return true;
        } else {
            return false;
        }
    }

    // **********************************************************************

    /**
     * Prepare the record to be saved on DB.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function preSave(Request $request): void
    {
        $teachers = Teacher::pluck('name', 'id');

        $this->title = $request->get('title');
        $this->description = clean($request->get('description'));

        if ($request->get('created_by')) {
            $this->created_by = $request->get('created_by');
        }

        if (! $this->slug) {
            $this->slug = Str::slug($this->title, '-').'-'.rand(100000, 1000000);
        }
        $this->category_id = $request->get('category_id');
        $this->venue_id = $request->get('venue_id');
        $this->image = $request->get('image');
        $this->contact_email = $request->get('contact_email');
        $this->website_event_link = $request->get('website_event_link');
        $this->facebook_event_link = $request->get('facebook_event_link');
        $this->status = $request->get('status');
        $this->on_monthly_kind = $request->get('on_monthly_kind');
        $this->multiple_dates = $request->get('multiple_dates');

        // Event teaser image upload
        if ($request->file('image')) {
            $imageFile = $request->file('image');
            $imageName = time().'.'.'jpg';  //$imageName = $teaserImageFile->hashName();
            $imageSubdir = 'events_teaser';
            $imageWidth = 968;
            $thumbWidth = 310;

            LaravelEventsCalendar::uploadImageOnServer($imageFile, $imageName, $imageSubdir, $imageWidth, $thumbWidth);
            $this->image = $imageName;
        } else {
            $this->image = $request->get('image');
        }

        // Support columns for homepage search (we need this to show events in HP with less use of resources)
         $this->sc_teachers_id = json_encode(explode(',', $request->get('multiple_teachers'))); // keep just this SC

         // Multiple teachers - populate support column field
        $this->sc_teachers_names = '';
        if ($request->get('multiple_teachers')) {
            $multiple_teachers = explode(',', $request->get('multiple_teachers'));

            $multiple_teachers_names = [];
            foreach ($multiple_teachers as $key => $teacher_id) {
                $multiple_teachers_names[] = $teachers[$teacher_id];
            }

            $this->sc_teachers_names .= LaravelEventsCalendar::getStringFromArraySeparatedByComma($multiple_teachers_names);
        }

        // Set the Event attributes about repeating (repeat until field and multiple days)
        $this->setEventRepeatFields($request);

        // Save event and repetitions
         //$this->save();
         //$this->saveEventRepetitions($request, $this->id);
    }

    /***************************************************************************/

    /**
     * Set the Event attributes about repeating before store or update (repeat until field and multiple days).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function setEventRepeatFields(Request $request)
    {
        // Set Repeat Until
        $this->repeat_type = $request->get('repeat_type');
        if ($request->get('repeat_until')) {
            $dateRepeatUntil = implode('-', array_reverse(explode('/', $request->get('repeat_until'))));
            $this->repeat_until = $dateRepeatUntil.' 00:00:00';
        }

        // Weekely - Set multiple week days
        if ($request->get('repeat_weekly_on_day')) {
            $repeat_weekly_on_day = $request->get('repeat_weekly_on_day');
            //dd($repeat_weekly_on_day);
            $i = 0;
            $len = count($repeat_weekly_on_day); // to put "," to all items except the last
            $this->repeat_weekly_on = '';
            foreach ($repeat_weekly_on_day as $key => $weeek_day) {
                $this->repeat_weekly_on .= $weeek_day;
                if ($i != $len - 1) {  // not last
                    $this->repeat_weekly_on .= ',';
                }
                $i++;
            }
        }
    }

    /***************************************************************************/
}
