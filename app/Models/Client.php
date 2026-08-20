<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'email',
        'phone',
        'document',
        'notes',
        'birth_date',
        'source',
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
        ];
    }
}
