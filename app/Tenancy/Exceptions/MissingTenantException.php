<?php

declare(strict_types=1);

namespace App\Tenancy\Exceptions;

use RuntimeException;

final class MissingTenantException extends RuntimeException
{
    public function __construct(string $message = 'No tenant is bound to the current context.')
    {
        parent::__construct($message);
    }
}
