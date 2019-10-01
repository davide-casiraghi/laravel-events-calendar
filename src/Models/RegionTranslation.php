<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Database\Eloquent\Model;

class RegionTranslation extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'region_translations';

    /***************************************************************************/

    public $timestamps = false;
    protected $fillable = ['name', 'slug'];
}
