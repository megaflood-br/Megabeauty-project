<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class TenantResolver
{
    /**
     * Identify the tenant for the incoming request.
     *
     * Resolution order:
     *  1. Exact custom domain
     *  2. Subdomain of a configured central domain
     */
    public function resolve(Request $request): ?Tenant
    {
        $host = $this->normalizedHost($request);

        if ($host === '' || $this->isCentralHost($request)) {
            return null;
        }

        $tenant = Tenant::query()
            ->where('custom_domain', $host)
            ->first();

        if ($tenant !== null) {
            return $tenant;
        }

        $subdomain = $this->extractSubdomain($host);

        if ($subdomain === null || $this->isReservedSubdomain($subdomain)) {
            return null;
        }

        return Tenant::query()
            ->where('subdomain', $subdomain)
            ->first();
    }

    public function isCentralHost(Request $request): bool
    {
        $host = $this->normalizedHost($request);

        if ($this->matchesCentralDomain($host)) {
            return true;
        }

        $subdomain = $this->extractSubdomain($host);

        return $subdomain !== null && $this->isReservedSubdomain($subdomain);
    }

    public function extractSubdomain(string $host): ?string
    {
        $host = $this->normalizeHostValue($host);

        foreach ($this->centralDomains() as $central) {
            if ($host === $central) {
                return null;
            }

            $suffix = '.'.$central;

            if (! str_ends_with($host, $suffix)) {
                continue;
            }

            $subdomain = substr($host, 0, -strlen($suffix));

            if ($subdomain === '' || str_contains($subdomain, '.')) {
                return null;
            }

            return $subdomain;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function centralDomains(): array
    {
        /** @var list<string> $configured */
        $configured = config('tenancy.central_domains', []);

        $fromAppUrl = parse_url((string) config('app.url'), PHP_URL_HOST);
        $hosts = array_merge($configured, $fromAppUrl ? [$fromAppUrl] : []);

        $normalized = array_map(
            fn (string $domain): string => $this->normalizeHostValue($domain),
            $hosts,
        );

        return array_values(array_unique(array_filter($normalized)));
    }

    public function isReservedSubdomain(string $subdomain): bool
    {
        /** @var list<string> $reserved */
        $reserved = config('tenancy.reserved_subdomains', []);

        return in_array(strtolower($subdomain), $reserved, true);
    }

    public function matchesCentralDomain(string $host): bool
    {
        $host = $this->normalizeHostValue($host);

        return in_array($host, $this->centralDomains(), true);
    }

    public function normalizedHost(Request $request): string
    {
        $forwarded = $request->headers->get('X-Forwarded-Host');

        if (is_string($forwarded) && $forwarded !== '') {
            $host = Str::before($forwarded, ',');
        } else {
            $host = $request->getHost();
        }

        return $this->normalizeHostValue($host);
    }

    private function normalizeHostValue(string $host): string
    {
        $host = strtolower(trim($host));
        $host = Str::before($host, ':');

        return rtrim($host, '.');
    }
}
