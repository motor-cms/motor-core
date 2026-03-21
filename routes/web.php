<?php

use Motor\Core\Http\Controllers\ArtisanCommandsController;

Route::get('artisan/commands', [ArtisanCommandsController::class, 'index'])
    ->middleware(['auth.basic', 'can:viewAdminDashboard']);
Route::get('artisan/commands/{command}', [ArtisanCommandsController::class, 'execute'])
    ->middleware(['auth.basic', 'can:viewAdminDashboard']);
