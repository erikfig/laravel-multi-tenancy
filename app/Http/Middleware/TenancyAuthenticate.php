<?php

namespace ErikFig\Laravel\Tenancy\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class TenancyAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role = null)
    {
        $tenancy = Tenancy::where('route', get_subdomain())->firstOrFail();
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        if (!$user->tenancies) {
            abort(403);
        }

        if (!$user->tenancies->contains('route', get_subdomain())) {
            abort(403);
        }

        if ($role) {
            $roles = explode('|', $role);
            $this->checkRole($roles, $user);
        }

        return $next($request);
    }

    private function checkRole($roles, $user)
    {
        $tenancy = $user->tenancies->firstWhere('route', get_subdomain());

        if (!in_array($tenancy->pivot->role, $roles)) {
            abort(403);
        }
    }
}
