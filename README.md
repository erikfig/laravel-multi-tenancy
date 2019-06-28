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

## Filtrar os registros de um model por subdominio

O trait a seguir é capaz de lidar com cadastro e listagem de dados em cada subdomínio automaticamente, apenas inclua:

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

A filtragem acontece graças a um scope `tenancy`, então você pode lidar com isso normalmente.

```
$all_products = \App\Product::withoutGlobalScope('tenancy)->get();
```

## Middleware

O middleware restrige o acesso apenas aos usuários liberados no dominio atual.

Para usar adicione ao `app/Http/Kernel.php`, na variável `$routeMiddleware`:

```
'tenancy' => \ErikFig\Laravel\Tenancy\Http\Middleware\TenancyAuthenticate::class,
```

Use em conjunto com o middleware de autenticação:

```
Route::middleware(['auth', 'tenancy'])->post('/tenancy', function () {
```

Você também pode incluir os níveis de usuários que terão acesso as rotas:

```
Route::middleware(['auth', 'tenancy'])->post('/tenancy', function () {
    return 'todos os níveis acessam';
});

Route::middleware(['auth', 'tenancy:owner'])->post('/tenancy', function () {
    return 'somente usuários com role owner acessam';
});

Route::middleware(['auth', 'tenancy:employee'])->post('/tenancy', function () {
    return 'somente usuários com role employee acessam';
});

Route::middleware(['auth', 'tenancy:owner|employee'])->post('/tenancy', function () {
    return 'somente usuários com role owner e employee acessam';
});

```

## Gerenciando tenancies

O tenancies seriam "as empresas", desta forma será possível você incluir multiplos usuários em multiplos tenancies.

Para cadastrar um tenancy:

```
use ErikFig\Laravel\Tenancy\Tenancy;

$owner = \Auth::user();
Tenancy::newTenancy('Nome da empresa', 'subdominio', $owner);
```

O usuário será incluído com o `role` (nível de acesso) owner (dono em inglês), você pode filtrar os usuários por tipo no middleware.

Para adicionar um usuário em um tenancy:

```
use ErikFig\Laravel\Tenancy\Tenancy;

$user = \Auth::user();

$tenancy = Tenancy:where('route', get_subdomain())->first();
$tenancy->attachUser($user, 'editor');
```

O usuário logado recebe uma lista de tenancies aos quais foi cadastrado/criou:

```
dd($user->tenancies);
```

O atributo `tenancies` é um `belongsToMany`, então fique a vontade, rsrs.

## Contribuir

Mande seu PR!

Para ver/sugerir recursos/bugs use o [Issues](https://github.com/erikfig/laravel-multi-tenancy/issues).
