<?php

    Route::group([
        'namespace' => 'DavideCasiraghi\LaravelEventsCalendar\Http\Controllers',
        'middleware' => 'web',
        'prefix' => LaravelLocalization::setLocale(),
    ], function () {

        /* Event Categories */
        Route::resource('eventCategories', 'EventCategoryController');

        /* Event Categories Translations */
        Route::get('/eventCategoryTranslations/{eventCategoryId}/{languageCode}/create', 'EventCategoryTranslationController@create');
        Route::get('/eventCategoryTranslations/{eventCategoryId}/{languageCode}/edit', 'EventCategoryTranslationController@edit');
        Route::post('/eventCategoryTranslations/store', 'EventCategoryTranslationController@store')->name('eventCategoryTranslations.store');
        Route::put('/eventCategoryTranslations/update', 'EventCategoryTranslationController@update')->name('eventCategoryTranslations.update');
        Route::delete('/eventCategoryTranslations/destroy/{eventCategoryTranslationId}', 'EventCategoryTranslationController@destroy')->name('eventCategoryTranslations.destroy');

        /* Events */
        Route::resource('events', 'EventController');
        Route::get('/event/monthSelectOptions/', 'EventController@calculateMonthlySelectOptions');  // To populate the event repeat by month options
        Route::get('/event/{slug}', 'EventController@eventBySlug')->where('eventBySlug', '[a-z]+')->name('events.eventBySlug');
        Route::get('/event/{slug}/{repeatition}', 'EventController@eventBySlugAndRepetition')->where('eventBySlugAndRepetition', '[a-z]+', '[0-9]+');

        /* Report Misuse */
        Route::post('/misuse', 'EventController@reportMisuse')->name('events.misuse');
        Route::get('/misuse/thankyou', 'EventController@reportMisuseThankyou')->name('events.misuse-thankyou');

        /* Mail to the event organizer */
        Route::post('/mailToOrganizer', 'EventController@mailToOrganizer')->name('events.organizer-message');
        Route::get('/mailToOrganizer/sent', 'EventController@mailToOrganizerSent')->name('events.organizer-sent');

        /* Event Venues */
        Route::resource('eventVenues', 'EventVenueController');
        Route::get('/create-venue/modal/', 'EventVenueController@modal')->name('eventVenues.modal');
        Route::post('/create-venue/modal/', 'EventVenueController@storeFromModal')->name('eventVenues.storeFromModal');

        /* Teachers */
        Route::resource('teachers', 'TeacherController');
        Route::get('/create-teacher/modal/', 'TeacherController@modal')->name('teachers.modal');
        Route::post('/create-teacher/modal/', 'TeacherController@storeFromModal')->name('teachers.storeFromModal');
        Route::get('/teachersDirectory/', 'TeacherController@index')->name('teachers.directory');
        Route::get('/teacher/{slug}', 'TeacherController@teacherBySlug')->where('teacherBySlug', '[a-z]+');

        /* Organizers */
        Route::resource('organizers', 'OrganizerController');
        Route::get('/create-organizer/modal/', 'OrganizerController@modal')->name('organizers.modal');
        Route::post('/create-organizer/modal/', 'OrganizerController@storeFromModal')->name('organizers.storeFromModal');
        Route::get('/organizer/{slug}', 'OrganizerController@organizerBySlug')->where('organizerBySlug', '[a-z]+');

        /* Continents  */
        Route::resource('continents', 'ContinentController');
        Route::get('/update_continents_dropdown/', 'ContinentController@updateContinentsDropdown')->name('continents.updateContinents');

        /* Countries */
        Route::resource('countries', 'CountryController');
        Route::get('/update_countries_dropdown/', 'CountryController@updateCountriesDropdown')->name('countries.updateCountries');

        /* Regions */
        Route::resource('regions', 'RegionController');
        Route::get('/update_regions_dropdown/', 'RegionController@updateRegionsDropdown')->name('regions.updateRegions');

        /* Regions Translations */
        Route::get('/regionTranslations/{regionId}/{languageCode}/create', 'RegionTranslationController@create');
        Route::get('/regionTranslations/{regionId}/{languageCode}/edit', 'RegionTranslationController@edit');
        Route::post('/regionTranslations/store', 'RegionTranslationController@store')->name('regionTranslations.store');
        Route::put('/regionTranslations/update', 'RegionTranslationController@update')->name('regionTranslations.update');
        Route::delete('/regionTranslations/destroy/{eventCategoryTranslationId}', 'RegionTranslationController@destroy')->name('regionTranslations.destroy');

        /* Homepage - Event Search */
        //Route::get('/', 'EventController@index')->name('home');
    });

    /*Route::group(['namespace' => 'DavideCasiraghi\LaravelEventsCalendar\Http\Controllers', 'middleware' => 'auth'], function () {
        // Teachers
        Route::resource('teachers', 'TeacherController');
        Route::get('/create-teacher/modal/', 'TeacherController@modal')->name('teachers.modal');
        Route::post('/create-teacher/modal/', 'TeacherController@storeFromModal')->name('teachers.storeFromModal');
        Route::get('/teachersDirectory/', 'TeacherController@index')->name('teachers.directory');
        Route::get('/teacher/{slug}', 'TeacherController@teacherBySlug')->where('teacherBySlug', '[a-z]+');
    });*/
