<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Phar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setTimezone();

        $this->setLogging();

        $this->setDatabaseConnection();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Set the timezone
     */
    protected function setTimezone()
    {
        // Set the app timezone
        $timezone = config('settings.timezone', 'UTC');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }

    /**
     * Set the logging file
     */
    protected function setLogging()
    {
        // Set logging file path
        $logfile = Phar::running()
            ? config('settings.directory') . 'application.log'
            : config('logging.channels.single.path');

        config(['logging.channels.single.path' => $logfile]);
    }

    /**
     * Set the database path
     */
    protected function setDatabaseConnection()
    {
        $database = Phar::running()
            ? config('settings.directory') . 'database.sqlite'
            : config('database.connections.sqlite.database');

        config(['database.connections.sqlite.database' => $database]);
    }
}
