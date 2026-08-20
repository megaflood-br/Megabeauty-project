<?php

declare(strict_types=1);

namespace App\Support\Agenda;

use App\Enums\FinancialTransactionStatus;
use App\Enums\FinancialTransactionType;
use App\Models\Client;
use App\Models\FinancialTransaction;

final class ClientAgendaSummary
{
    /**
     * @return array{
     *     client: ?Client,
     *     birthday: string,
     *     cashback: string,
     *     credit: string,
     *     openOrders: int,
     *     openPayments: string
     * }
     */
    public static function make(?Client $client): array
    {
        $openQuery = FinancialTransaction::query()
            ->where('client_id', $client?->id)
            ->where('type', FinancialTransactionType::Income)
            ->where('status', FinancialTransactionStatus::Pending);

        $openOrders = $client === null ? 0 : (int) (clone $openQuery)->count();
        $openAmount = $client === null ? 0.0 : (float) (clone $openQuery)->sum('amount');

        return [
            'client' => $client,
            'birthday' => self::birthdayLabel($client),
            'cashback' => 'R$ 0,00',
            'credit' => 'R$ 0,00',
            'openOrders' => $openOrders,
            'openPayments' => 'R$ '.number_format($openAmount, 2, ',', '.'),
        ];
    }

    private static function birthdayLabel(?Client $client): string
    {
        if ($client?->birth_date === null) {
            return 'Não informado';
        }

        $label = $client->birth_date->format('d/m');
        $age = $client->age();

        return $age !== null ? "{$label} · {$age} anos" : $label;
    }
}
