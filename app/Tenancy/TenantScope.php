<?php

declare(strict_types=1);

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    /**
     * Apply the tenant isolation constraint.
     *
     * When no tenant is bound, the query is forced to match nothing (fail-closed)
     * so data from other tenants cannot leak through accidental unscoped queries.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = tenant_id();

        if ($tenantId === null) {
            $builder->whereRaw('0 = 1');

            return;
        }

        $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
    }
}
