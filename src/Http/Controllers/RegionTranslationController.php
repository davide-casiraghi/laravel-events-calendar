<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

use DavideCasiraghi\LaravelEventsCalendar\Models\RegionTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class RegionTranslationController extends Controller
{
    /* Restrict the access to this resource just to logged in users */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*public function index()
    {
        //
    }*/

    /**
     * Show the form for creating a new resource.
     * @param int $regionId
     * @param int $languageCode
     * @return \Illuminate\Http\Response
     */
    public function create($regionId, $languageCode)
    {
        $selectedLocaleName = $this->getSelectedLocaleName($languageCode);

        return view('laravel-events-calendar::regionTranslations.create')
                ->with('regionId', $regionId)
                ->with('languageCode', $languageCode)
                ->with('selectedLocaleName', $selectedLocaleName);
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
            ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $regionTranslation = new RegionTranslation();
        $regionTranslation->region_id = $request->get('region_id');
        $regionTranslation->locale = $request->get('language_code');

        $regionTranslation->name = $request->get('name');
        $regionTranslation->slug = Str::slug($regionTranslation->name, '-');

        $regionTranslation->save();

        return redirect()->route('regions.index')
                        ->with('success', 'Translation created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\RegionTranslation  $regionTranslation
     * @return \Illuminate\Http\Response
     */
    /*public function show(RegionTranslation $regionTranslation)
    {
        //
    }*/

    /**
     * Show the form for editing the specified resource.
     * @param int $regionId
     * @param int $languageCode
     * @return \Illuminate\Http\Response
     */
    public function edit($regionId, $languageCode)
    {
        $regionTranslation = RegionTranslation::where('region_id', $regionId)
                        ->where('locale', $languageCode)
                        ->first();

        $selectedLocaleName = $this->getSelectedLocaleName($languageCode);

        return view('laravel-events-calendar::regionTranslations.edit', compact('regionTranslation'))
                    ->with('regionId', $regionId)
                    ->with('languageCode', $languageCode)
                    ->with('selectedLocaleName', $selectedLocaleName);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        // Validate form datas
        $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $regionTranslation = RegionTranslation::where('id', $request->get('region_translation_id'));

        $region_t['name'] = $request->get('name');
        $region_t['slug'] = Str::slug($request->get('name'), '-');

        $regionTranslation->update($region_t);

        return redirect()->route('regions.index')
                        ->with('success', 'Translation updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\RegionTranslation  $regionTranslation
     * @return \Illuminate\Http\Response
     */
    public function destroy($regionTranslationId)
    {
        $regionTranslation = RegionTranslation::find($regionTranslationId);
        $regionTranslation->delete();

        return redirect()->route('regions.index')
                        ->with('success', __('laravel-events-calendar::messages.region_translation_deleted_successfully'));
    }
}
