<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    $tenant = tenant();

    return response()->json([
        'pong' => true,
        'tenant' => $tenant?->subdomain,
    ]);
})->name('api.ping');
