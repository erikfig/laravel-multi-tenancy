<?php

namespace ErikFig\Laravel\Tenancy\Providers;

use Illuminate\Support\ServiceProvider;
use Route;

class TenancyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->routeRegister();
        $this->publishFiles();

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations/');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/tenancy.php',
            'tenancy'
        );
    }

    private function routeRegister()
    {
        $tenantParam = config('tenancy.route_param');

        if (!$domain = config('tenancy.domain')) {
            $appUrl = config('app.url');
            $domain = parse_url($appUrl)['host'];
        }

        Route::pattern($tenantParam, config('tenancy.subdomains_pattern'));

        $routeFile = __DIR__.'/../../routes/tenancy.php';
        if (file_exists(base_path('routes/tenancy.php'))) {
            $routeFile = base_path('routes/tenancy.php');
        }

        Route::domain("{{$tenantParam}}.$domain")
            ->middleware(['web'])
            ->group($routeFile);
    }

    private function publishFiles()
    {
        $this->publishes([
            __DIR__.'/../../routes' => base_path('routes/'),
            __DIR__.'/../../config' => base_path('config/'),
        ], 'tenancy');
    }
}
