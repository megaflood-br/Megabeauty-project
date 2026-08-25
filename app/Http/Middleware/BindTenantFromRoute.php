<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\Exceptions\TenantInactiveException;
use App\Tenancy\Exceptions\TenantNotFoundException;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class BindTenantFromRoute
{
    public function __construct(
        private readonly TenantContext $context,
    ) {}

    /**
     * Bind tenant from the {tenantSubdomain} route parameter (useful on localhost).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('tenantSubdomain');

        if (! is_string($subdomain) || $subdomain === '') {
            return $next($request);
        }

        $tenant = Tenant::query()->where('subdomain', $subdomain)->first();

        if ($tenant === null) {
            throw new TenantNotFoundException($subdomain);
        }

        if (! $tenant->isAccessible()) {
            throw new TenantInactiveException($tenant);
        }

        $this->context->set($tenant);
        $request->attributes->set('tenant', $tenant);
        app()->instance(Tenant::class, $tenant);

        return $next($request);
    }
}
