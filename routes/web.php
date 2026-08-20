<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tenant-context', function () {
    $tenant = tenant();

    return response()->json([
        'tenant' => $tenant === null ? null : [
            'id' => $tenant->getKey(),
            'uuid' => $tenant->uuid,
            'name' => $tenant->name,
            'subdomain' => $tenant->subdomain,
        ],
    ]);
})->name('tenant.context');
