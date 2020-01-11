<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Database\Eloquent\Model;

class EventVenue extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_venues';

    /***************************************************************************/

    protected $fillable = [
        'name', 'slug', 'continent_id', 'country_id', 'region_id', 'city', 'state_province', 'address', 'zip_code', 'lat', 'lng', 'description', 'website', 'created_by', 'created_at', 'updated_at',
    ];

    /***************************************************************************/

    /**
     * Return the venue name - used by - /http/resources/event.php.
     *
     * @param int $venueId
     * @return string
     */
    public static function getVenueName($venueId)
    {
        $ret = self::find($venueId)->name;

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return true if the Venue contains any event.
     *
     * @param int $venueId
     * @return string
     */
    public static function venueContainsEvents($venueId)
    {
        $events = Event::where('venue_id', '=', $venueId)->first();
        if ($events === null) {
            $ret = false;
        } else {
            $ret = true;
        }

        return $ret;
    }
}
