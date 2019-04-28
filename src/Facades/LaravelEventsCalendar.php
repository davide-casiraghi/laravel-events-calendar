<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \DavideCasiraghi\LaravelEventsCalendar\Skeleton\SkeletonClass
 */
class LaravelEventsCalendar extends Facade
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
