<?php

namespace DavideCasiraghi\LaravelEventsCalendar;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class LaravelEventsCalendarServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-events-calendar');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-events-calendar');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    
        // Register middleware
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('admin', DavideCasiraghi\LaravelEventsCalendar\Http\Middleware\Admin::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-events-calendar.php'),
            ], 'config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-events-calendar'),
            ], 'views');

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-events-calendar'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-events-calendar'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);

            /* - Migrations -
               create a migration instance for each .php.stub file eg.
               create_continents_table.php.stub --->  2019_04_28_190434761474_create_continents_table.php
            */
            $migrations = [
                     'CreateQuotesTable' => 'create_continents_table',
                     'CreateCountriesTable' => 'create_countries_table',
                     'CreateEventHasOrganizersTable' => 'create_event_has_organizers_table',
                     'CreateEventHasTeachersTable' => 'create_event_has_teachers_table',
                     'CreateEventsTable' => 'create_events_table',
                     'CreateOrganizersTable' => 'create_organizers_table',
                     'CreateEventCategoriesTable' => 'create_event_categories_table',
                     'CreateEventCategoryTranslationsTable' => 'create_event_category_translations_table',
                     'CreateEventRepetitionsTable' => 'create_event_repetitions_table',
                     'CreateEventVenuesTable' => 'create_event_venues_table',
                 ];

            foreach ($migrations as $migrationFunctionName => $migrationFileName) {
                if (! class_exists($migrationFunctionName)) {
                    $this->publishes([
                            __DIR__.'/../database/migrations/'.$migrationFileName.'.php.stub' => database_path('migrations/'.Carbon::now()->format('Y_m_d_Hmsu').'_'.$migrationFileName.'.php'),
                        ], 'migrations');
                }
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-events-calendar');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-events-calendar', function () {
            return new LaravelEventsCalendar;
        });
    }
}
