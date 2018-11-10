<?php

namespace ErikFig\Laravel\Tenancy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use ErikFig\Laravel\Tenancy\Tenancy;

trait TenancyTrait
{
    protected static function bootTenancyTrait()
    {
        $tenancyParam = get_subdomain();
        $tenancy = Tenancy::where('route', $tenancyParam)->firstOrFail();

        static::created(function(Model $model) use ($tenancy) {

            $model->tenancy()->attach([$tenancy->id]);
        });

        static::addGlobalScope('tenancy', function (Builder $builder) use ($tenancy) {
            $builder->whereHas('tenancy', function ($query) use ($tenancy) {
                $query->where('tenancies.id', $tenancy->id);
            });
        });
    }

    public function tenancy()
    {
        return $this->morphToMany(Tenancy::class, 'rentables');
    }
}
