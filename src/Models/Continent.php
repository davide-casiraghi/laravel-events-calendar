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
     * @return iterable
     */
    public static function getContinents(): iterable
    {
        $seconds = 86400; // One day
        $ret = Cache::remember('continents_list', $seconds, function () {
            return self::orderBy('name')->pluck('name', 'id');
        });

        return $ret;
    }
}
