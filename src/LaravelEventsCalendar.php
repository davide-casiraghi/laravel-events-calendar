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

    /***************************************************************************/

    /**
     * GET number of the specified weekday in this month (1 for the first).
     * $dateTimestamp - unix timestramp of the date specified
     * $dayOfWeekValue -  1 (for Monday) through 7 (for Sunday)
     * Return the number of the week in the month of the weekday specified.
     * @param  string $dateTimestamp
     * @param  string $dayOfWeekValue
     * @return int
     */
    public function weekdayNumberOfMonth($dateTimestamp, $dayOfWeekValue)
    {
        $cut = substr($dateTimestamp, 0, 8);
        $daylen = 86400;
        $timestamp = strtotime($dateTimestamp);
        $first = strtotime($cut.'01');
        $elapsed = (($timestamp - $first) / $daylen) + 1;
        $i = 1;
        $weeks = 0;
        for ($i == 1; $i <= $elapsed; $i++) {
            $dayfind = $cut.(strlen($i) < 2 ? '0'.$i : $i);
            $daytimestamp = strtotime($dayfind);
            $day = strtolower(date('N', $daytimestamp));
            if ($day == strtolower($dayOfWeekValue)) {
                $weeks++;
            }
        }
        if ($weeks == 0) {
            $weeks++;
        }

        return $weeks;
    }

    /***************************************************************************/

    /**
     * GET number of week from the end of the month - https://stackoverflow.com/questions/5853380/php-get-number-of-week-for-month
     * Week of the month = Week of the year - Week of the year of first day of month + 1.
     * Return the number of the week in the month of the day specified
     * $when - unix timestramp of the date specified.
     *
     * @param  string $when
     * @return int
     */
    public function weekOfMonthFromTheEnd($when = null)
    {
        $numberOfDayOfTheMonth = strftime('%e', $when); // Day of the month 1-31
        $lastDayOfMonth = strftime('%e', strtotime(date('Y-m-t', $when))); // the last day of the month of the specified date
        $dayDifference = $lastDayOfMonth - $numberOfDayOfTheMonth;

        switch (true) {
            case $dayDifference < 7:
                $weekFromTheEnd = 1;
                break;

            case $dayDifference < 14:
                $weekFromTheEnd = 2;
                break;

            case $dayDifference < 21:
                $weekFromTheEnd = 3;
                break;

            case $dayDifference < 28:
                $weekFromTheEnd = 4;
                break;

            default:
                $weekFromTheEnd = 5;
                break;
        }

        return $weekFromTheEnd;
    }

    /***************************************************************************/

    /**
     * GET number of day from the end of the month.
     * $when - unix timestramp of the date specified
     * Return the number of day of the month from end.
     *
     * @param  string $when
     * @return int
     */
    public function dayOfMonthFromTheEnd($when = null)
    {
        $numberOfDayOfTheMonth = strftime('%e', $when); // Day of the month 1-31
        $lastDayOfMonth = strftime('%e', strtotime(date('Y-m-t', $when))); // the last day of the month of the specified date
        $dayDifference = $lastDayOfMonth - $numberOfDayOfTheMonth;

        return $dayDifference;
    }

    /***************************************************************************/

    /**
     * GET the ordinal indicator - for the day of the month.
     * Return the ordinal indicator (st, nd, rd, th).
     * @param  int $number
     * @return string
     */
    public function getOrdinalIndicator($number)
    {
        switch ($number) {
            case  $number == 1 || $number == 21 || $number == 31:
                $ret = 'st';
                break;
            case  $number == 2 || $number == 22:
                $ret = 'nd';
                break;
            case  $number == 3 || $number == 23:
                $ret = 'rd';
                break;
            default:
                $ret = 'th';
                break;
        }

        return $ret;
    }

    /***************************************************************************/

    /**
     * Decode the event repeat_weekly_on field - used in event.show.
     * Return a string like "Monday".
     *
     * @param  string $repeatWeeklyOn
     * @return string
     */
    public function decodeRepeatWeeklyOn($repeatWeeklyOn)
    {
        $weekdayArray = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $ret = $weekdayArray[$repeatWeeklyOn];

        return $ret;
    }

    /***************************************************************************/

    /**
     * Decode the event on_monthly_kind field - used in event.show.
     * Return a string like "the 4th to last Thursday of the month".
     *
     * @param  string $onMonthlyKindCode
     * @return string
     */
    public function decodeOnMonthlyKind($onMonthlyKindCode)
    {
        $onMonthlyKindCodeArray = explode('|', $onMonthlyKindCode);
        $weekDays = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        //dd($onMonthlyKindCodeArray);
        switch ($onMonthlyKindCodeArray[0]) {
            case '0':  // 0|7 eg. the 7th day of the month
                $dayNumber = $onMonthlyKindCodeArray[1];
                $ordinalIndicator = self::getOrdinalIndicator($dayNumber);

                $dayNumberOrdinal = $dayNumber.$ordinalIndicator;
                $format = 'the %s day of the month';
                $ret = sprintf($format, $dayNumberOrdinal);
                break;
            case '1':  // 1|2|4 eg. the 2nd Thursday of the month
                $dayNumber = $onMonthlyKindCodeArray[1];
                $ordinalIndicator = self::getOrdinalIndicator($dayNumber);

                $dayNumberOrdinal = $dayNumber.$ordinalIndicator;
                $weekDay = $weekDays[$onMonthlyKindCodeArray[2]]; // Monday, Tuesday, Wednesday
                $format = 'the %s %s of the month';
                $ret = sprintf($format, $dayNumberOrdinal, $weekDay);
                break;
            case '2': // 2|20 eg. the 21st to last day of the month
                $dayNumber = $onMonthlyKindCodeArray[1] + 1;
                $ordinalIndicator = self::getOrdinalIndicator($dayNumber);

                $dayNumberOrdinal = $dayNumber.$ordinalIndicator;
                $format = 'the %s to last day of the month';
                $ret = sprintf($format, $dayNumberOrdinal);
                break;
            case '3': // 3|3|4 eg. the 4th to last Thursday of the month
                $dayNumber = $onMonthlyKindCodeArray[1] + 1;
                $ordinalIndicator = self::getOrdinalIndicator($dayNumber);

                $dayNumberOrdinal = $dayNumber.$ordinalIndicator;
                $weekDay = $weekDays[$onMonthlyKindCodeArray[2]]; // Monday, Tuesday, Wednesday

                if ($dayNumber == 1) {
                    $format = 'the last %s of the month';  // eg. the last Friday of the month until 17/06/2020
                    $ret = sprintf($format, $weekDay);
                } else {
                    $format = 'the %s to last %s of the month'; // eg. the 2nd to last Friday of the month until 17/06/2020
                    $ret = sprintf($format, $dayNumberOrdinal, $weekDay);
                }

                break;
        }

        return $ret;
    }
    
    /***************************************************************************/

    /**
     * Return the GPS coordinates of the venue
     * https://developer.mapquest.com/
     *
      * @param  array $address
     * @return array $ret
     */
    public static function getVenueGpsCoordinates($address)
    {
        $key = "Ad5KVnAISxX6aHyj6fAnHcKeh30n4W60";
        $response = file_get_contents("http://open.mapquestapi.com/geocoding/v1/address?key=".$key."&location=Washington,DC");
        $response = json_decode($response, true);
        
        $ret = array();
        $ret['lat'] = $response['results'][0]['locations'][0]['latLng']['lat'];
        $ret['lng'] = $response['results'][0]['locations'][0]['latLng']['lng'];
        
        return $ret;
    }
}
