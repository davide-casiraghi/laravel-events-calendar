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
     * @return void
     */
    public function handle()
    {
        $eventVenues = EventVenue::all();
        $eventVenuesNumber = count($eventVenues);

        foreach ($eventVenues as $key => $eventVenue) {

            // Get GPS coordinates
            $address = Country::getCountryName($eventVenue->country_id).', '.$eventVenue->city.', '.$eventVenue->address.', '.$eventVenue->zip_code;
            $gpsCoordinates = LaravelEventsCalendar::getVenueGpsCoordinates($address);

            // Print info in console
            // if there are problems the geocode system assign this coordinates 39.78373 -100.445882
            if ($gpsCoordinates['lng'] != '-100.445882') {
                $this->info($key.' of '.$eventVenuesNumber.' - '.$address);
                $this->info($gpsCoordinates['lat'].' '.$gpsCoordinates['lng']);
            } else {
                $this->error($key.' of '.$eventVenuesNumber.' - '.$address);
                $this->error($gpsCoordinates['lat'].' '.$gpsCoordinates['lng']);
            }

            // Save the data
            $eventVenue->lat = $gpsCoordinates['lat'];
            $eventVenue->lng = $gpsCoordinates['lng'];
            $eventVenue->save();
        }
    }
}
