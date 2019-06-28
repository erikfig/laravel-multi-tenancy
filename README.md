# Laravel Multi-Tenancy

This package allows you to create applications for multiple customers with minimal changes in your application
Status: Development

## Install

First, install the package:

```
composer require erikfig/laravel-tenancy
```

Then, publish it:

```
php artisan vendor:publish --provider=ErikFig\Laravel\Tenancy\Providers\TenancyServiceProvider
```

It will be created 2 files:

 - config/tenancy.php
 - routes/tenancy.php
 
 
After that, migrate to create the tables in your DB

```
php artisan migrate
```

## Routes

The new routes file (`routes/tenancy.php`) allows only the sub domain routes, without the "www" by standard.

On this file, it must have the routes that will be part of the your project, making separated routes for your main domain (for a site or  marketplace, for example)


This is the raw file:

```
<?php

Route::get('/', function () {
    return 'App Multi-Tenancy home';
});

Route::get('/tenancy', function () {
    return 'App Multi-Tenancy';
});

```

## Configurations

The file `config/tenancy.php` has this parameters:

```
route_param: Name of the sub domain parameter, change it only to don't get incompatibilities
subdomains_pattern: Regex of the url, removes the `www` from the subdomains.
domain: The main domain of the project (without www), if it gets NULL, it will use the `url` of `config/app.php`.
userModel: One string with name class (including namespace) of the model of authentication (In case you changed the default).
```

## Helper

If in any momemt you need the actual sub domain, use:

```
get_subdomain();
```

## Configurating the model of authentication

To configurate the model (normally on App\User) , add the trait
`ErikFig\Laravel\Tenancy\UserTenancyTrait`:

```
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use ErikFig\Laravel\Tenancy\UserTenancyTrait;

class User extends Authenticatable
{
    use Notifiable;
    use UserTenancyTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

}

```

The table `pivot` will return on the pivot item when you request the data:

```
\App\User::with('tenancies')->get()
```

## Filterign the model record's by sub domain

The following trait is capable of to deal with data and list this informations in each sub domain automatically, you only need to include:

```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use ErikFig\Laravel\Tenancy\TenancyTrait;

class Product extends Model
{
    use TenancyTrait;
}

```

The filtering happens becaus of the scope `tenancy`, so you can handle with it normally.

```
$all_products = \App\Product::withoutGlobalScope('tenancy)->get();
```

## Middleware

The middleware  only gives the acess to autorized users on the current domain

To use it, adds `app/Http/Kernel.php`, on the `$routeMiddleware` variable:

```
'tenancy' => \ErikFig\Laravel\Tenancy\Http\Middleware\TenancyAuthenticate::class,
```

Use it together with the authentication middleware:

```
Route::middleware(['auth', 'tenancy'])->post('/tenancy', function () {
```

You also can adds the roles of the users, and where they can go:

```
Route::middleware(['auth', 'tenancy'])->post('/tenancy', function () {
    return 'Everyone gets the acess';
});

Route::middleware(['auth', 'tenancy:owner'])->post('/tenancy', function () {
    return 'only users with owner role can acess';
});

Route::middleware(['auth', 'tenancy:employee'])->post('/tenancy', function () {
    return 'only users with employee role can acess';
});

Route::middleware(['auth', 'tenancy:owner|employee'])->post('/tenancy', function () {
    return 'only users with owner role and employee role can acess';
});

```

## Managing tenancies

The tenancies seriam "as empresas", desta forma será possível você incluir multiplos usuários em multiplos tenancies.

To register one tenancy:


```
use ErikFig\Laravel\Tenancy\Tenancy;

$owner = \Auth::user();
Tenancy::newTenancy('Nome da empresa', 'subdominio', $owner);
```
The user will get the "owner" role, you can filter the users by the type on the middleware.


To adds an user on a tenancy:
```
use ErikFig\Laravel\Tenancy\Tenancy;

$user = \Auth::user();

$tenancy = Tenancy:where('route', get_subdomain())->first();
$tenancy->attachUser($user, 'editor');
```

The logged user gets a list of tenancies wich it has been registered/created:

```
dd($user->tenancies);
```

The attribute `tenancies` is a `belongsToMany`, so feel free to use it

## Contributions

Send your PR!

 To see suggest features/bugs use the [Issues](https://github.com/erikfig/laravel-multi-tenancy/issues).
