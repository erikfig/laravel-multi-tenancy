<?php

namespace ErikFig\Laravel\Tenancy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class Tenancy extends Model
{
    protected $fillable = ['title', 'route', 'role', 'user_id', 'tenancy_id'];
    
    public function users()
    {
        return $this->belongsToMany(config('tenancy.userModel'), 'tenancy_users', 'tenancy_id', 'user_id');
    }

    public static function newTenancy(string $title, string $route, User $user)
    {
        $tenancy = self::create(compact('title', 'route'));

        \DB::table('tenancy_users')->insert([
           'role' => 'owner',
           'user_id' => $user->getAuthIdentifier(),
           'tenancy_id' => $tenancy->id,
        ]);

        return $tenancy;
    }

    public function attachUser(User $user, string $role)
    {
        \DB::table('tenancy_users')->insert([
           'role' => $role,
           'user_id' => $user->getAuthIdentifier(),
           'tenancy_id' => $this->id,
        ]);
    }
}
