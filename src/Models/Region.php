<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;

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
    protected $fillable = ['country_id','timezone'];
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
