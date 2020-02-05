<?php

namespace DavideCasiraghi\LaravelEventsCalendar;

use Carbon\Carbon;
use DateTime;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition;

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
    public static function formatDatePickerDateForMysql($DatePickerDate, bool $todaysDateIfNull = null)
    {
        if ($DatePickerDate) {
            [$tid, $tim, $tiy] = explode('/', $DatePickerDate);
            $ret = "$tiy-$tim-$tid";
        } elseif ($todaysDateIfNull) {
            date_default_timezone_set('Europe/Rome');
            $ret = date('Y-m-d', time());
        } else {
            $ret = '';
        }

        return $ret;
    }

    /***************************************************************************/

    /**
     * It returns a string that is composed by the array values separated by a comma.
     *
     * @param  iterable  $items
     * @return string  $ret
     */
    public static function getStringFromArraySeparatedByComma(iterable $items)
    {
        $ret = '';
        $i = 0;
        $len = count($items); // to put "," to all items except the last

        foreach ($items as $key => $item) {
            $ret .= $item;
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
     * @param  string $date
     * @param  int $dayOfTheWeek
     * @return bool
     */
    public static function isWeekDay(string $date, int $dayOfTheWeek)
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
    public static function weekdayNumberOfMonth(string $dateTimestamp, string $dayOfWeekValue)
    {
        $cut = substr($dateTimestamp, 0, 8);
        $daylen = 86400;
        $timestamp = strtotime($dateTimestamp);
        $first = strtotime($cut.'01');
        $elapsed = (($timestamp - $first) / $daylen) + 1;
        $i = 1;
        $weeks = 0;
        for ($i == 1; $i <= $elapsed; $i++) {
            $dayfind = $cut.(strlen((string)$i) < 2 ? '0'.$i : $i);
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
     * @param  int $when
     * @return int
     */
    public static function weekOfMonthFromTheEnd(int $when)
    {
        $numberOfDayOfTheMonth = strftime('%e', $when); // Day of the month 1-31
        $lastDayOfMonth = strftime('%e', strtotime(date('Y-m-t', $when))); // the last day of the month of the specified date
        $dayDifference = (int)$lastDayOfMonth - (int)$numberOfDayOfTheMonth;

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
     * @param  int $when
     * @return int
     */
    public static function dayOfMonthFromTheEnd(int $when)
    {
        $numberOfDayOfTheMonth = strftime('%e', $when); // Day of the month 1-31
        $lastDayOfMonth = strftime('%e', strtotime(date('Y-m-t', $when))); // the last day of the month of the specified date
        $dayDifference = $lastDayOfMonth - $numberOfDayOfTheMonth;

        return $dayDifference;
    }

    /***************************************************************************/

    /**
     * Decode the event repeat_weekly_on field - used in event.show.
     * Return a string like "Monday".
     *
     * @param  string $repeatWeeklyOn
     * @return string
     */
    public static function decodeRepeatWeeklyOn(string $repeatWeeklyOn)
    {
        $weekdayArray = [
            '',
            __('laravel-events-calendar::general.monday'),
            __('laravel-events-calendar::general.tuesday'),
            __('laravel-events-calendar::general.wednesday'),
            __('laravel-events-calendar::general.thursday'),
            __('laravel-events-calendar::general.friday'),
            __('laravel-events-calendar::general.saturday'),
            __('laravel-events-calendar::general.sunday'),
        ];
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
    public static function decodeOnMonthlyKind(string $onMonthlyKindCode)
    {
        $ret = "";
        $onMonthlyKindCodeArray = explode('|', $onMonthlyKindCode);
        $weekDays = [
            '',
            __('laravel-events-calendar::general.monday'),
            __('laravel-events-calendar::general.tuesday'),
            __('laravel-events-calendar::general.wednesday'),
            __('laravel-events-calendar::general.thursday'),
            __('laravel-events-calendar::general.friday'),
            __('laravel-events-calendar::general.saturday'),
            __('laravel-events-calendar::general.sunday'),
        ];

        //dd($onMonthlyKindCodeArray);
        switch ($onMonthlyKindCodeArray[0]) {
            case '0':  // 0|7 eg. the 7th day of the month
                $dayNumber = $onMonthlyKindCodeArray[1];
                $format = __('laravel-events-calendar::ordinalDays.the_'.($dayNumber).'_x_of_the_month');
                $ret = sprintf($format, __('laravel-events-calendar::general.day'));
                break;
            case '1':  // 1|2|4 eg. the 2nd Thursday of the month
                $dayNumber = $onMonthlyKindCodeArray[1];
                $weekDay = $weekDays[$onMonthlyKindCodeArray[2]]; // Monday, Tuesday, Wednesday
                $format = __('laravel-events-calendar::ordinalDays.the_'.($dayNumber).'_x_of_the_month');
                $ret = sprintf($format, $weekDay);
                break;
            case '2': // 2|20 eg. the 21st to last day of the month
                $dayNumber = $onMonthlyKindCodeArray[1] + 1;
                $format = __('laravel-events-calendar::ordinalDays.the_'.($dayNumber).'_to_last_x_of_the_month');
                $ret = sprintf($format, __('laravel-events-calendar::general.day'));
                break;
            case '3': // 3|3|4 eg. the 4th to last Thursday of the month
                $dayNumber = $onMonthlyKindCodeArray[1] + 1;
                $weekDay = $weekDays[$onMonthlyKindCodeArray[2]]; // Monday, Tuesday, Wednesday
                $format = __('laravel-events-calendar::ordinalDays.the_'.($dayNumber).'_to_last_x_of_the_month');
                $ret = sprintf($format, $weekDay);
                break;
        }

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return the GPS coordinates of the venue
     * https://developer.mapquest.com/.
     *
     * @param  string $address
     * @return array $ret
     */
    public static function getVenueGpsCoordinates(string $address)
    {
        $key = 'Ad5KVnAISxX6aHyj6fAnHcKeh30n4W60';
        $response = @file_get_contents('http://open.mapquestapi.com/geocoding/v1/address?key='.$key.'&location='.$address);
        $response = json_decode($response, true);

        $ret = [];
        $ret['lat'] = $response['results'][0]['locations'][0]['latLng']['lat'];
        $ret['lng'] = $response['results'][0]['locations'][0]['latLng']['lng'];

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return a string with the list of the collection id separated by comma.
     * without any space. eg. "354,320,310".
     *
     * @param  iterable $items
     * @return string $ret
     */
    public static function getCollectionIdsSeparatedByComma(iterable $items)
    {
        $itemsIds = [];
        foreach ($items as $item) {
            array_push($itemsIds, $item->id);
        }
        $ret = implode(',', $itemsIds);

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return a string that describe the report misuse reason.
     *
     * @param  int $reason
     * @return string $ret
     */
    public static function getReportMisuseReasonDescription(int $reason)
    {
        $ret = "";
        switch ($reason) {
            case '1':
                $ret = 'Not about Contact Improvisation';
                break;
            case '2':
                $ret = 'Contains wrong informations';
                break;
            case '3':
                $ret = 'It is not translated in english';
                break;
            case '4':
                $ret = 'Other (specify in the message)';
                break;
        }

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return a string that describe repetition kind in the event show view.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition $firstRpDates
     * @return string $ret
     */
    public static function getRepetitionTextString(Event $event, EventRepetition $firstRpDates)
    {
        $ret = '';

        switch ($event->repeat_type) {
                case '1': // noRepeat
                    break;
                case '2': // repeatWeekly
                    $repeatUntil = new DateTime($event->repeat_until);

                    // Get the name of the weekly day when the event repeat, if two days, return like "Thursday and Sunday"
                        $repetitonWeekdayNumbersArray = explode(',', $event->repeat_weekly_on);
                        $repetitonWeekdayNamesArray = [];
                        foreach ($repetitonWeekdayNumbersArray as $key => $repetitonWeekdayNumber) {
                            $repetitonWeekdayNamesArray[] = self::decodeRepeatWeeklyOn($repetitonWeekdayNumber);
                        }
                        // create from an array a string with all the values divided by " and "
                        $nameOfTheRepetitionWeekDays = implode(' and ', $repetitonWeekdayNamesArray);

                    //$ret = 'The event happens every '.$nameOfTheRepetitionWeekDays.' until '.$repeatUntil->format('d/m/Y');
                    $format = __('laravel-events-calendar::event.the_event_happens_every_x_until_x');
                    $ret .= sprintf($format, $nameOfTheRepetitionWeekDays, $repeatUntil->format('d/m/Y'));
                    break;
                case '3': //repeatMonthly
                    $repeatUntil = new DateTime($event->repeat_until);
                    $repetitionFrequency = self::decodeOnMonthlyKind($event->on_monthly_kind);

                    //$ret = 'The event happens '.$repetitionFrequency.' until '.$repeatUntil->format('d/m/Y');
                    $format = __('laravel-events-calendar::event.the_event_happens_x_until_x');
                    $ret .= sprintf($format, $repetitionFrequency, $repeatUntil->format('d/m/Y'));
                    break;
                case '4': //repeatMultipleDays
                    $dateStart = date('d/m/Y', strtotime($firstRpDates->start_repeat));
                    $singleDaysRepeatDatas = explode(',', $event->multiple_dates);

                    // Sort the datas
                       usort($singleDaysRepeatDatas, function ($a, $b) {
                           $a = Carbon::createFromFormat('d/m/Y', $a);
                           $b = Carbon::createFromFormat('d/m/Y', $b);

                           return strtotime($a) - strtotime($b);
                       });

                    $ret .= __('laravel-events-calendar::event.the_event_happens_on_this_dates');
                    $ret .= $dateStart.', ';
                    $ret .= self::getStringFromArraySeparatedByComma($singleDaysRepeatDatas);
                    break;
            }

        return $ret;
    }
}
