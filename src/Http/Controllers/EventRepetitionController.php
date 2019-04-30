<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

use Illuminate\Http\Request;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition;

class EventRepetitionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //return view('eventCategories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*request()->validate([
            'name' => 'required'
        ]);

        EventCategory::create($request->all());

        return redirect()->route('eventCategories.index')
                        ->with('success','Event category created successfully.');*/
    }

    /**
     * Display the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition  $eventRepetition
     * @return \Illuminate\Http\Response
     */
    public function show(EventRepetition $eventRepetition)
    {
        //return view('eventCategories.show',compact('eventCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition  $eventCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(EventRepetition $eventRepetition)
    {
        //return view('eventCategories.edit',compact('eventCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\EventRepetition  $eventRepetition
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EventRepetition $eventRepetition)
    {
        /*request()->validate([
            'name' => 'required'
        ]);

        $eventCategory->update($request->all());

        return redirect()->route('eventCategories.index')
                        ->with('success','Event category updated successfully');*/
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory  $eventCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(EventCategory $eventRepetition)
    {
        /*$eventCategory->delete();
        return redirect()->route('eventCategories.index')
                        ->with('success','Event category deleted successfully');*/
    }

    // **********************************************************************
}
