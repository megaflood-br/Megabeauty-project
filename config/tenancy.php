<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Central domains
    |--------------------------------------------------------------------------
    |
    | Hosts that belong to the platform itself (marketing site, central admin,
    | health checks). Requests to these hosts do not require a tenant.
    |
    */
    'central_domains' => array_values(array_filter(array_map(
        static fn (string $domain): string => strtolower(trim($domain)),
        explode(',', (string) env('TENANCY_CENTRAL_DOMAINS', 'localhost,127.0.0.1')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Reserved subdomains
    |--------------------------------------------------------------------------
    |
    | First-level labels that must never be treated as a tenant subdomain
    | (e.g. www.megabeauty.test is the central site, not a salon).
    |
    */
    'reserved_subdomains' => [
        'www',
        'api',
        'admin',
        'app',
        'mail',
        'cdn',
        'static',
        'status',
        'docs',
    ],
];
