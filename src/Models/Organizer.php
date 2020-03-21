<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Database\Eloquent\Model;
use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Organizer extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organizers';

    /***************************************************************************/

    protected $fillable = [
        'name', 'description', 'website', 'created_by', 'slug', 'email', 'phone',
    ];

    /***************************************************************************/

    /**
     * Get the user that owns the event. eg. $this->user.
     */
    public function user()
    {
        return $this->belongsTo('\Illuminate\Foundation\Auth\User', 'created_by');
    }
    
    /**
     * Prepare datas for save model on DB
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function preSave(Request $request){
        $this->name = $request->get('name');
        $this->description = clean($request->get('description'));
        $this->website = $request->get('website');
        $this->email = $request->get('email');
        $this->phone = $request->get('phone');

        // Organizer profile picture upload
        if ($request->file('profile_picture')) {
            $imageFile = $request->file('profile_picture');
            $imageName = $imageFile->hashName();
            $imageSubdir = 'organizers_profile';
            $imageWidth = 968;
            $thumbWidth = 300;

            LaravelEventsCalendar::uploadImageOnServer($imageFile, $imageName, $imageSubdir, $imageWidth, $thumbWidth);
            $this->profile_picture = $imageName;
        } else {
            $this->profile_picture = $request->profile_picture;
        }

        //$this->created_by = Auth::id();
        $this->created_by = $request->get('created_by');
        if (! $this->slug) {
            $this->slug = Str::slug($this->name, '-').'-'.rand(10000, 100000);
        }
    }
}
