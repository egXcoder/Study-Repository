# Guards Config

you need to define the supported ways to authenticate in config('auth.guards')

```php

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'token',
        'provider' => 'users',
    ],
    'sanctum' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
    'jwt_api' => [
        'driver' => 'jwt',
        'provider' => '',
    ],
    'admin_api' => [
        'driver' => 'sanctum',
        'provider' => 'admins',
    ],
],

```

on top example, we defined multiple ways to authenticate on our application
- web .. is using laravel session of the box
- api .. is using laravel token out of the box (users.api_token column)
- sanctum.. is using sanctum guard which try web guard if not possible it will try bearer token against personal_tokens table
- jwt_api.. is using our custom jwt guard we have built and provider is custom as well so not declared in config
- admin_api.. is using sanctum guard and rely on admins provider to find the user, if the user is not admin then it will not pass


## Usage

you can protect routes using middleware auth typically `auth:jwt_api`

Tip: `auth:api` means auth middleware and pass api as argument which is the guard who is guarding this route and you have to pass it by authenticating

```php
Route::middleware('auth:api')->group(function(){
    //routes are here protected by api guard
});

Route::middleware('auth:admin_api')->group(function(){
    //routes are here protected by admin_api guard
});

$user = Auth::guard('jwt_api')->user(); // to reach user by guard

if (Auth::guard('jwt_api')->check()) { //to check if user
    echo "Hello, " . $user->name;
}

```

## Default

default guard is web and you can define it in config/auth.php

Tip: default means if you just do `Auth::check()` .. this will rely on `web guard` by default..

Tip: default means if you do `Route::middleware('auth')->group(function(){//})` its `web guard` as well

