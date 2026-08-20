<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SyncFilamentTenant
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    /**
     * Copy the Filament tenant into the application TenantContext so Eloquent
     * global scopes isolate salon data the same way as the rest of the app.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Tenant) {
            $this->context->set($tenant);
        }

        return $next($request);
    }
}
