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
     * It returns a string that is composed by the array values separated by a comma.
     *
     * @param  array  $array
     * @return string  $ret
     */
    public function getStringFromArraySeparatedByComma($array)
    {
        $ret = '';
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
    
    
    /***************************************************************************/

    /**
     * Check the date and return true if the weekday is the one specified in $dayOfTheWeek. eg. if $dayOfTheWeek = 3, is true if the date is a Wednesday
     * $dayOfTheWeek: 1|2|3|4|5|6|7 (MONDAY-SUNDAY)
     * https://stackoverflow.com/questions/2045736/getting-all-dates-for-mondays-and-tuesdays-for-the-next-year.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @param  string $date
     * @param  int $dayOfTheWeek
     * @return void
     */
    public function isWeekDay($date, $dayOfTheWeek)
    {
        // Fix the bug that was avoiding to save Sunday. Date 'w' identify sunday as 0 and not 7.
        if ($dayOfTheWeek == 7) {
            $dayOfTheWeek = 0;
        }

        return date('w', strtotime($date)) == $dayOfTheWeek;
    }
}
