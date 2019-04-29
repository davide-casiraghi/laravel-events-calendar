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
            return Country::orderBy('name')->pluck('name', 'id');
        });

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return active Continent and Countries JSON Tree (for hp select filters, vue component).
     *
     * @return string
     */
    public static function getActiveCountriesByContinent()
    {
        $minutes = 15;
        $ret = Cache::remember('active_continent_countries_json_tree', $minutes, function () {
            return Country::orderBy('name')->pluck('name', 'id');
        });

        return $ret;
    }
}
