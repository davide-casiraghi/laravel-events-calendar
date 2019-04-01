<?php

namespace DavideCasiraghi\LaravelEventsCalendar;

class LaravelEventsCalendar
{
    /***************************************************************************/

    /**
     * Format the start date to be used in the search query.
     * If the start date is null return today's date.
     *
     * @param  int  event id
     * @return \App\Event the active events collection
     */
    public static function prepareStartDate($DatePickerStartDate)
    {
        if ($DatePickerStartDate) {
            list($tid, $tim, $tiy) = explode('/', $DatePickerStartDate);
            $ret = "$tiy-$tim-$tid";
        } else {
            // If no start date selected the search start from today's date
            date_default_timezone_set('Europe/Rome');
            $ret = date('Y-m-d', time());
        }

        return $ret;
    }
    
    /***************************************************************************/

    /**
     * Format the edn date to be used in the search query.
     *
     * @param  int  event id
     * @return \App\Event the active events collection
     */
    public static function prepareEndDate($DatePickerEndDate)
    {
        if ($DatePickerEndDate) {
            list($tid, $tim, $tiy) = explode('/', $DatePickerEndDate);
            $ret = "$tiy-$tim-$tid";
        } else {
            $ret = null;
        }

        return $ret;
    }


}
