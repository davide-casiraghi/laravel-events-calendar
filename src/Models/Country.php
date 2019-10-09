<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /***************************************************************************/

    protected $fillable = [
        'name', 'code', 'continent_id',
    ];

    /***************************************************************************/

    /**
     * Return all the countries ordered by name.
     *
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Country
     */
    public static function getCountries()
    {
        $minutes = 15;
        $ret = Cache::remember('countries_list', $minutes, function () {
            return self::orderBy('name')->pluck('name', 'id');
        });

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return the all countries with active events.
     *
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Country
     */
    public static function getActiveCountries()
    {
        $cacheExpireMinutes = 15; // Set the duration time of the cache

        // All the countries
        $ret = Cache::remember('active_countries', $cacheExpireMinutes, function () {
            date_default_timezone_set('Europe/Rome');
            $searchStartDate = date('Y-m-d', time());
            $lastestEventsRepetitionsQuery = EventRepetition::getLastestEventsRepetitionsQuery($searchStartDate, null);

            return self::
            select('countries.*')
                ->join('event_venues', 'countries.id', '=', 'event_venues.country_id')
                ->join('events', 'event_venues.id', '=', 'events.venue_id')
                ->joinSub($lastestEventsRepetitionsQuery, 'event_repetitions', function ($join) {
                    $join->on('events.id', '=', 'event_repetitions.event_id');
                })
                ->orderBy('countries.name')
                ->get();
        });

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return the all active countries by continent.
     *
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Country
     */
    public static function getActiveCountriesByContinent($continent_id)
    {
        $activeCountries = self::getActiveCountries()->unique('name')->sortBy('name');
        $ret = $activeCountries->where('continent_id', $continent_id);

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return the all countries with teachers.
     *
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Country
     */
    public static function getCountriesWithTeachers()
    {
        $ret = self::join('teachers', 'countries.id', '=', 'teachers.country_id')
                      ->orderBy('countries.name')->pluck('countries.name', 'countries.id');

        return $ret;
    }

    /***************************************************************************/

    /*
     * Return active Continent and Countries JSON Tree (for hp select filters, vue component).
     *
     * @return string
     */
    /*public static function getActiveCountriesByContinent()
    {
        $minutes = 15;
        $ret = Cache::remember('active_continent_countries_json_tree', $minutes, function () {
            return Country::orderBy('name')->pluck('name', 'id');
        });

        return $ret;
    }*/
}
