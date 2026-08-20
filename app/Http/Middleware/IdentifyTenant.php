<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\Exceptions\TenantInactiveException;
use App\Tenancy\Exceptions\TenantNotFoundException;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class IdentifyTenant
{
    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantContext $context,
    ) {}

    /**
     * Bind the current tenant from the request host (subdomain or custom domain).
     *
     * Central hosts (apex domain, reserved subdomains such as www/api) continue
     * without a tenant. Any other host must resolve to an accessible tenant.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->context->forget();
        app()->forgetInstance(Tenant::class);

        $tenant = $this->resolver->resolve($request);

        if ($tenant === null) {
            if ($this->resolver->isCentralHost($request)) {
                return $next($request);
            }

            throw new TenantNotFoundException($this->resolver->normalizedHost($request));
        }

        if (! $tenant->isAccessible()) {
            throw new TenantInactiveException($tenant);
        }

        $this->bind($request, $tenant);

        return $next($request);
    }

    private function bind(Request $request, Tenant $tenant): void
    {
        $this->context->set($tenant);

        $request->attributes->set('tenant', $tenant);
        app()->instance(Tenant::class, $tenant);
    }
}
