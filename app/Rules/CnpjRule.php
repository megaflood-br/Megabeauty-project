<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\Documents\Cnpj;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class CnpjRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || ! Cnpj::isValid($value)) {
            $fail('Informe um CNPJ válido.');
        }
    }
}
