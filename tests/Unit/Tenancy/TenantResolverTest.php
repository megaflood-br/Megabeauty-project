<?php

declare(strict_types=1);

namespace Tests\Unit\Tenancy;

use App\Tenancy\TenantResolver;
use Illuminate\Http\Request;
use Tests\TestCase;

final class TenantResolverTest extends TestCase
{
    public function test_extracts_single_label_subdomain_from_central_domain(): void
    {
        $resolver = app(TenantResolver::class);

        $this->assertSame('salao-ana', $resolver->extractSubdomain('salao-ana.localhost'));
        $this->assertNull($resolver->extractSubdomain('localhost'));
        $this->assertNull($resolver->extractSubdomain('foo.bar.localhost'));
    }

    public function test_normalizes_host_and_strips_port(): void
    {
        $resolver = app(TenantResolver::class);
        $request = Request::create('http://Salao-Ana.localhost:8000/agenda');

        $this->assertSame('salao-ana.localhost', $resolver->normalizedHost($request));
        $this->assertFalse($resolver->isCentralHost($request));
    }

    public function test_localhost_is_a_central_host(): void
    {
        $resolver = app(TenantResolver::class);
        $request = Request::create('http://localhost/');

        $this->assertTrue($resolver->isCentralHost($request));
        $this->assertTrue($resolver->isReservedSubdomain('www'));
    }
}
