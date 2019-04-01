<?php

namespace DavideCasiraghi\LaravelEventsCalendar;

class LaravelEventsCalendar
{
    /***************************************************************************/

    /**
     * Format a date from datepicker (d/m/Y) to a format ready to be stored on DB (Y-m-d).
     * If the date picker date is null return today's date.
     *
     * @param  string  date in the d/m/Y format
     * @return string  date in the Y-m-d format
     */
    public static function formatDatePickerDateForMysql($DatePickerDate)
    {
        if ($DatePickerDate) {
            list($tid, $tim, $tiy) = explode('/', $DatePickerDate);
            $ret = "$tiy-$tim-$tid";
        } else {
            date_default_timezone_set('Europe/Rome');
            $ret = date('Y-m-d', time());
        }

        return $ret;
    }
}
