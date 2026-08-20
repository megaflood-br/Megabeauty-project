<?php

declare(strict_types=1);

namespace App\Tenancy\Exceptions;

use App\Models\Tenant;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class TenantInactiveException extends HttpException
{
    public function __construct(Tenant $tenant)
    {
        parent::__construct(
            403,
            "Tenant [{$tenant->subdomain}] is not accessible (status: {$tenant->status->value})."
        );
    }
}
