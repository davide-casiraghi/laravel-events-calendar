<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

use Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use DavideCasiraghi\LaravelEventsCalendar\Models\Region;

class RegionController extends Controller
{
    /* Restrict the access to this resource just to logged in users except show view */
    public function __construct()
    {
        $this->middleware('admin', ['except' => ['show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $regions = Region::latest()->paginate(20);

        // Countries available for translations
        $countriesAvailableForTranslations = LaravelLocalization::getSupportedLocales();

        return view('laravel-events-calendar::regions.index', compact('regions'))
            ->with('i', (request()->input('page', 1) - 1) * 20)
            ->with('countriesAvailableForTranslations', $countriesAvailableForTranslations);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('laravel-events-calendar::regions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate form datas
        $validator = Validator::make($request->all(), [
                'name' => 'required',
                'timezone' => 'required',
            ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $region = new Region();

        $this->saveOnDb($request, $region);

        return redirect()->route('regions.index')
                        ->with('success', __('laravel-events-calendar::messages.region_added_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function show(Region $region)
    {
        return view('laravel-events-calendar::regions.show', compact('region'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function edit(Region $region)
    {
        return view('laravel-events-calendar::regions.edit', compact('region'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Region $region)
    {
        request()->validate([
            'name' => 'required',
            'timezone' => 'required',
        ]);

        $this->saveOnDb($request, $region);

        return redirect()->route('regions.index')
                        ->with('success', __('laravel-events-calendar::messages.region_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function destroy(Region $region)
    {
        $region->delete();

        return redirect()->route('regions.index')
                        ->with('success', __('laravel-events-calendar::messages.region_deleted_successfully'));
    }

    // **********************************************************************

    /**
     * Return the single event region datas by cat id.
     *
     * @param  int $cat_id
     * @return \DavideCasiraghi\LaravelEventsCalendar\Models\Region
     */
    /*public function eventregiondata($cat_id)
    {
        $ret = DB::table('regions')->where('id', $cat_id)->first();
        //dump($ret);

        return $ret;
    }*/

    // **********************************************************************

    /**
     * Save/Update the record on DB.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \DavideCasiraghi\LaravelEventsCalendar\Models\Region $region
     * @return void
     */
    public function saveOnDb($request, $region)
    {
        $region->name = $request->get('name');
        $region->code = $request->get('code');
        $region->country_id = $request->get('country_id');
        $region->timezone = $request->get('timezone');
        $region->slug = Str::slug($region->name, '-');

        $region->save();
    }
}
