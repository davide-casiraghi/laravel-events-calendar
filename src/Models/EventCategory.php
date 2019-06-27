<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Models;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class EventCategory extends Model
{
    /***************************************************************************/
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_categories';

    /***************************************************************************/

    use Translatable;

    public $translatedAttributes = ['name', 'slug'];
    protected $fillable = [];
    public $useTranslationFallback = true;

    /***************************************************************************/

    /*
     * Return the category name.
     *
     * @param  int  $categoryId
     * @return string
     */
    public static function getCategoryName($categoryId)
    {
        $ret = self::find($categoryId)->name;

        return $ret;
    }
}
