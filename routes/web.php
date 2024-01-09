<?php

Route::get('artisan/commands', [\Motor\Core\Http\Controllers\ArtisanCommandsController::class, 'index']);
Route::get('artisan/commands/{command}', [\Motor\Core\Http\Controllers\ArtisanCommandsController::class, 'execute']);
