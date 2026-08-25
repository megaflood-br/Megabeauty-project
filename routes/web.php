<?php

declare(strict_types=1);

use App\Http\Controllers\AgentWidgetController;
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

Route::get('/agente/{agentSlug}', [AgentWidgetController::class, 'show'])
    ->name('agents.widget.hosted');
Route::post('/agente/{agentSlug}/mensagens', [AgentWidgetController::class, 'message'])
    ->middleware('throttle:30,1')
    ->name('agents.widget.hosted.message');

Route::prefix('t/{tenantSubdomain}')
    ->middleware('tenant.from-route')
    ->group(function (): void {
        Route::get('/agente/{agentSlug}', [AgentWidgetController::class, 'show'])
            ->name('agents.widget');
        Route::post('/agente/{agentSlug}/mensagens', [AgentWidgetController::class, 'message'])
            ->middleware('throttle:30,1')
            ->name('agents.widget.message');
    });
