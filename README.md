# Laravel Multi-Tenancy

Este pacote permite que você crie aplicações para multiplos clientes com mínimas alterações na sua aplicação.

## Instalação

Primeiramente instale o pacote:

```
composer require erikfig/laravel-tenancy
```

Em seguida publique os arquivos de configuração:

```
php artisan vendor:publish --provider=ErikFig\Laravel\Tenancy\Providers\TenancyServiceProvider
```

Os seguintes arquivos serão criados:

 - config/tenancy.php
 - routes/tenancy.php

Para finalizar crie as tabelas no banco de dados.

```
php artisan migrate
```

## Rotas

O novo arquivo de rotas (`routes/tenancy.php`) libera as rotas apenas em subdominios, excluindo `www` por padrão.

Neste arquivo devem ficar as rotas que farão parte do seu projeto, desta forma ainda podemos ter rotas separadas para o domínio principal (para um site ou market place, quem sabe).

Este é o router padrão:

```
<?php

Route::get('/', function () {
    return 'App Multi-Tenancy home';
});

Route::get('/tenancy', function () {
    return 'App Multi-Tenancy';
});

```

## Configurações

O arquivo `config/tenancy.php` possui os seguintes parâmetros:

```
route_param: Nome do parâmetro do subdominio na rota, altere somente para evitar incompatibilidades.
subdomains_pattern: Regex da url, exclui o `www` dos subdominios no formato padrão.
domain: O domínio em que vai rodar o projeto (sem www), se o valor for null, vai usar o que estiver em `url` do `config/app.php`.
userModel: Uma string com nome da classe (incluindo namespace) do model de autenticação (caso você tenha alterado o padrão)
```

## Helper

Se em algum momento precisar do sudominio atual use:

```
get_subdomain();
```

## Configurar o model de autenticação

Para configurar o model de autenticação (normalmente o App\User), adicione o trait `ErikFig\Laravel\Tenancy\UserTenancyTrait`:

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

A tabela `pivot` já vai trazer as permissões no item pivot quando você solicitar os dados:

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
