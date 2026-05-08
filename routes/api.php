<?php

use Illuminate\Support\Facades\Route;
use Motor\Core\Http\Controllers\Api\V2\GlobalSearchController;
use Motor\Core\Http\Middleware\ScopeRequestsToClient;
use Motor\Core\Http\Middleware\V2\V2ErrorHandler;

Route::middleware(['auth:sanctum', V2ErrorHandler::class, ScopeRequestsToClient::class])
    ->prefix('v2')
    ->name('v2.')
    ->group(function () {
        Route::get('global-search', GlobalSearchController::class)
            ->name('global-search');
    });
