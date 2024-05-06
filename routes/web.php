<?php

Route::get('artisan/commands', [\Motor\Core\Http\Controllers\ArtisanCommandsController::class, 'index'])
     ->middleware('auth.very_basic');
Route::get('artisan/commands/{command}', [\Motor\Core\Http\Controllers\ArtisanCommandsController::class, 'execute'])
     ->middleware('auth.very_basic');
