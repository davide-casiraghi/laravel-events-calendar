<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Console;

use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;
use Illuminate\Console\Command;

class RetrieveAllGpsCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retrieve-all-gps-coordinates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign to all the venues the corresponding GPS coordinates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param
     * @return mixed
     */
    public function handle()
    {
        $eventVenues = EventVenue::all();
        foreach ($eventVenues as $key => $eventVenue) {
            // Get GPS coordinates
            $address = Country::getCountryName($eventVenue->country_id).', '.$eventVenue->city.', '.$eventVenue->address;
            $gpsCoordinates = LaravelEventsCalendar::getVenueGpsCoordinates($address);
            $eventVenue->lat = $gpsCoordinates['lat'];
            $eventVenue->lng = $gpsCoordinates['lng'];
            $eventVenue->save();
        }
    }
}
