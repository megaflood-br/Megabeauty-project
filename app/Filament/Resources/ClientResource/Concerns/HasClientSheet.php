<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Concerns;

use App\Enums\FinancialTransactionStatus;
use App\Enums\FinancialTransactionType;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\FinancialTransaction;
use Illuminate\Support\Collection;

trait HasClientSheet
{
    public string $clientTab = 'cadastro';

    /**
     * @return array<string, array{label: string, badge: ?string}>
     */
    public function clientSheetTabs(): array
    {
        return [
            'cadastro' => ['label' => 'Cadastro', 'badge' => null],
            'painel' => ['label' => 'Painel', 'badge' => null],
            'debitos' => ['label' => 'Débitos', 'badge' => null],
            'creditos' => ['label' => 'Créditos', 'badge' => null],
            'cashback' => ['label' => 'Cashback', 'badge' => null],
            'agendamentos' => ['label' => 'Agendamentos', 'badge' => null],
            'produtos' => ['label' => 'Produtos', 'badge' => 'novo'],
            'vendas' => ['label' => 'Vendas', 'badge' => null],
            'pacotes' => ['label' => 'Pacotes', 'badge' => null],
            'mensagens' => ['label' => 'Mensagens', 'badge' => null],
            'anotacoes' => ['label' => 'Anotações', 'badge' => null],
            'arquivos' => ['label' => 'Imagens e Arquivos', 'badge' => null],
            'anamneses' => ['label' => 'Anamneses', 'badge' => null],
            'assinaturas' => ['label' => 'Vendas por Assinatura', 'badge' => null],
        ];
    }

    public function setClientTab(string $tab): void
    {
        if (! array_key_exists($tab, $this->clientSheetTabs())) {
            return;
        }

        $this->clientTab = $tab;
    }

    public function clientSheetTitle(): string
    {
        $name = data_get($this->data ?? [], 'name');

        if (blank($name)) {
            $name = $this->clientSheetRecord()?->name;
        }

        return filled($name) ? (string) $name : 'Novo cliente';
    }

    public function clientSheetRecord(): ?Client
    {
        $record = $this->getRecord();

        return $record instanceof Client ? $record : null;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function clientSheetAppointments(): Collection
    {
        $client = $this->clientSheetRecord();

        if ($client === null) {
            return collect();
        }

        return Appointment::query()
            ->with(['professional', 'service'])
            ->where('client_id', $client->id)
            ->orderByDesc('starts_at')
            ->limit(20)
            ->get();
    }

    public function clientSheetOpenDebits(): string
    {
        return $this->pendingAmount(FinancialTransactionType::Expense);
    }

    public function clientSheetOpenCredits(): string
    {
        return $this->pendingAmount(FinancialTransactionType::Income);
    }

    private function pendingAmount(FinancialTransactionType $type): string
    {
        $client = $this->clientSheetRecord();

        $amount = $client === null
            ? 0.0
            : (float) FinancialTransaction::query()
                ->where('client_id', $client->id)
                ->where('type', $type)
                ->where('status', FinancialTransactionStatus::Pending)
                ->sum('amount');

        return 'R$ '.number_format($amount, 2, ',', '.');
    }
}
