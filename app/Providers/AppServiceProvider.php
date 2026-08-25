<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\UnscopedEloquentUserProvider;
use App\Models\Appointment;
use App\Observers\AppointmentObserver;
use App\Services\Agents\AgentPromptBuilder;
use App\Services\Agents\AgentRuntime;
use App\Services\Agents\AgentToolExecutor;
use App\Services\Agents\CatalogSearch;
use App\Services\Evolution\EvolutionApiService;
use App\Services\OpenAI\AppointmentSuggestionService;
use App\Services\OpenAI\OpenAIClient;
use App\Services\OpenAI\OpenAiService;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
        $this->app->singleton(TenantResolver::class);
        $this->app->singleton(OpenAIClient::class);
        $this->app->singleton(AppointmentSuggestionService::class);
        $this->app->singleton(OpenAiService::class);
        $this->app->singleton(EvolutionApiService::class);
        $this->app->singleton(CatalogSearch::class);
        $this->app->singleton(AgentPromptBuilder::class);
        $this->app->singleton(AgentToolExecutor::class);
        $this->app->singleton(AgentRuntime::class);
    }

    public function boot(): void
    {
        Auth::provider('eloquent', function ($app): UnscopedEloquentUserProvider {
            return new UnscopedEloquentUserProvider($app->make('hash'));
        });

        Appointment::observe(AppointmentObserver::class);
    }
}
