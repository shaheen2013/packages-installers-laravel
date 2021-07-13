<?php

namespace Mediusware\LaravelInstaller;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class LaravelInstallerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel_installer.php', 'LaravelInstaller');

        $this->publishConfig();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'LaravelInstaller');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/laravel-installer'),
        ], 'laravel-installer-public');

        $this->publishes([
                __DIR__ . '/../resources/assets' => resource_path('assets/vendor/laravel-installer')
            ], 'laravel-installer-vue-components');

        $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration('guest'), function () {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        });
    }

    /**
     * Get route group configuration array.
     *
     * @param string $type
     * @return array
     */
    private function routeConfiguration($type = 'guest'): array
    {
        $routePrefix = [
            'guest' => [
                'namespace' => "Mediusware\LaravelInstaller\Http\Controllers",
                'prefix' => 'laravel-installer'
            ],
            'api' => [
                'namespace' => "Mediusware\LaravelInstaller\Http\Controllers\Api",
                'middleware' => 'api',
                'prefix' => 'api'
            ]
        ];

        return $routePrefix[$type];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register facade
        $this->app->singleton('laravelinstaller', function () {
            return new LaravelInstaller;
        });
    }

    /**
     * Publish Config
     *
     * @return void
     */
    public function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel_installer.php' => config_path('laravel_installer.php'),
            ], 'config');
        }
    }
}
