# Service Providers


## Key Idea:
- register: Register services into container
- boot: Here’s some code I want to run when the app boots.

## How Laravel Loads Service Providers

All service providers are listed in config/app.php:
```php
'providers' => [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    // ... your custom providers
],
```

Laravel loops through providers:
- Calls register() for all of them first
- Calls boot() for all of them next

## Example

```php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ExampleServiceProvider extends ServiceProvider
{
    /**
     * Register services into the container.
     */
    public function register()
    {
        // Bind something into the container
        $this->app->singleton(\App\Services\ExampleService::class, function ($app) {
            return new \App\Services\ExampleService();
        });
    }

    /**
     * Bootstrap services after all providers are registered.
     */
    public function boot()
    {
        // Run code after everything else is loaded
        // e.g., routes, events, or publishing configs
    }
}

```


## Common Use

Binding services or repositories

```php
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);

$this->app->bind(Foo::class, function ($app) {
    return new Foo($app['config']['foo.option']);
});
```

Singletons

```php

$this->app->singleton(PaymentGateway::class, function ($app) {
    return new PaymentGateway(config('payment.api_key'));
});

```

Bootstrapping routes

```php

public function boot()
{
    $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
}

```

Publishing configs, assets, migrations (especially for packages)

```php

$this->publishes([
    __DIR__.'/../Config/package.php' => config_path('package.php')
]);

```


```php

Event::listen(OrderPlaced::class, SendOrderEmail::class);

```


## Publishing Method:

- it set instructions to copy files from package path into core .. 
- this publish dont take effect with normal request.. it only is effective by artisan command
- its optional if you want to customize config/views etc.. so it copy it to your project
- most likely its for configuration file + view files + migrations + assets
- `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag=config`
- then laravel copy vendor/package/Config/package.php  →  config/package.php


```php

public function boot()
{
    $this->publishes([
        __DIR__.'/../Config/package.php' => config_path('package.php'),
    ], 'config');
}

```

### Real world Example


package spatie/laravel-permission.

```php

public function boot()
{
    $this->publishes([
        __DIR__.'/../config/permission.php' => config_path('permission.php'),
    ], 'config');

    $this->publishes([
        __DIR__.'/../database/migrations/' => database_path('migrations')
    ], 'migrations');
}

```

Developer runs artisan command, and it will copy files into your core

- `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag=config`
- `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag=migrations`

or

- `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`

### Q: why i have to copy files from package into core.. can't i rely on package service provider then i load configuration from it directly and views for it directly?

Yes, you can rely on the package’s own config and views directly — you don’t have to publish them. Publishing is only needed in specific situations. Let me explain clearly:

publishes() exists so developers can customize files shipped with the package.

by default laravel can use config + views + migrations without having to publish

Example: Config

```php
# config('package.some_option');
$this->mergeConfigFrom(__DIR__.'/../config/package.php', 'package');

# now you can use views @include('package::index')
$this->loadViewsFrom(__DIR__.'/../resources/views', 'package');

$this->loadMigrationsFrom(__DIR__.'/../database/migrations');

$this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'blog');
```

if files are published, Laravel automatically prioritizes the published files over the package defaults.



### Event Listeners In Service Provider

in EventServiceProvider you can declare the event listerens map

```php

<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

```


### Session in service providers

- Service providers boot very early in the request lifecycle. before session starts
    - so if you call session()->getId() in service provider, Laravel doesn’t have a session loaded → it will generate a fresh one.

- Session is normally started by middleware
    - Specifically, \Illuminate\Session\Middleware\StartSession.
    - That middleware runs after all service providers have already booted.
    - By the time your controller or view runs, the session is active and stable.

- when you ask for session()->getId() early .. laravel do strange thing
    - it create in memory a session store and give it an id and let you use session
    - at this step, nothing is persistent no cookie, no files nothing.. just in memory
    - when middleware run, it would take the accurate id from cookie and replace the temp id