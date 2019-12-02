<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Continent extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'continents';

    /***************************************************************************/

    protected $fillable = [
        'name', 'code',
    ];

    /***************************************************************************/

    /**
     * Return all the continents ordered by name.
     *
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Continent
     */
    public static function getContinents()
    {
        $minutes = 15;
        $ret = Cache::remember('continents_list', $minutes, function () {
            return self::orderBy('name')->pluck('name', 'id');
        });

        return $ret;
    }
}
