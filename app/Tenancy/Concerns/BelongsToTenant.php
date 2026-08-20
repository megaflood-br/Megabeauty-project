<?php

declare(strict_types=1);

namespace App\Tenancy\Concerns;

use App\Models\Tenant;
use App\Tenancy\Exceptions\MissingTenantException;
use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Model
 *
 * @property int $tenant_id
 * @property-read Tenant $tenant
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('tenant_id') !== null) {
                return;
            }

            $tenantId = tenant_id();

            if ($tenantId === null) {
                throw new MissingTenantException(
                    'Cannot persist ['.static::class.'] without a tenant context or an explicit tenant_id.'
                );
            }

            $model->setAttribute('tenant_id', $tenantId);
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTenant(Builder $query, Tenant|int $tenant): Builder
    {
        $id = $tenant instanceof Tenant ? $tenant->getKey() : $tenant;

        return $query
            ->withoutGlobalScope(TenantScope::class)
            ->where($this->qualifyColumn('tenant_id'), $id);
    }

    /**
     * Bypass tenant isolation. Use only for central/admin operations.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
