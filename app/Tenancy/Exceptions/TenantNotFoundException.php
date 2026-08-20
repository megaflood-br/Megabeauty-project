<?php

declare(strict_types=1);

namespace App\Tenancy\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TenantNotFoundException extends NotFoundHttpException
{
    public function __construct(string $host)
    {
        parent::__construct("No tenant was found for host [{$host}].");
    }
}
