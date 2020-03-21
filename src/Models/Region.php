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
    public static function getRegionName($regionId): string
    {
        $ret = self::find($regionId)->name;

        return $ret;
    }

    /***************************************************************************/

    /**
     * Return all the countries ordered by name.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRegionsByCountry($countryId)
    {
        $ret = self::join('region_translations', 'regions.id', '=', 'region_translations.region_id')
                    ->where('locale', 'en')
                    ->where('country_id', $countryId)
                    ->orderBy('name')
                    ->pluck('name', 'region_translations.region_id AS id');

        return $ret;
    }
}
