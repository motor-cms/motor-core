<?php

use Illuminate\Support\Facades\Route;
use Motor\Core\Http\Controllers\Api\V2\GlobalSearchController;

Route::middleware(['auth:sanctum', \Motor\Core\Http\Middleware\V2\V2ErrorHandler::class])
    ->prefix('v2')
    ->name('v2.')
    ->group(function () {
        Route::get('global-search', GlobalSearchController::class)
            ->name('global-search');
    });
