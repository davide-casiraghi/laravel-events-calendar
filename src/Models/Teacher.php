<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Database\Eloquent\Model;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;

use Illuminate\Http\Request; // to remove
use Illuminate\Support\Str;

class Teacher extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teachers';

    /***************************************************************************/

    protected $fillable = [
        'name', 'bio', 'country_id', 'year_starting_practice', 'year_starting_teach', 'significant_teachers', 'profile_picture', 'website', 'facebook', 'created_by', 'slug',
    ];

    /***************************************************************************/

    /**
     * Get the user that owns the event. eg. $teacher->user.
     */
    public function user()
    {
        return $this->belongsTo('\Illuminate\Foundation\Auth\User', 'created_by');
    }

    /***************************************************************************/

    /**
     * Get the events for the teacher.
     */
    public function events()
    {
        return $this->belongsToMany('DavideCasiraghi\LaravelEventsCalendar\Models\Event', 'event_has_teachers', 'teacher_id', 'event_id');
    }

    /***************************************************************************/

    /**
     * Get the events where this teacher is going to teach to.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public static function eventsByTeacher($teacher, $lastestEventsRepetitionsQuery)
    {
        $ret = $teacher->events()
                         ->select('events.title', 'events.category_id', 'events.slug', 'event_venues.name AS venue_name', 'countries.name AS country', 'event_venues.city AS city', 'events.sc_teachers_names', 'event_repetitions.start_repeat', 'event_repetitions.end_repeat')
                         ->join('event_venues', 'event_venues.id', '=', 'events.venue_id')
                         ->join('countries', 'countries.id', '=', 'event_venues.country_id')
                         ->joinSub($lastestEventsRepetitionsQuery, 'event_repetitions', function ($join) {
                             $join->on('events.id', '=', 'event_repetitions.event_id');
                         })
                         ->orderBy('event_repetitions.start_repeat', 'asc')
                         ->get();

        return $ret;
    }
    
    /***************************************************************************/

    /**
     * Save the record on DB.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function preSave(Request $request)
    {
        $this->name = $request->get('name');
        //$this->bio = $request->get('bio');
        $this->bio = clean($request->get('bio'));
        $this->country_id = $request->get('country_id');
        $this->year_starting_practice = $request->get('year_starting_practice');
        $this->year_starting_teach = $request->get('year_starting_teach');
        $this->significant_teachers = $request->get('significant_teachers');

        // Teacher profile picture upload
        if ($request->file('profile_picture')) {
            $imageFile = $request->file('profile_picture');
            $imageName = $imageFile->hashName();
            $imageSubdir = 'teachers_profile';
            $imageWidth = 968;
            $thumbWidth = 300;

            LaravelEventsCalendar::uploadImageOnServer($imageFile, $imageName, $imageSubdir, $imageWidth, $thumbWidth);
            $this->profile_picture = $imageName;
        } else {
            $this->profile_picture = $request->profile_picture;
        }

        $this->website = $request->get('website');
        $this->facebook = $request->get('facebook');

        //$this->created_by = Auth::id();
        $this->created_by = $request->get('created_by');

        if (! $this->slug) {
            $this->slug = Str::slug($this->name, '-').'-'.rand(10000, 100000);
        }
    }

    /***************************************************************************/

}
