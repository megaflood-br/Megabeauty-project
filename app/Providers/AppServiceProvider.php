<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\OpenAI\AppointmentSuggestionService;
use App\Services\OpenAI\OpenAIClient;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
        $this->app->singleton(TenantResolver::class);
        $this->app->singleton(OpenAIClient::class);
        $this->app->singleton(AppointmentSuggestionService::class);
    }

    public function boot(): void
    {
        //
    }
}
