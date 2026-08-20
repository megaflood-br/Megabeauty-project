<?php

declare(strict_types=1);

namespace App\Services\Evolution;

use App\Models\Appointment;
use App\Models\EvolutionApiSetting;
use App\Tenancy\Exceptions\MissingTenantException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

final class EvolutionApiService
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {}

    public function sendText(string $number, string $text, ?EvolutionApiSetting $settings = null): array
    {
        $settings ??= $this->currentSettings();

        if ($settings === null || ! $settings->isConfigured() || ! $settings->is_active) {
            throw new RuntimeException('A Evolution API não está configurada ou está inativa para este estabelecimento.');
        }

        $payload = [
            'number' => $this->normalizeNumber($number),
            'text' => $text,
        ];

        try {
            $response = $this->http
                ->baseUrl(rtrim($settings->url, '/'))
                ->withHeaders([
                    'apikey' => $settings->token,
                ])
                ->acceptJson()
                ->timeout(20)
                ->retry(2, 200, throw: false)
                ->post('/message/sendText/'.$settings->instance_name, $payload);
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Falha ao enviar mensagem na Evolution API: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                "Evolution API retornou HTTP {$response->status()}: {$response->body()}"
            );
        }

        $decoded = $response->json();

        return is_array($decoded) ? $decoded : ['ok' => true];
    }

    public function sendAppointmentReminder(Appointment $appointment): array
    {
        $appointment->loadMissing(['client', 'professional', 'service', 'tenant']);

        $phone = $appointment->client?->phone;

        if (blank($phone)) {
            throw new RuntimeException('O cliente do agendamento não possui telefone.');
        }

        $startsAt = $appointment->starts_at?->timezone($appointment->tenant?->timezone ?? 'America/Sao_Paulo');

        $message = trim(implode("\n", [
            "Olá, {$appointment->client?->name}!",
            'Seu horário foi confirmado:',
            '📅 '.$startsAt?->format('d/m/Y \à\s H:i'),
            '✂️ '.$appointment->service?->name,
            '👤 '.$appointment->professional?->name,
            'Qualquer dúvida, é só responder esta mensagem.',
        ]));

        return $this->sendText((string) $phone, $message);
    }

    private function currentSettings(): ?EvolutionApiSetting
    {
        if (tenant_id() === null) {
            throw new MissingTenantException('Não há tenant no contexto para enviar WhatsApp.');
        }

        return EvolutionApiSetting::query()->first();
    }

    private function normalizeNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if ($digits !== '' && ! str_starts_with($digits, '55')) {
            $digits = '55'.$digits;
        }

        return $digits;
    }
}
