<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app(TenantContext::class)->forget();
    }

    protected function actingAsTenant(Tenant $tenant): Tenant
    {
        app(TenantContext::class)->set($tenant);

        return $tenant;
    }
}
