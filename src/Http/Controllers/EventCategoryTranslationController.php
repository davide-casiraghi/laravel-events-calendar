<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class EventCategoryTranslationController extends Controller
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
     * @param int $eventCategoryId
     * @param string $languageCode
     * @return \Illuminate\Http\Response
     */
    public function create(int $eventCategoryId, string $languageCode)
    {
        $selectedLocaleName = $this->getSelectedLocaleName($languageCode);

        return view('laravel-events-calendar::eventCategoryTranslations.create')
                ->with('eventCategoryId', $eventCategoryId)
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

        $eventCategoryTranslation = new EventCategoryTranslation();
        $eventCategoryTranslation->event_category_id = $request->get('event_category_id');
        $eventCategoryTranslation->locale = $request->get('language_code');

        $eventCategoryTranslation->name = $request->get('name');
        $eventCategoryTranslation->slug = Str::slug($eventCategoryTranslation->name, '-');

        $eventCategoryTranslation->save();

        return redirect()->route('eventCategories.index')
                        ->with('success', 'Translation created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\EventCategoryTranslation  $eventCategoryTranslation
     * @return \Illuminate\Http\Response
     */
    /*public function show(EventCategoryTranslation $eventCategoryTranslation)
    {
        //
    }*/

    /**
     * Show the form for editing the specified resource.
     * @param int $eventCategoryId
     * @param int $languageCode
     * @return \Illuminate\Http\Response
     */
    public function edit($eventCategoryId, $languageCode)
    {
        $eventCategoryTranslation = EventCategoryTranslation::where('event_category_id', $eventCategoryId)
                        ->where('locale', $languageCode)
                        ->first();

        $selectedLocaleName = $this->getSelectedLocaleName($languageCode);

        return view('laravel-events-calendar::eventCategoryTranslations.edit', compact('eventCategoryTranslation'))
                    ->with('eventCategoryId', $eventCategoryId)
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

        $eventCategoryTranslation = EventCategoryTranslation::where('id', $request->get('event_category_translation_id'));

        $event_category_t['name'] = $request->get('name');
        $event_category_t['slug'] = Str::slug($request->get('name'), '-');

        $eventCategoryTranslation->update($event_category_t);

        return redirect()->route('eventCategories.index')
                        ->with('success', 'Translation updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $eventCategoryTranslationId
     * @return \Illuminate\Http\Response
     */
    public function destroy($eventCategoryTranslationId)
    {
        $eventCategoryTranslation = EventCategoryTranslation::find($eventCategoryTranslationId);
        $eventCategoryTranslation->delete();

        return redirect()->route('eventCategories.index')
                        ->with('success', __('laravel-events-calendar::messages.event_category_translation_deleted_successfully'));
    }
}
