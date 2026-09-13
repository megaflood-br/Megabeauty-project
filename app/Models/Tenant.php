<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AgendaSlotInterval;
use App\Enums\TenantStatus;
use Database\Factories\TenantFactory;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model implements HasCurrentTenantLabel, HasName
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'status',
        'owner_email',
        'phone',
        'timezone',
        'locale',
        'trial_ends_at',
        'settings',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant): void {
            if (blank($tenant->uuid)) {
                $tenant->uuid = (string) Str::uuid();
            }

            if (blank($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->subdomain ?: $tenant->name);
            }
        });
    }

    public function isAccessible(): bool
    {
        return $this->status->isAccessible();
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Estabelecimento';
    }

    public function agendaSlotMinutes(): int
    {
        return AgendaSlotInterval::clamp((int) ($this->settings['agenda_slot_minutes'] ?? AgendaSlotInterval::Fifteen->value));
    }

    public function setAgendaSlotMinutes(int $minutes): void
    {
        $settings = $this->settings ?? [];
        $settings['agenda_slot_minutes'] = AgendaSlotInterval::clamp($minutes);
        $this->settings = $settings;
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Professional, $this>
     */
    public function professionals(): HasMany
    {
        return $this->hasMany(Professional::class);
    }

    /**
     * @return HasMany<Service, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * @return HasMany<Client, $this>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return HasMany<FinancialTransaction, $this>
     */
    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * @return HasOne<EvolutionApiSetting, $this>
     */
    public function evolutionApiSetting(): HasOne
    {
        return $this->hasOne(EvolutionApiSetting::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'trial_ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }
}
