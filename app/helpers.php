<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Tenancy\TenantContext;

if (! function_exists('tenant')) {
    /**
     * Resolve the tenant bound to the current request/context.
     */
    function tenant(): ?Tenant
    {
        return app(TenantContext::class)->tenant();
    }
}

if (! function_exists('tenant_id')) {
    /**
     * Resolve the current tenant primary key, if any.
     */
    function tenant_id(): ?int
    {
        return tenant()?->getKey();
    }
}
