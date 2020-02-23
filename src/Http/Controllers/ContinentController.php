<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

use DavideCasiraghi\LaravelEventsCalendar\Models\Continent;
use Illuminate\Http\Request;
use Validator;

class ContinentController extends Controller
{
    /* Restrict the access to this resource just to logged in users */
    public function __construct()
    {
        //$this->middleware('admin');
        $this->middleware('admin', ['except' => ['updateContinentsDropdown']]);
    }

    /***************************************************************************/

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $continents = Continent::orderBy('name')->paginate(10);

        return view('laravel-events-calendar::continents.index', compact('continents'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }
    
    /***************************************************************************/

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('laravel-events-calendar::continents.create');
    }
    
    /***************************************************************************/

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {

        // Validate form datas
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $continent = new Continent();
        $continent->name = $request->get('name');
        $continent->code = $request->get('code');

        $continent->save();

        return redirect()->route('continents.index')
                        ->with('success', __('laravel-events-calendar::messages.continent_added_successfully'));
    }
    
    /***************************************************************************/

    /**
     * Display the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Continent  $continent
     * @return \Illuminate\View\View
     */
    public function show(Continent $continent)
    {
        return view('laravel-events-calendar::continents.show', compact('continent'));
    }
    
    /***************************************************************************/

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Continent  $continent
     * @return \Illuminate\View\View
     */
    public function edit(Continent $continent)
    {
        return view('laravel-events-calendar::continents.edit', compact('continent'));
    }
    
    /***************************************************************************/

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Continent  $continent
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Continent $continent)
    {
        request()->validate([
            'name' => 'required',
            'code' => 'required',
        ]);

        $continent->update($request->all());

        return redirect()->route('continents.index')
                        ->with('success', __('laravel-events-calendar::messages.continent_updated_successfully'));
    }
    
    /***************************************************************************/

    /**
     * Remove the specified resource from storage.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Continent  $continent
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Continent $continent)
    {
        $continent->delete();

        return redirect()->route('continents.index')
                        ->with('success', __('laravel-events-calendar::messages.continent_deleted_successfully'));
    }
    
    /***************************************************************************/

    /**
     * Return the contient id of the select country
     * after a country get selected.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int $ret
     */
    public function updateContinentsDropdown(Request $request)
    {
        $selectedCountry = Country::find($request->get('country_id'));
        $ret = $selectedCountry->continent_id;

        return $ret;
    }
}
