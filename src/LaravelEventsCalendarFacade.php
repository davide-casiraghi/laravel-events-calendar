<?php

namespace DavideCasiraghi\LaravelEventsCalendar;

use Illuminate\Support\Facades\Facade;

/**
 * @see \DavideCasiraghi\LaravelEventsCalendar\Skeleton\SkeletonClass
 */
class LaravelEventsCalendarFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-events-calendar';
    }
}
