<?php

namespace Igniter\Flame\Foundation\Providers;

use File;
use Igniter\Flame\ActivityLog\ActivityLogServiceProvider;
use Igniter\Flame\Currency\CurrencyServiceProvider;
use Igniter\Flame\Pagic\PagicServiceProvider;
use Igniter\Flame\Setting\SettingServiceProvider;
use Igniter\Flame\Support\HelperServiceProvider;
use Igniter\Flame\Translation\TranslationServiceProvider;
use Illuminate\Support\ServiceProvider;

abstract class AppServiceProvider extends ServiceProvider
{
    /**
     * The application instance.
     *
     * @var \Igniter\Flame\Foundation\Application
     */
    protected $app;

    /**
     * @var bool Indicates if loading of the provider is deferred.
     */
    protected $defer = FALSE;

    /**
     * Boot the service provider.
     * @return void
     */
    public function boot()
    {
        if ($module = $this->getModule(func_get_args())) {
            // Register paths for: config, translator, view
            $modulePath = app_path($module);
            $this->loadTranslationsFrom($modulePath.DIRECTORY_SEPARATOR.'language', $module);
            $this->loadViewsFrom($modulePath.DIRECTORY_SEPARATOR.'views', $module);
        }
    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        if ($module = $this->getModule(func_get_args())) {
            $routesFile = app_path($module.'/routes.php');
            if (File::isFile($routesFile))
                require $routesFile;
        }

//        $this->app->register(ConsoleServiceProvider::class);
//        $this->app->register(GeneratorServiceProvider::class);
        $this->app->register(HelperServiceProvider::class);
        $this->app->register(PagicServiceProvider::class);
        $this->app->register(ActivityLogServiceProvider::class);
        $this->app->register(CurrencyServiceProvider::class);
    }

    public function getModule($args)
    {
        $module = (isset($args[0]) and is_string($args[0])) ? $args[0] : null;

        return $module;
    }

    /**
     * Registers a new console (artisan) command
     *
     * @param string $key The command name
     * @param string $class The command class
     *
     * @return void
     */
    public function registerConsoleCommand($key, $class)
    {
        $key = 'command.'.$key;
        $this->app->singleton($key, function ($app) use ($class) {
            return new $class;
        });

        $this->commands($key);
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return [];
    }
}