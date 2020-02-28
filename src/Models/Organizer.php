<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Database\Eloquent\Model;

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
     * Get the user that owns the event. eg. $organizer->user
     */
    public function user()
    {
        return $this->belongsTo('\Illuminate\Foundation\Auth\User', 'created_by');
    }
}
