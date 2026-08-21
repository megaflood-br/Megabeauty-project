<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'avatar_path',
        'nickname',
        'email',
        'phone',
        'landline',
        'document',
        'cnpj',
        'rg',
        'notes',
        'birth_date',
        'source',
        'referred_by_client_id',
        'hashtags',
        'dependents',
        'address_zip',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'instagram',
        'facebook',
        'tiktok',
        'default_discount_percent',
        'default_discount_apply_on',
        'is_active',
        'notifications_enabled',
        'access_blocked',
    ];

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
     * @return BelongsTo<Client, $this>
     */
    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_client_id');
    }

    /**
     * @return HasMany<Client, $this>
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by_client_id');
    }

    /**
     * @param  Builder<Client>  $query
     * @return Builder<Client>
     */
    public function scopeSchedulable(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $letters = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr((string) $part, 0, 1));
        }

        return $letters !== '' ? $letters : 'C';
    }

    public function formattedPhone(): string
    {
        $digits = preg_replace('/\D+/', '', $this->phone) ?? '';

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6));
        }

        return $this->phone;
    }

    public function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', $this->phone) ?? '';

        if (strlen($digits) < 10) {
            return null;
        }

        if (! str_starts_with($digits, '55')) {
            $digits = '55'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }

    public function age(): ?int
    {
        return $this->birth_date?->age;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hashtags' => 'array',
            'dependents' => 'array',
            'default_discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
            'notifications_enabled' => 'boolean',
            'access_blocked' => 'boolean',
        ];
    }
}
