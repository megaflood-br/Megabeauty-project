<?php

declare(strict_types=1);

namespace App\Support\Documents;

final class Cnpj
{
    public static function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public static function format(string $value): string
    {
        $digits = self::digits($value);

        if (strlen($digits) !== 14) {
            return $value;
        }

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 3),
            substr($digits, 5, 3),
            substr($digits, 8, 4),
            substr($digits, 12, 2),
        );
    }

    public static function isValid(string $value): bool
    {
        $digits = self::digits($value);

        if (strlen($digits) !== 14) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $digits) === 1) {
            return false;
        }

        $first = self::checkDigit(substr($digits, 0, 12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $second = self::checkDigit(substr($digits, 0, 12).$first, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return $digits === substr($digits, 0, 12).$first.$second;
    }

    /**
     * @param  list<int>  $weights
     */
    private static function checkDigit(string $base, array $weights): string
    {
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += (int) $base[$index] * $weight;
        }

        $remainder = $sum % 11;

        return (string) ($remainder < 2 ? 0 : 11 - $remainder);
    }
}
