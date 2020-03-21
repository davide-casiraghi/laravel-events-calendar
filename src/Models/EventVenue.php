<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Request; // to remove
use Illuminate\Support\Str;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;

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
        'name', 'slug', 'continent_id', 'country_id', 'region_id', 'city', 'state_province', 'address', 'zip_code', 'extra_info', 'lat', 'lng', 'description', 'website', 'created_by', 'created_at', 'updated_at',
    ];

    /***************************************************************************/

    /**
     * Get the user that owns the event. eg. $eventVenue->user.
     */
    public function user()
    {
        return $this->belongsTo('\Illuminate\Foundation\Auth\User', 'created_by');
    }

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
     * @return bool
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
    
    /***************************************************************************/

    /**
     * Prepare the record to be saved on DB.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function preSave(Request $request): void
    {
        $this->name = $request->get('name');
        $this->description = clean($request->get('description'));
        $this->continent_id = Country::where('id', $request->get('country_id'))->pluck('continent_id')->first();
        $this->country_id = $request->get('country_id');
        $this->region_id = $request->get('region_id');
        $this->city = $request->get('city');
        $this->address = $request->get('address');
        $this->zip_code = $request->get('zip_code');
        $this->extra_info = $request->get('extra_info');
        $this->website = $request->get('website');

        // Get GPS coordinates
        $address = Country::getCountryName($this->country_id).', '.$this->city.', '.$this->address;
        $gpsCoordinates = LaravelEventsCalendar::getVenueGpsCoordinates($address);
        $this->lat = $gpsCoordinates['lat'];
        $this->lng = $gpsCoordinates['lng'];

        if (!$this->slug) {
            $this->slug = Str::slug($this->name, '-').rand(10000, 100000);
        }

        //$eventVenue->created_by = Auth::id();
        $this->created_by = $request->get('created_by');
    }
    
}
