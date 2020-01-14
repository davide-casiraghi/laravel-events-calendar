<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use DavideCasiraghi\LaravelEventsCalendar\Mail\ContactOrganizer;
use DavideCasiraghi\LaravelEventsCalendar\Mail\ReportMisuse;
use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventVenue;
use DavideCasiraghi\LaravelEventsCalendar\Models\Organizer;
use DavideCasiraghi\LaravelEventsCalendar\Models\Region;
use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Validator;

class EventController extends Controller
{
    /***************************************************************************/
    /* Restrict the access to this resource just to logged in users except show view */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'reportMisuse', 'reportMisuseThankyou', 'mailToOrganizer', 'mailToOrganizerSent', 'eventBySlug', 'eventBySlugAndRepetition', 'EventsListByCountry', 'calculateMonthlySelectOptions']]);
    }

    /***************************************************************************/

    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // To show just the events created by the the user - If admin or super admin is set to null show all the events
        $authorUserId = ($this->getLoggedAuthorId()) ? $this->getLoggedAuthorId() : null; // if is 0 (super admin or admin) it's setted to null to avoid include it in the query

        $eventCategories = EventCategory::listsTranslations('name')->orderBy('name')->pluck('name', 'id');
        $countries = Country::orderBy('name')->pluck('name', 'id');
        $venues = EventVenue::pluck('country_id', 'id');

        $searchKeywords = $request->input('keywords');
        $searchCategory = $request->input('category_id');
        $searchCountry = $request->input('country_id');

        if ($searchKeywords || $searchCategory || $searchCountry) {
            $events = Event::
                // Show only the events owned by the user, if the user is an admin or super admin show all the events
                when(isset($authorUserId), function ($query, $authorUserId) {
                    return $query->where('created_by', $authorUserId);
                })
                ->when($searchKeywords, function ($query, $searchKeywords) {
                    return $query->where('title', $searchKeywords)->orWhere('title', 'like', '%'.$searchKeywords.'%');
                })
                ->when($searchCategory, function ($query, $searchCategory) {
                    return $query->where('category_id', '=', $searchCategory);
                })
                ->when($searchCountry, function ($query, $searchCountry) {
                    return $query->join('event_venues', 'events.venue_id', '=', 'event_venues.id')->where('event_venues.country_id', '=', $searchCountry);
                })
                ->select('*', 'events.id as id', 'events.slug as slug', 'events.image as image') // To keep in the join the id of the Events table - https://stackoverflow.com/questions/28062308/laravel-eloquent-getting-id-field-of-joined-tables-in-eloquent
                ->paginate(20);

        //dd($events);
        } else {
            $events = Event::latest()
                ->when($authorUserId, function ($query, $authorUserId) {
                    return $query->where('created_by', $authorUserId);
                })
                ->paginate(20);
        }

        return view('laravel-events-calendar::events.index', compact('events'))
            ->with('i', (request()->input('page', 1) - 1) * 20)
            ->with('eventCategories', $eventCategories)
            ->with('countries', $countries)
            ->with('venues', $venues)
            ->with('searchKeywords', $searchKeywords)
            ->with('searchCategory', $searchCategory)
            ->with('searchCountry', $searchCountry);
    }

    /***************************************************************************/

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $authorUserId = $this->getLoggedAuthorId();

        $eventCategories = EventCategory::listsTranslations('name')->orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('name')->pluck('name', 'id');
        $teachers = Teacher::orderBy('name')->pluck('name', 'id');
        $organizers = Organizer::orderBy('name')->pluck('name', 'id');
        //$venues = EventVenue::pluck('name', 'id');
        $venues = DB::table('event_venues')
                ->select('id', 'name', 'city')->orderBy('name')->get();

        $dateTime = [];
        $dateTime['repeatUntil'] = null;
        $dateTime['multipleDates'] = null;

        return view('laravel-events-calendar::events.create')
            ->with('eventCategories', $eventCategories)
            ->with('users', $users)
            ->with('teachers', $teachers)
            ->with('organizers', $organizers)
            ->with('venues', $venues)
            ->with('dateTime', $dateTime)
            ->with('authorUserId', $authorUserId);
    }

    /***************************************************************************/

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate form datas
        $validator = $this->eventsValidator($request);
        if ($validator->fails()) {
            //dd($validator->failed());
            return back()->withErrors($validator)->withInput();
        }

        $event = new Event();
        $this->saveOnDb($request, $event);

        return redirect()->route('events.index')
                        ->with('success', __('laravel-events-calendar::messages.event_added_successfully'));
    }

    /***************************************************************************/

    /**
     * Display the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition $firstRpDates
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event, $firstRpDates)
    {
        $category = EventCategory::find($event->category_id);
        $teachers = $event->teachers()->get();
        $organizers = $event->organizers()->get();

        $venue = DB::table('event_venues')
                ->select('id', 'name', 'city', 'address', 'zip_code', 'country_id', 'region_id', 'description', 'website', 'extra_info')
                ->where('id', $event->venue_id)
                ->first();

        $country = Country::find($venue->country_id);
        $region = Region::listsTranslations('name')->find($venue->region_id);
        // aaaaa
        /*$country = DB::table('countries')
                ->select('id', 'name', 'continent_id')
                ->where('id', $venue->country_id)
                ->first();*/

        $continent = Continent::find($country->continent_id);

        /*DB::table('continents')
                ->select('id', 'name')
                ->where('id', $country->continent_id)
                ->first();*/

        // Repetition text to show
        switch ($event->repeat_type) {
                case '1': // noRepeat
                    $repetition_text = null;
                    break;
                case '2': // repeatWeekly
                    $repeatUntil = new DateTime($event->repeat_until);

                    // Get the name of the weekly day when the event repeat, if two days, return like "Thursday and Sunday"
                        $repetitonWeekdayNumbersArray = explode(',', $event->repeat_weekly_on);
                        $repetitonWeekdayNamesArray = [];
                        foreach ($repetitonWeekdayNumbersArray as $key => $repetitonWeekdayNumber) {
                            $repetitonWeekdayNamesArray[] = LaravelEventsCalendar::decodeRepeatWeeklyOn($repetitonWeekdayNumber);
                        }
                        // create from an array a string with all the values divided by " and "
                        $nameOfTheRepetitionWeekDays = implode(' and ', $repetitonWeekdayNamesArray);

                    //$repetition_text = 'The event happens every '.$nameOfTheRepetitionWeekDays.' until '.$repeatUntil->format('d/m/Y');
                    $format = __('laravel-events-calendar::event.the_event_happens_every_x_until');
                    $repetition_text = sprintf($format, $nameOfTheRepetitionWeekDays, $repeatUntil->format('d/m/Y'));
                    break;
                case '3': //repeatMonthly
                    $repeatUntil = new DateTime($event->repeat_until);
                    $repetitionFrequency = LaravelEventsCalendar::decodeOnMonthlyKind($event->on_monthly_kind);

                    //$repetition_text = 'The event happens '.$repetitionFrequency.' until '.$repeatUntil->format('d/m/Y');
                    $format = __('laravel-events-calendar::event.the_event_happens_x_until_x');
                    $repetition_text = sprintf($format, $repetitionFrequency, $repeatUntil->format('d/m/Y'));
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

                    //$repetition_text = 'The event happens on this dates: ';
                    $repetition_text = __('laravel-events-calendar::event.the_event_happens_on_this_dates');

                    $repetition_text .= $dateStart.', ';
                    $repetition_text .= LaravelEventsCalendar::getStringFromArraySeparatedByComma($singleDaysRepeatDatas);

                    break;
            }

        // True if the repetition start and end on the same day
        $sameDateStartEnd = ((date('Y-m-d', strtotime($firstRpDates->start_repeat))) == (date('Y-m-d', strtotime($firstRpDates->end_repeat)))) ? 1 : 0;

        return view('laravel-events-calendar::events.show', compact('event'))
                ->with('category', $category)
                ->with('teachers', $teachers)
                ->with('organizers', $organizers)
                ->with('venue', $venue)
                ->with('country', $country)
                ->with('region', $region)
                ->with('continent', $continent)
                ->with('datesTimes', $firstRpDates)
                ->with('repetition_text', $repetition_text)
                ->with('sameDateStartEnd', $sameDateStartEnd);
    }

    /***************************************************************************/

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //if (Auth::user()->id == $event->created_by || Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()) {
        if (Auth::user()->id == $event->created_by || Auth::user()->group == 1 || Auth::user()->group == 2) {
            $authorUserId = $this->getLoggedAuthorId();

            //$eventCategories = EventCategory::pluck('name', 'id');  // removed because was braking the tests
            $eventCategories = EventCategory::listsTranslations('name')->orderBy('name')->pluck('name', 'id');

            $users = User::orderBy('name')->pluck('name', 'id');
            $teachers = Teacher::orderBy('name')->pluck('name', 'id');
            $organizers = Organizer::orderBy('name')->pluck('name', 'id');
            $venues = DB::table('event_venues')
                    ->select('id', 'name', 'address', 'city')->orderBy('name')->get();

            $eventFirstRepetition = DB::table('event_repetitions')
                    ->select('id', 'start_repeat', 'end_repeat')
                    ->where('event_id', '=', $event->id)
                    ->first();

            $dateTime = [];
            $dateTime['dateStart'] = (isset($eventFirstRepetition->start_repeat)) ? date('d/m/Y', strtotime($eventFirstRepetition->start_repeat)) : '';
            $dateTime['dateEnd'] = (isset($eventFirstRepetition->end_repeat)) ? date('d/m/Y', strtotime($eventFirstRepetition->end_repeat)) : '';
            $dateTime['timeStart'] = (isset($eventFirstRepetition->start_repeat)) ? date('g:i A', strtotime($eventFirstRepetition->start_repeat)) : '';
            $dateTime['timeEnd'] = (isset($eventFirstRepetition->end_repeat)) ? date('g:i A', strtotime($eventFirstRepetition->end_repeat)) : '';
            $dateTime['repeatUntil'] = date('d/m/Y', strtotime($event->repeat_until));
            $dateTime['multipleDates'] = $event->multiple_dates;

            // GET Multiple teachers
            $teachersDatas = $event->teachers;
            $teachersSelected = [];
            foreach ($teachersDatas as $teacherDatas) {
                array_push($teachersSelected, $teacherDatas->id);
            }
            $multiple_teachers = implode(',', $teachersSelected);

            // GET Multiple Organizers
            $organizersDatas = $event->organizers;
            $organizersSelected = [];
            foreach ($organizersDatas as $organizerDatas) {
                array_push($organizersSelected, $organizerDatas->id);
            }
            $multiple_organizers = implode(',', $organizersSelected);

            return view('laravel-events-calendar::events.edit', compact('event'))
                        ->with('eventCategories', $eventCategories)
                        ->with('users', $users)
                        ->with('teachers', $teachers)
                        ->with('multiple_teachers', $multiple_teachers)
                        ->with('organizers', $organizers)
                        ->with('multiple_organizers', $multiple_organizers)
                        ->with('venues', $venues)
                        ->with('dateTime', $dateTime)
                        ->with('authorUserId', $authorUserId);
        } else {
            return redirect()->route('home')->with('message', __('auth.not_allowed_to_access'));
        }
    }

    /***************************************************************************/

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        // Validate form datas
        $validator = $this->eventsValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->saveOnDb($request, $event);

        return redirect()->route('events.index')
                        ->with('success', __('laravel-events-calendar::messages.event_updated_successfully'));
    }

    /***************************************************************************/

    /**
     * Remove the specified resource from storage.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        DB::table('event_repetitions')
                ->where('event_id', $event->id)
                ->delete();

        $event->delete();

        return redirect()->route('events.index')
                        ->with('success', __('laravel-events-calendar::messages.event_deleted_successfully'));
    }

    /***************************************************************************/

    /**
     * To save event repetitions for create and update methods.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @return void
     */
    public function saveEventRepetitions($request, $event)
    {
        Event::deletePreviousRepetitions($event->id);

        // Saving repetitions - If it's a single event will be stored with just one repetition
        $timeStart = date('H:i:s', strtotime($request->get('time_start')));
        $timeEnd = date('H:i:s', strtotime($request->get('time_end')));
        switch ($request->get('repeat_type')) {
                case '1':  // noRepeat
                    $eventRepetition = new EventRepetition();
                    $eventRepetition->event_id = $event->id;

                    $dateStart = implode('-', array_reverse(explode('/', $request->get('startDate'))));
                    $dateEnd = implode('-', array_reverse(explode('/', $request->get('endDate'))));

                    $eventRepetition->start_repeat = $dateStart.' '.$timeStart;
                    $eventRepetition->end_repeat = $dateEnd.' '.$timeEnd;
                    $eventRepetition->save();

                    break;

                case '2':   // repeatWeekly

                    // Convert the start date in a format that can be used for strtotime
                        $startDate = implode('-', array_reverse(explode('/', $request->get('startDate'))));

                    // Calculate repeat until day
                        $repeatUntilDate = implode('-', array_reverse(explode('/', $request->get('repeat_until'))));
                        $this->saveWeeklyRepeatDates($event, $request->get('repeat_weekly_on_day'), $startDate, $repeatUntilDate, $timeStart, $timeEnd);

                    break;

                case '3':  //repeatMonthly
                    // Same of repeatWeekly
                        $startDate = implode('-', array_reverse(explode('/', $request->get('startDate'))));
                        $repeatUntilDate = implode('-', array_reverse(explode('/', $request->get('repeat_until'))));

                    // Get the array with month repeat details
                        $monthRepeatDatas = explode('|', $request->get('on_monthly_kind'));

                        $this->saveMonthlyRepeatDates($event, $monthRepeatDatas, $startDate, $repeatUntilDate, $timeStart, $timeEnd);

                    break;

                case '4':  //repeatMultipleDays
                    // Same of repeatWeekly
                        $startDate = implode('-', array_reverse(explode('/', $request->get('startDate'))));

                    // Get the array with single day repeat details
                        $singleDaysRepeatDatas = explode(',', $request->get('multiple_dates'));

                        $this->saveMultipleRepeatDates($event, $singleDaysRepeatDatas, $startDate, $timeStart, $timeEnd);

                    break;
            }
    }

    /***************************************************************************/

    /**
     * Save all the weekly repetitions in the event_repetitions table.
     * $dateStart and $dateEnd are in the format Y-m-d
     * $timeStart and $timeEnd are in the format H:i:s.
     * $weekDays - $request->get('repeat_weekly_on_day').
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @param  string  $weekDays
     * @param  string  $startDate
     * @param  string  $repeatUntilDate
     * @param  string  $timeStart
     * @param  string  $timeEnd
     * @return void
     */
    public function saveWeeklyRepeatDates($event, $weekDays, $startDate, $repeatUntilDate, $timeStart, $timeEnd)
    {
        $beginPeriod = new DateTime($startDate);
        $endPeriod = new DateTime($repeatUntilDate);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($beginPeriod, $interval, $endPeriod);

        foreach ($period as $day) {  // Iterate for each day of the period
            foreach ($weekDays as $weekDayNumber) { // Iterate for every day of the week (1:Monday, 2:Tuesday, 3:Wednesday ...)
                if (LaravelEventsCalendar::isWeekDay($day->format('Y-m-d'), $weekDayNumber)) {
                    $this->saveEventRepetitionOnDB($event->id, $day->format('Y-m-d'), $day->format('Y-m-d'), $timeStart, $timeEnd);
                }
            }
        }
    }

    /***************************************************************************/

    /**
     * Save all the weekly repetitions inthe event_repetitions table
     * useful: http://thisinterestsme.com/php-get-first-monday-of-month/.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @param  array   $monthRepeatDatas - explode of $request->get('on_monthly_kind')
     *                      0|28 the 28th day of the month
     *                      1|2|2 the 2nd Tuesday of the month
     *                      2|17 the 18th to last day of the month
     *                      3|1|3 the 2nd to last Wednesday of the month
     * @param  string  $startDate (Y-m-d)
     * @param  string  $repeatUntilDate (Y-m-d)
     * @param  string  $timeStart (H:i:s)
     * @param  string  $timeEnd (H:i:s)
     * @return void
     */
    public function saveMonthlyRepeatDates($event, $monthRepeatDatas, $startDate, $repeatUntilDate, $timeStart, $timeEnd)
    {
        $start = $month = Carbon::create($startDate);
        $end = Carbon::create($repeatUntilDate);

        $numberOfTheWeekArray = ['first', 'second', 'third', 'fourth', 'fifth', 'sixth'];
        $weekdayArray = [Carbon::MONDAY, Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY];

        switch ($monthRepeatDatas[0]) {
            case '0':  // Same day number - eg. "the 28th day of the month"
                while ($month < $end) {
                    $day = $month->format('Y-m-d');

                    $this->saveEventRepetitionOnDB($event->id, $day, $day, $timeStart, $timeEnd);
                    $month = $month->addMonth();
                }
                break;
            case '1':  // Same weekday/week of the month - eg. the "1st Monday"
                $numberOfTheWeek = $monthRepeatDatas[1]; // eg. 1(first) | 2(second) | 3(third) | 4(fourth) | 5(fifth)
                $weekday = $weekdayArray[$monthRepeatDatas[2] - 1]; // eg. monday | tuesday | wednesday

                while ($month < $end) {
                    $month_number = Carbon::parse($month)->isoFormat('M');
                    $year_number = Carbon::parse($month)->isoFormat('YYYY');

                    $day = Carbon::create($year_number, $month_number, 30, 0, 0, 0)->nthOfMonth($numberOfTheWeek, $weekday);  // eg. Carbon::create(2014, 5, 30, 0, 0, 0)->nthOfQuarter(2, Carbon::SATURDAY);

                    $this->saveEventRepetitionOnDB($event->id, $day, $day, $timeStart, $timeEnd);
                    $month = $month->addMonth();
                }
                break;
            case '2':  // Same day of the month (from the end) - the 3rd to last day (0 if last day, 1 if 2nd to last day, 2 if 3rd to last day)
                $dayFromTheEnd = $monthRepeatDatas[1];
                while ($month < $end) {
                    $month_number = Carbon::parse($month)->isoFormat('M');
                    $year_number = Carbon::parse($month)->isoFormat('YYYY');

                    $day = Carbon::create($year_number, $month_number, 30, 0, 0, 0)->lastOfMonth()->subDays($dayFromTheEnd);

                    $this->saveEventRepetitionOnDB($event->id, $day, $day, $timeStart, $timeEnd);
                    $month = $month->addMonth();
                }
                break;
            case '3':  // Same weekday/week of the month (from the end) - the last Friday - (0 if last Friday, 1 if the 2nd to last Friday, 2 if the 3nd to last Friday)
                $weekday = $weekdayArray[$monthRepeatDatas[2] - 1]; // eg. monday | tuesday | wednesday
                $weeksFromTheEnd = $monthRepeatDatas[1];

                while ($month < $end) {
                    $month_number = Carbon::parse($month)->isoFormat('M');
                    $year_number = Carbon::parse($month)->isoFormat('YYYY');

                    $day = Carbon::create($year_number, $month_number, 30, 0, 0, 0)->lastOfMonth($weekday)->subWeeks($weeksFromTheEnd);

                    $this->saveEventRepetitionOnDB($event->id, $day, $day, $timeStart, $timeEnd);
                    $month = $month->addMonth();
                }
                break;
        }
    }

    /***************************************************************************/

    /**
     * Save all the weekly repetitions inthe event_repetitions table
     * useful: http://thisinterestsme.com/php-get-first-monday-of-month/.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @param  array   $singleDaysRepeatDatas - explode of $request->get('multiple_dates')
     * @param  string  $startDate (Y-m-d)
     * @param  string  $timeStart (H:i:s)
     * @param  string  $timeEnd (H:i:s)
     * @return void
     */
    public function saveMultipleRepeatDates($event, $singleDaysRepeatDatas, $startDate, $timeStart, $timeEnd)
    {
        $dateTime = strtotime($startDate);
        $day = date('Y-m-d', $dateTime);
        $this->saveEventRepetitionOnDB($event->id, $day, $day, $timeStart, $timeEnd);

        foreach ($singleDaysRepeatDatas as $key => $singleDayRepeatDatas) {
            $day = Carbon::createFromFormat('d/m/Y', $singleDayRepeatDatas);
            $this->saveEventRepetitionOnDB($event->id, $day, $day, $timeStart, $timeEnd);
        }
    }

    /***************************************************************************/

    /**
     * Save event repetition in the DB.
     * $dateStart and $dateEnd are in the format Y-m-d
     * $timeStart and $timeEnd are in the format H:i:s.
     * @param  int $eventId
     * @param  string $dateStart
     * @param  string $dateEnd
     * @param  string $timeStart
     * @param  string $timeEnd
     * @return void
     */
    public function saveEventRepetitionOnDB($eventId, $dateStart, $dateEnd, $timeStart, $timeEnd)
    {
        $eventRepetition = new EventRepetition();
        $eventRepetition->event_id = $eventId;

        $eventRepetition->start_repeat = $dateStart.' '.$timeStart;
        $eventRepetition->end_repeat = $dateEnd.' '.$timeEnd;
        $eventRepetition->save();
    }

    /***************************************************************************/

    /**
     * Send the Misuse mail.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reportMisuse(Request $request)
    {
        $report = [];

        //$report['senderEmail'] = 'noreply@globalcicalendar.com';
        $report['senderEmail'] = $request->user_email;
        $report['senderName'] = 'Anonymus User';
        $report['subject'] = 'Report misuse form';
        //$report['adminEmail'] = env('ADMIN_MAIL');
        $report['creatorEmail'] = $this->getCreatorEmail($request->created_by);

        $report['message'] = $request->message;
        $report['event_title'] = $request->event_title;
        $report['event_id'] = $request->event_id;
        $report['event_slug'] = $request->slug;

        switch ($request->reason) {
            case '1':
                $report['reason'] = 'Not about Contact Improvisation';
                break;
            case '2':
                $report['reason'] = 'Contains wrong informations';
                break;
            case '3':
                $report['reason'] = 'It is not translated in english';
                break;
            case '4':
                $report['reason'] = 'Other (specify in the message)';
                break;
        }

        //Mail::to($request->user())->send(new ReportMisuse($report));
        Mail::to(env('ADMIN_MAIL'))->send(new ReportMisuse($report));

        return redirect()->route('events.misuse-thankyou');
    }

    /***************************************************************************/

    /**
     * Send the mail to the Organizer (from the event modal in the event show view).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function mailToOrganizer(Request $request)
    {
        $message = [];
        $message['senderEmail'] = $request->user_email;
        $message['senderName'] = $request->user_name;
        $message['subject'] = 'Request from the Global CI Calendar';
        //$message['emailTo'] = $organizersEmails;

        $message['message'] = $request->message;
        $message['event_title'] = $request->event_title;
        $message['event_id'] = $request->event_id;
        $message['event_slug'] = $request->slug;

        /*
        $eventOrganizers = Event::find($request->event_id)->organizers;
        foreach ($eventOrganizers as $eventOrganizer) {
            Mail::to($eventOrganizer->email)->send(new ContactOrganizer($message));
        }*/

        Mail::to($request->contact_email)->send(new ContactOrganizer($message));

        return redirect()->route('events.organizer-sent');
    }

    /***************************************************************************/

    /**
     * Display the thank you view after the mail to the organizer is sent (called by /mailToOrganizer/sent route).
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function mailToOrganizerSent()
    {
        return view('laravel-events-calendar::emails.contact.organizer-sent');
    }

    /***************************************************************************/

    /**
     * Display the thank you view after the misuse report mail is sent (called by /misuse/thankyou route).
     * @return \Illuminate\Http\Response
     */
    public function reportMisuseThankyou()
    {
        return view('laravel-events-calendar::emails.report-thankyou');
    }

    /***************************************************************************/

    /**
     * Set the Event attributes about repeating before store or update (repeat until field and multiple days).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Event  $event
     */
    public function setEventRepeatFields($request, $event)
    {
        // Set Repeat Until
        $event->repeat_type = $request->get('repeat_type');
        if ($request->get('repeat_until')) {
            $dateRepeatUntil = implode('-', array_reverse(explode('/', $request->get('repeat_until'))));
            $event->repeat_until = $dateRepeatUntil.' 00:00:00';
        }

        // Weekely - Set multiple week days
        if ($request->get('repeat_weekly_on_day')) {
            $repeat_weekly_on_day = $request->get('repeat_weekly_on_day');
            //dd($repeat_weekly_on_day);
            $i = 0;
            $len = count($repeat_weekly_on_day); // to put "," to all items except the last
            $event->repeat_weekly_on = '';
            foreach ($repeat_weekly_on_day as $key => $weeek_day) {
                $event->repeat_weekly_on .= $weeek_day;
                if ($i != $len - 1) {  // not last
                    $event->repeat_weekly_on .= ',';
                }
                $i++;
            }
        }

        // Monthly

        /* $event->repeat_type = $request->get('repeat_monthly_on');*/

        return $event;
    }

    /***************************************************************************/

    /**
     * Return the HTML of the monthly select dropdown - inspired by - https://www.theindychannel.com/calendar
     * - Used by the AJAX in the event repeat view -
     * - The HTML contain a <select></select> with four <options></options>.
     *
     * @param  \Illuminate\Http\Request  $request  - Just the day
     * @return string
     */
    public function calculateMonthlySelectOptions(Request $request)
    {
        $monthlySelectOptions = [];
        $date = implode('-', array_reverse(explode('/', $request->day)));  // Our YYYY-MM-DD date string
        $unixTimestamp = strtotime($date);  // Convert the date string into a unix timestamp.
        $dayOfWeekString = date('l', $unixTimestamp); // Monday | Tuesday | Wednesday | ..

        // Same day number - eg. "the 28th day of the month"
        $dateArray = explode('/', $request->day);
        $dayNumber = ltrim($dateArray[0], '0'); // remove the 0 in front of a day number eg. 02/10/2018
        $ordinalIndicator = LaravelEventsCalendar::getOrdinalIndicator($dayNumber);

        /*array_push($monthlySelectOptions, [
            'value' => '0|'.$dayNumber,
            'text' => 'the '.$dayNumber.$ordinalIndicator.' day of the month',
        ]);*/
        
        $format = __('laravel-events-calendar::event.the_x_day_of_the_month');
        $repeatText = sprintf($format, $dayNumber.$ordinalIndicator);
        
        array_push($monthlySelectOptions, [
            'value' => '0|'.$dayNumber,
            'text' => $repeatText,
        ]);

        // Same weekday/week of the month - eg. the "1st Monday" 1|1|1 (first week, monday)
            $dayOfWeekValue = date('N', $unixTimestamp); // 1 (for Monday) through 7 (for Sunday)
            $weekOfTheMonth = LaravelEventsCalendar::weekdayNumberOfMonth($date, $dayOfWeekValue); // 1 | 2 | 3 | 4 | 5
            $ordinalIndicator = LaravelEventsCalendar::getOrdinalIndicator($weekOfTheMonth); //st, nd, rd, th

            /*array_push($monthlySelectOptions, [
                'value' => '1|'.$weekOfTheMonth.'|'.$dayOfWeekValue,
                'text' => 'the '.$weekOfTheMonth.$ordinalIndicator.' '.$dayOfWeekString.' of the month',
            ]);*/
            
            $format = __('laravel-events-calendar::event.the_x_x_of_the_month');
            $repeatText = sprintf($format, $weekOfTheMonth.$ordinalIndicator, $dayOfWeekString);
            
            array_push($monthlySelectOptions, [
                'value' => '1|'.$weekOfTheMonth.'|'.$dayOfWeekValue,
                'text' => $repeatText,
            ]);

        // Same day of the month (from the end) - the 3rd to last day (0 if last day, 1 if 2nd to last day, , 2 if 3rd to last day)
            $dayOfMonthFromTheEnd = LaravelEventsCalendar::dayOfMonthFromTheEnd($unixTimestamp); // 1 | 2 | 3 | 4 | 5

            //$dayOfMonthFromTheEnd = $dayOfMonthFromTheEnd+1;
        /*if ($dayOfMonthFromTheEnd == 0) {
            $dayText = 'last';
        } else {
            $numberOfTheDay = $dayOfMonthFromTheEnd + 1;
            $ordinalIndicator = LaravelEventsCalendar::getOrdinalIndicator($numberOfTheDay);
            $dayText = $numberOfTheDay.$ordinalIndicator.' to last';
        }*/
        
        

        /*array_push($monthlySelectOptions, [
            'value' => '2|'.$dayOfMonthFromTheEnd,
            'text' => 'the '.$dayText.' day of the month',
        ]);*/
        
        $format = __('laravel-events-calendar::ordinalDays.the_'.($dayOfMonthFromTheEnd+1).'_to_last_x_of_the_month');
        
        $repeatText = sprintf($format, "day");
        
        array_push($monthlySelectOptions, [
            'value' => '2|'.$dayOfMonthFromTheEnd,
            'text' => $repeatText,
        ]);

        // Same weekday/week of the month (from the end) - the last Friday - (0 if last Friday, 1 if the 2nd to last Friday, 2 if the 3nd to last Friday)
            $weekOfMonthFromTheEnd = LaravelEventsCalendar::weekOfMonthFromTheEnd($unixTimestamp); // 1 | 2 | 3 | 4 | 5

        if ($weekOfMonthFromTheEnd == 1) {
            $weekText = 'last ';
            $weekValue = 0;
        } else {
            $ordinalIndicator = LaravelEventsCalendar::getOrdinalIndicator($weekOfMonthFromTheEnd);
            $weekText = $weekOfMonthFromTheEnd.$ordinalIndicator.' to last';
            $weekValue = $weekOfMonthFromTheEnd - 1;
        }

        /*array_push($monthlySelectOptions, [
            'value' => '3|'.$weekValue.'|'.$dayOfWeekValue,
            'text' => 'the '.$weekText.$dayOfWeekString.' of the month',
        ]);*/
        
        $format = __('laravel-events-calendar::event.the_x_x_of_the_month');
        $repeatText = sprintf($format, $weekText, $dayOfWeekString);

        array_push($monthlySelectOptions, [
            'value' => '3|'.$weekValue.'|'.$dayOfWeekValue,
            'text' => $repeatText,
        ]);

        // GENERATE the HTML to return
        $selectTitle = __('laravel-events-calendar::general.select_repeat_monthly_kind');
        $onMonthlyKindSelect = "<select name='on_monthly_kind' id='on_monthly_kind' class='selectpicker' title='".$selectTitle."'>";
        foreach ($monthlySelectOptions as $key => $monthlySelectOption) {
            $onMonthlyKindSelect .= "<option value='".$monthlySelectOption['value']."'>".$monthlySelectOption['text'].'</option>';
        }
        $onMonthlyKindSelect .= '</select>';

        return $onMonthlyKindSelect;
    }

    // **********************************************************************

    /**
     * Save/Update the record on DB.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Event $event
     * @return string $ret - the ordinal indicator (st, nd, rd, th)
     */
    public function saveOnDb($request, $event)
    {
        $countries = Country::getCountries();
        $teachers = Teacher::pluck('name', 'id');

        $venue = DB::table('event_venues')
                ->select('event_venues.id AS venue_id', 'event_venues.name AS venue_name', 'event_venues.country_id AS country_id', 'event_venues.continent_id', 'event_venues.city')
                ->where('event_venues.id', '=', $request->get('venue_id'))
                ->first();

        $event->title = $request->get('title');
        $event->description = clean($request->get('description'));

        //$event->created_by = (isset(Auth::id())) ? Auth::id() : null;
        //$event->created_by = Auth::id();
        if ($request->get('created_by')) {
            $event->created_by = $request->get('created_by');
        }

        if (! $event->slug) {
            $event->slug = Str::slug($event->title, '-').'-'.rand(100000, 1000000);
        }
        $event->category_id = $request->get('category_id');
        $event->venue_id = $request->get('venue_id');
        $event->image = $request->get('image');
        $event->contact_email = $request->get('contact_email');
        $event->website_event_link = $request->get('website_event_link');
        $event->facebook_event_link = $request->get('facebook_event_link');
        $event->status = $request->get('status');
        $event->on_monthly_kind = $request->get('on_monthly_kind');
        $event->multiple_dates = $request->get('multiple_dates');

        // Event teaser image upload
        //dd($request->file('image'));
        if ($request->file('image')) {
            $imageFile = $request->file('image');
            $imageName = time().'.'.'jpg';  //$imageName = $teaserImageFile->hashName();
            $imageSubdir = 'events_teaser';
            $imageWidth = '968';
            $thumbWidth = '310';

            $this->uploadImageOnServer($imageFile, $imageName, $imageSubdir, $imageWidth, $thumbWidth);
            $event->image = $imageName;
        } else {
            $event->image = $request->get('image');
        }

        // Support columns for homepage search (we need this to show events in HP with less use of resources)
        /*$event->sc_country_id = $venue->country_id;
        $event->sc_region_id = $venue->region_id;
        $event->sc_country_name = $countries[$venue->country_id];
        $event->sc_city_name = $venue->city;
        $event->sc_venue_name = $venue->venue_name;*/
        $event->sc_teachers_id = json_encode(explode(',', $request->get('multiple_teachers'))); // keep just this SC
        /*$event->sc_continent_id = $venue->continent_id;*/

        // Multiple teachers - populate support column field
        $event->sc_teachers_names = '';
        if ($request->get('multiple_teachers')) {
            $multiple_teachers = explode(',', $request->get('multiple_teachers'));

            $multiple_teachers_names = [];
            foreach ($multiple_teachers as $key => $teacher_id) {
                $multiple_teachers_names[] = $teachers[$teacher_id];
            }

            $event->sc_teachers_names .= LaravelEventsCalendar::getStringFromArraySeparatedByComma($multiple_teachers_names);
        }

        // Set the Event attributes about repeating (repeat until field and multiple days)
        $event = $this->setEventRepeatFields($request, $event);

        // Save event and repetitions
        $event->save();
        $this->saveEventRepetitions($request, $event);

        // Update multi relationships with teachers and organizers tables.
        if ($request->get('multiple_teachers')) {
            $multiple_teachers = explode(',', $request->get('multiple_teachers'));
            $event->teachers()->sync($multiple_teachers);
        } else {
            $event->teachers()->sync([]);
        }
        if ($request->get('multiple_organizers')) {
            $multiple_organizers = explode(',', $request->get('multiple_organizers'));
            $event->organizers()->sync($multiple_organizers);
        } else {
            $event->organizers()->sync([]);
        }
    }

    /***********************************************************************/

    /**
     * Get creator email.
     *
     * @param  int $created_by
     * @return \Illuminate\Foundation\Auth\User
     */
    public function getCreatorEmail($created_by)
    {
        $creatorEmail = DB::table('users')  // Used to send the Report misuse (not in english)
                ->select('email')
                ->where('id', $created_by)
                ->first();

        $ret = $creatorEmail->email;

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return the event by SLUG. (eg. http://websitename.com/event/xxxx).
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function eventBySlug($slug)
    {
        $event = Event::where('slug', $slug)->first();
        $firstRpDates = Event::getFirstEventRpDatesByEventId($event->id);

        return $this->show($event, $firstRpDates);
    }

    /***************************************************************************/

    /**
     * Return the event by SLUG. (eg. http://websitename.com/event/xxxx/300).
     * @param  string $slug
     * @param  int $repetitionId
     * @return \Illuminate\Http\Response
     */
    public function eventBySlugAndRepetition($slug, $repetitionId)
    {
        $event = Event::where('slug', $slug)->first();
        $firstRpDates = Event::getFirstEventRpDatesByRepetitionId($repetitionId);

        // If not found get the first repetion of the event in the future.
        if (! $firstRpDates) {
            $firstRpDates = Event::getFirstEventRpDatesByEventId($event->id);
        }

        return $this->show($event, $firstRpDates);
    }

    /***************************************************************************/

    /**
     * Return the Event validator with all the defined constraint.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function eventsValidator($request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'venue_id' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
            'repeat_until' => Rule::requiredIf($request->repeat_type == 2 || $request->repeat_type == 3),
            'repeat_weekly_on_day' => Rule::requiredIf($request->repeat_type == 2),
            'on_monthly_kind' => Rule::requiredIf($request->repeat_type == 3),
            'contact_email' => 'nullable|email',
            'facebook_event_link' => 'nullable|url',
            'website_event_link' => 'nullable|url',
            // 'image' => 'nullable|image|mimes:jpeg,jpg,png|max:3000', // BUG create problems to validate on edit. Fix this after the rollout
        ];
        if ($request->hasFile('image')) {
            $rules['image'] = 'nullable|image|mimes:jpeg,jpg,png|max:5000';
        }

        $messages = [
            'repeat_weekly_on_day[].required' => 'Please specify which day of the week is repeting the event.',
            'on_monthly_kind.required' => 'Please specify the kind of monthly repetion',
            'endDate.same' => 'If the event is repetitive the start date and end date must match',
            'facebook_event_link.url' => 'The facebook link is invalid. It should start with https://',
            'website_event_link.url' => 'The website link is invalid. It should start with https://',
            'image.max' => 'The maximum image size is 5MB. If you need to resize it you can use: www.simpleimageresizer.com',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // End date and start date must match if the event is repetitive
        $validator->sometimes('endDate', 'same:startDate', function ($input) {
            return $input->repeat_type > 1;
        });

        return $validator;
    }
}
