<?php

namespace DavideCasiraghi\LaravelEventsCalendar;

class LaravelEventsCalendar
{
    /***************************************************************************/

    /**
     * Format a date from datepicker (d/m/Y) to a format ready to be stored on DB (Y-m-d).
     * If the date picker date is null return today's date.
     * the PARAM is a date in the d/m/Y format - the RETURN is a date in the Y-m-d format.
     * If $todaysDateIfNull = 1, when the date is null return the date of today.
     *
     * @param  string  $DatePickerDate
     * @param  bool  $todaysDateIfNull
     * @return string  $ret
     */
    public static function formatDatePickerDateForMysql($DatePickerDate, $todaysDateIfNull = 0)
    {
        if ($DatePickerDate) {
            [$tid, $tim, $tiy] = explode('/', $DatePickerDate);
            $ret = "$tiy-$tim-$tid";
        } elseif ($todaysDateIfNull) {
            date_default_timezone_set('Europe/Rome');
            $ret = date('Y-m-d', time());
        } else {
            $ret = null;
        }

        return $ret;
    }

    /***************************************************************************/

    /**
     * It returns a string that is composed by the array values separated by a comma
     *
     * @param  array  $array
     * @return string  $ret
     */
    public static function getStringFromArraySeparatedByComma($array)
    {
        $ret = "";
        $i = 0;
        $len = count($array); // to put "," to all items except the last
        
        foreach ($array as $key => $value) {
            $ret .= $value;
            if ($i != $len - 1) {  // not last
                $ret .= ', ';
            }
            $i++;
        }

        return $ret;
    }

}
