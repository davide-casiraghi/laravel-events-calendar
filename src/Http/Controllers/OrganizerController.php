<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Http\Controllers;

use DavideCasiraghi\LaravelEventsCalendar\Models\Organizer;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Validator;

class OrganizerController extends Controller
{
    /* Restrict the access to this resource just to logged in users except show view */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'organizerBySlug']]);
    }

    /***************************************************************************/

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // To show just the organizers created by the the user - If admin or super admin is set to null show all the organizers
        $authorUserId = ($this->getLoggedAuthorId()) ? $this->getLoggedAuthorId() : null; // if is 0 (super admin or admin) it's setted to null to avoid include it in the query

        $searchKeywords = $request->input('keywords');

        if ($searchKeywords) {
            $organizers = DB::table('organizers')
                ->when($authorUserId, function ($query, $authorUserId) {
                    return $query->where('created_by', $authorUserId);
                })
                ->when($searchKeywords, function ($query, $searchKeywords) {
                    return $query->where('name', $searchKeywords)->orWhere('name', 'like', '%'.$searchKeywords.'%');
                })
                ->orderBy('name')
                ->paginate(20);
        } else {
            $organizers = DB::table('organizers')
            ->when($authorUserId, function ($query, $authorUserId) {
                return $query->where('created_by', $authorUserId);
            })
            ->orderBy('name')
            ->paginate(20);
        }

        return view('laravel-events-calendar::organizers.index', compact('organizers'))
            ->with('i', (request()->input('page', 1) - 1) * 20)
            ->with('searchKeywords', $searchKeywords);
    }

    /***************************************************************************/

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::pluck('name', 'id');
        $authorUserId = $this->getLoggedAuthorId();

        return view('laravel-events-calendar::organizers.create')
            ->with('users', $users)
            ->with('authorUserId', $authorUserId);
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
        $validator = $this->organizersValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $organizer = new Organizer();
        $this->saveOnDb($request, $organizer);

        return redirect()->route('organizers.index')
                        ->with('success', __('laravel-events-calendar::messages.organizer_added_successfully'));
    }

    /***************************************************************************/

    /**
     * Display the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Organizer  $organizer
     * @return \Illuminate\Http\Response
     */
    public function show(Organizer $organizer)
    {
        return view('laravel-events-calendar::organizers.show', compact('organizer'));
    }

    /***************************************************************************/

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Organizer  $organizer
     * @return \Illuminate\Http\Response
     */
    public function edit(Organizer $organizer)
    {
        $authorUserId = $this->getLoggedAuthorId();
        $users = User::pluck('name', 'id');

        return view('laravel-events-calendar::organizers.edit', compact('organizer'))
            ->with('users', $users)
            ->with('authorUserId', $authorUserId);
    }

    /***************************************************************************/

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Organizer  $organizer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Organizer $organizer)
    {
        // Validate form datas
        $validator = $this->organizersValidator($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->saveOnDb($request, $organizer);

        return redirect()->route('organizers.index')
                        ->with('success', __('laravel-events-calendar::messages.organizer_updated_successfully'));
    }

    /***************************************************************************/

    /**
     * Remove the specified resource from storage.
     *
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Organizer  $organizer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organizer $organizer)
    {
        $organizer->delete();

        return redirect()->route('organizers.index')
                        ->with('success', __('laravel-events-calendar::messages.organizer_deleted_successfully'));
    }

    /***************************************************************************/

    /**
     * Save the record on DB.
     * @param  \Illuminate\Http\Request  $request
     * @param  \DavideCasiraghi\LaravelEventsCalendar\Models\Organizer  $organizer
     * @return void
     */
    public function saveOnDb(Request $request, Organizer $organizer)
    {
        $organizer->name = $request->get('name');
        $organizer->description = clean($request->get('description'));
        $organizer->website = $request->get('website');
        $organizer->email = $request->get('email');
        $organizer->phone = $request->get('phone');

        // Organizer profile picture upload
        if ($request->file('profile_picture')) {
            $imageFile = $request->file('profile_picture');
            $imageName = $imageFile->hashName();
            $imageSubdir = 'organizers_profile';
            $imageWidth = '968';
            $thumbWidth = '300';

            $this->uploadImageOnServer($imageFile, $imageName, $imageSubdir, $imageWidth, $thumbWidth);
            $organizer->profile_picture = $imageName;
        } else {
            $organizer->profile_picture = $request->profile_picture;
        }

        //$organizer->created_by = Auth::id();
        $organizer->created_by = $request->get('created_by');
        if (! $organizer->slug) {
            $organizer->slug = Str::slug($organizer->name, '-').'-'.rand(10000, 100000);
        }

        $organizer->save();

        return $organizer->id;
    }

    /***************************************************************************/

    /**
     * Open a modal in the event view when 'create new organizer' button is clicked.
     *
     * @return \Illuminate\Http\Response
     */
    public function modal()
    {
        $users = User::pluck('name', 'id');

        return view('laravel-events-calendar::organizers.modal')
                    ->with('users', $users);
    }

    /***************************************************************************/

    /**
     * Store a newly created organizer from the create event view modal in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeFromModal(Request $request)
    {
        $organizer = new Organizer();

        $organizerId = $this->saveOnDb($request, $organizer);
        $organizer = Organizer::find($organizerId);

        return response()->json([
            'organizerId' => $organizerId,
            'organizerName' => $organizer->name,
        ]);
    }

    /***************************************************************************/

    /**
     * Return the organizer by SLUG. (eg. http://websitename.com/organizer/xxxxx).
     *
     * @param  string $slug
     * @return \Illuminate\Http\Response
     */
    public function organizerBySlug($slug)
    {
        $organizer = Organizer::
                where('slug', $slug)
                ->first();

        return $this->show($organizer);
    }

    /***************************************************************************/

    /**
     * Return the validator with all the defined constraint.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Validation\Validator
     */
    public function organizersValidator($request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'website' => 'nullable|url',
        ];
        $messages = [
            'website.url' => 'The website link is invalid. It should start with https://',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        return $validator;
    }
}
