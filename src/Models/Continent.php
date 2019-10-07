<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

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
    public static function getContinents($country_id)
    {
        // All the contients
        if ($country_id == null){
            $minutes = 15;
            $ret = Cache::remember('continents_list', $minutes, function () {
                return self::orderBy('name')->pluck('name', 'id');
            });
        }
        // The contient of a specified country
        else {
            $ret = self::where('id', $country_id)->first();
            //firstWhere('id', $country_id);
                        //where('id', $country_id)->first();
                        //->pluck('name', 'id');
        }
            
        return $ret;
    }
}
