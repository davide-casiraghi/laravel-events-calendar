<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use DavideCasiraghi\LaravelEventsCalendar\Models\Country;
use Illuminate\Http\Request;
use Validator;

class CountryController extends Controller
{
    /* Restrict the access to this resource just to logged in users */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /***************************************************************************/

    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $continents = Continent::getContinents();

        $searchKeywords = $request->input('keywords');
        if ($searchKeywords) {
            $countries = Country::orderBy('name')
                                ->where('name', 'like', '%'.$request->input('keywords').'%')
                                ->paginate(20);
        } else {
            $countries = Country::orderBy('name')
                                ->paginate(20);
        }

        return view('laravel-events-calendar::countries.index', compact('countries'))
            ->with('i', (request()->input('page', 1) - 1) * 20)
            ->with('continents', $continents)
            ->with('searchKeywords', $searchKeywords);
    }

    /***************************************************************************/

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $continents = Continent::getContinents();

        return view('laravel-events-calendar::countries.create')
                    ->with('continents', $continents);
    }

    /***************************************************************************/

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
            'code' => 'required',
            'continent_id' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $country = new Country();
        $country->name = $request->get('name');
        $country->code = $request->get('code');
        $country->continent_id = $request->get('continent_id');

        $country->save();

        return redirect()->route('countries.index')
                        ->with('success', __('laravel-events-calendar::messages.country_added_successfully'));
    }

    /***************************************************************************/

    /**
     * Display the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Country  $country
     * @return \Illuminate\View\View
     */
    public function show(Country $country)
    {
        return view('laravel-events-calendar::countries.show', compact('country'));
    }

    /***************************************************************************/

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Country  $country
     * @return \Illuminate\View\View
     */
    public function edit(Country $country)
    {
        $continents = Continent::getContinents();

        return view('laravel-events-calendar::countries.edit', compact('country'))->with('continents', $continents);
    }

    /***************************************************************************/

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Country $country)
    {
        request()->validate([
            'name' => 'required',
            'code' => 'required',
            'continent_id' => 'required',
        ]);

        $country->update($request->all());

        return redirect()->route('countries.index')
                        ->with('success', __('laravel-events-calendar::messages.country_updated_successfully'));
    }

    /***************************************************************************/

    /**
     * Remove the specified resource from storage.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('countries.index')
                        ->with('success', __('laravel-events-calendar::messages.country_deleted_successfully'));
    }

    /***************************************************************************/
}
