<?php

namespace App\Models\Concerns;

use App\Support\OrganizationContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->organization_id) && app(OrganizationContext::class)->has()) {
                $model->organization_id = app(OrganizationContext::class)->id();
            }
        });

        static::addGlobalScope('organization', function (Builder $builder): void {
            $context = app(OrganizationContext::class);

            if ($context->has()) {
                $builder->where(
                    $builder->getModel()->getTable().'.organization_id',
                    $context->id()
                );
            }
        });
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }
}
