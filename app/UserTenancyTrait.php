<?php

namespace ErikFig\Laravel\Tenancy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use ErikFig\Laravel\Tenancy\Tenancy;

trait UserTenancyTrait
{
    public function tenancies()
    {
        return $this->belongsToMany(Tenancy::class, 'tenancy_users', 'user_id', 'tenancy_id')->withPivot('role');
    }
}
