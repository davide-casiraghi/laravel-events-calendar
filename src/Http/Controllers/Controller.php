<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

//use DavideCasiraghi\LaravelEventsCalendar\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // https://stackoverflow.com/questions/51611015/authuser-return-null-5-6
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
    }

    // **********************************************************************

    /**
     * Get the current logged user ID.
     * If user is admin or super admin return 0.
     *
     * @return \Illuminate\Foundation\Auth\User $ret
     */
    public function getLoggedUser()
    {
        $user = Auth::user();

        // This is needed to not get error in the queries with: ->when($loggedUser->id, function ($query, $loggedUserId) {
        /*if (! $user) {
            $user = new User();
            $user->name = null;
            $user->group = null;
        }*/

        $ret = $user;

        return $ret;
    }

    // **********************************************************************

    /**
     * Get the current logged user id.
     * (if admin or super admin returns 0).
     *
     * @return int|null $ret
     */
    public function getLoggedAuthorId()
    {
        $user = Auth::user();

        $ret = null;

        if ($user) {
            //@todo - disabled for the tests errors -- still to solve the isSuperAdmin()
            //$ret = (! $user->isSuperAdmin() && ! $user->isAdmin()) ? $user->id : 0;
            $ret = (! $user->group == 1 && ! $user->group == 2) ? $user->id : 0;
        }

        return $ret;
    }

    // **********************************************************************

    /**
     * Get the language name from language code.
     *
     * @param  string $languageCode
     * @return string
     */
    public function getSelectedLocaleName($languageCode)
    {
        $countriesAvailableForTranslations = LaravelLocalization::getSupportedLocales();
        $ret = $countriesAvailableForTranslations[$languageCode]['name'];

        return $ret;
    }
}
