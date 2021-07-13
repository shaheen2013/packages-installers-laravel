<?php

namespace Mediusware\LaravelInstaller;

use Illuminate\Support\Facades\Facade;

class LaravelInstallerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-installer';
    }
}
