<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Documents\Cnpj;
use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\CompanyProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    /** @use HasFactory<CompanyProfileFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'legal_name',
        'trade_name',
        'cnpj',
        'state_registration',
        'municipal_registration',
        'email',
        'phone',
        'whatsapp',
        'website',
        'instagram',
        'facebook',
        'tiktok',
        'logo_path',
        'address_zip',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'latitude',
        'longitude',
        'about',
        'policies',
        'opening_hours',
        'payment_methods',
    ];

    protected static function booted(): void
    {
        static::saving(function (CompanyProfile $profile): void {
            if (is_string($profile->cnpj)) {
                $digits = Cnpj::digits($profile->cnpj);
                $profile->cnpj = $digits === '' ? null : $digits;
            }

            if (is_string($profile->address_zip)) {
                $zip = preg_replace('/\D+/', '', $profile->address_zip) ?? '';
                $profile->address_zip = $zip === '' ? null : $zip;
            }

            if (is_string($profile->address_state) && $profile->address_state !== '') {
                $profile->address_state = strtoupper($profile->address_state);
            }
        });
    }

    public function displayName(): string
    {
        return $this->trade_name ?: $this->legal_name ?: (tenant()?->name ?? 'Estabelecimento');
    }

    public function formattedCnpj(): ?string
    {
        return $this->cnpj ? Cnpj::format($this->cnpj) : null;
    }

    public function formattedAddress(): ?string
    {
        $parts = array_filter([
            trim(implode(', ', array_filter([
                $this->address_street,
                $this->address_number,
                $this->address_complement,
            ], static fn (?string $value): bool => filled($value)))),
            $this->address_neighborhood,
            trim(implode('/', array_filter([
                $this->address_city,
                $this->address_state,
            ], static fn (?string $value): bool => filled($value)))),
            $this->address_zip ? 'CEP '.$this->address_zip : null,
        ], static fn (?string $value): bool => filled($value));

        return $parts === [] ? null : implode(' — ', $parts);
    }

    /**
     * Compact card injected into the agent prompt. Details stay behind tools.
     *
     * @return array<string, mixed>
     */
    public function promptCard(): array
    {
        return array_filter([
            'nome' => $this->displayName(),
            'razao_social' => $this->legal_name,
            'cnpj' => $this->formattedCnpj(),
            'cidade' => $this->address_city,
            'estado' => $this->address_state,
            'telefone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toToolPayload(): array
    {
        return [
            'nome_fantasia' => $this->trade_name,
            'razao_social' => $this->legal_name,
            'cnpj' => $this->formattedCnpj(),
            'inscricao_estadual' => $this->state_registration,
            'inscricao_municipal' => $this->municipal_registration,
            'email' => $this->email,
            'telefone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'website' => $this->website,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'tiktok' => $this->tiktok,
            'endereco' => $this->formattedAddress(),
            'cidade' => $this->address_city,
            'estado' => $this->address_state,
            'cep' => $this->address_zip,
            'sobre' => $this->about,
            'politicas' => $this->policies,
            'horario_funcionamento' => $this->opening_hours,
            'formas_pagamento' => $this->payment_methods,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'opening_hours' => 'array',
            'payment_methods' => 'array',
        ];
    }
}
