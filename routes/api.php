<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::post('/enviro/saveReading', [App\Http\Controllers\EnviroController::class, 'invoke'])
    ->middleware([App\Http\Middleware\AuthenticateSensor::class, 'throttle:api']);
