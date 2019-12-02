<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'regions';

    /***************************************************************************/

    use Translatable;

    public $translatedAttributes = ['name', 'slug'];
    protected $fillable = ['country_id', 'timezone'];
    public $useTranslationFallback = true;

    /***************************************************************************/

    /*
     * Return the region name.
     *
     * @param  int  $regionId
     * @return string
     */
    public static function getRegionName($regionId)
    {
        $ret = self::find($regionId)->name;

        return $ret;
    }
}
