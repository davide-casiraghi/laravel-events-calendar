<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use DavideCasiraghi\LaravelEventsCalendar\Facades\LaravelEventsCalendar;
use Illuminate\Database\Eloquent\Model;
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

    /***************************************************************************/

    /**
     * Prepare the record to be saved on DB.
     * @param  array  $requestArray
     * @param  \Illuminate\Http\UploadedFile  $profilePicture
     * @return void
     */
    public function preSave(array $requestArray, $profilePicture): void
    {
        $this->name = $requestArray['name'];
        $this->description = clean($requestArray['description']);
        $this->website = $requestArray['website'];
        $this->email = $requestArray['email'];
        $this->phone = $requestArray['phone'];

        // Organizer profile picture upload
        if (!empty($profilePicture)) {
            $imageFile = $profilePicture;
            $imageName = $imageFile->hashName();
            $imageSubdir = 'organizers_profile';
            $imageWidth = 968;
            $thumbWidth = 300;

            LaravelEventsCalendar::uploadImageOnServer($imageFile, $imageName, $imageSubdir, $imageWidth, $thumbWidth);
            $this->profile_picture = $imageName;
        } else {
            if (array_key_exists("profile_picture",$requestArray)){
                $this->profile_picture = $requestArray['profile_picture'];
            }
        }

        //$this->created_by = Auth::id();
        $this->created_by = $requestArray['created_by'];
        if (! $this->slug) {
            $this->slug = Str::slug($this->name, '-').'-'.rand(10000, 100000);
        }
    }
}
