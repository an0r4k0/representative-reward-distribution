<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Phar;
use Yosymfony\Toml\Toml;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Let's read and store the config file
        config(['tribe' => $this->getConfigFileContent()]);

        // Set the app timezone
        if (config('tribe.general.timezone')) {
            config(['app.timezone' => config('tribe.general.timezone')]);
            date_default_timezone_set(config('app.timezone', 'UTC'));
        }

        // Set logging file path
        $logfile = Phar::running()
            ? $_SERVER['HOME'] . '/.paw-tribe/paw-tribe.log'
            : config('logging.channels.single.path');

        config(['logging.channels.single.path' => $logfile]);
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
     * Get config file content
     */
    protected function getConfigFileContent()
    {
        $path = config('path.directory') . '/' . config('path.config_filename');
        return file_exists($path) ? Toml::parseFile($path) : [];
    }
}
