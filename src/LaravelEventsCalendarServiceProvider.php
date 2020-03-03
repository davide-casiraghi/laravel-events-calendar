<?php

namespace DavideCasiraghi\LaravelEventsCalendar;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
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
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-events-calendar');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-events-calendar');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register middleware
        // https://stackoverflow.com/questions/29599584/laravel-5-register-middleware-from-in-package-service-provider
        // https://stackoverflow.com/questions/45398875/getting-route-in-laravel-packages-middleware

        $this->app['router']->aliasMiddleware('admin', \DavideCasiraghi\LaravelEventsCalendar\Http\Middleware\Admin::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-events-calendar.php'),
            ], 'config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-events-calendar'),
            ], 'views');

            $this->publishes([
                __DIR__.'/../resources/assets/sass' => resource_path('sass/vendor/laravel-events-calendar/'),
            ], 'sass');
            $this->publishes([
                __DIR__.'/../resources/assets/js' => resource_path('js/vendor/laravel-events-calendar/'),
            ], 'js');

            $this->publishes([
                __DIR__.'/../resources/assets/images' => public_path('vendor/laravel-events-calendar/images/'),
            ], 'images');

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-events-calendar'),
            ], 'assets');*/

            // Publishing the translation files.
            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-events-calendar'),
            ], 'lang');

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
                'CreateTeachersTable' => 'create_teachers_table',
            ];

            foreach ($migrations as $migrationFunctionName => $migrationFileName) {
                if (! class_exists($migrationFunctionName)) {
                    $this->publishes([
                        __DIR__.'/../database/migrations/'.$migrationFileName.'.php.stub' => database_path('migrations/'.Carbon::now()->format('Y_m_d_Hmsu').'_'.$migrationFileName.'.php'),
                    ], 'migrations');
                }
            }

            /* Seeders */
            $this->publishes([
                __DIR__.'/../database/seeds/ContinentsTableSeeder.php' => database_path('seeds/ContinentsTableSeeder.php'),
            ], 'seed-continents');
            $this->publishes([
                __DIR__.'/../database/seeds/CountriesTableSeeder.php' => database_path('seeds/CountriesTableSeeder.php'),
            ], 'seed-countries');
            $this->publishes([
                __DIR__.'/../database/seeds/EventCategoriesTableSeeder.php' => database_path('seeds/EventCategoriesTableSeeder.php'),
            ], 'seed-event-categories');
            
            $this->commands([
                RetrieveAllGpsCoordinates::class  //the console class
            ])
        }

        /* Directives to manage the dates*/
        Blade::directive('date', function ($expression) {
            return "<?php echo date('d/m/Y', strtotime($expression))?>";
        });
        Blade::directive('date_monthname', function ($expression) {
            /*return "<?php echo date('d M Y', strtotime($expression))?>";*/
            return "<?php echo Carbon\Carbon::parse($expression)->isoFormat('D MMM YYYY'); ?>";
        });
        Blade::directive('day', function ($expression) {
            return "<?php echo date('d', strtotime($expression))?>";
        });
        Blade::directive('month', function ($expression) {
            /*return "<?php echo date('M', strtotime($expression))?>";*/
            return "<?php echo Carbon\Carbon::parse($expression)->isoFormat('MMM')?>";
        });
        Blade::directive('time_am_pm', function ($expression) {
            return "<?php echo date('g.i a', strtotime($expression))?>";
        });
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

        /*
     * Register the service provider for the dependency.
     */
        $this->app->register('Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider');
        /*
         * Create aliases for the dependency.
         */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('LaravelLocalization', 'Mcamara\LaravelLocalization\Facades\LaravelLocalization');
    }
}
