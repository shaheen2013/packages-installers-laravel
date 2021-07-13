<?php

use Mediusware\LaravelInstaller\Http\Controllers\LaravelInstallerController;

Route::prefix('/api')->group(function () {
    Route::get('/server-requirements', [LaravelInstallerController::class, 'getServerRequirements'])->name('LaravelInstaller.ServerRequirements');
    Route::get('/directory-permission', [LaravelInstallerController::class, 'getDirectoryPermission'])->name('LaravelInstaller.DirectoryPermission');
    Route::get('/setup/{key}', [LaravelInstallerController::class, 'getSetup'])->name('LaravelInstaller.setup');
    Route::post('/save-setup', [LaravelInstallerController::class, 'saveSetup'])->name('LaravelInstaller.save.setup');
});

Route::get('/{any?}', [LaravelInstallerController::class, 'index'])->where('any', '.*');
