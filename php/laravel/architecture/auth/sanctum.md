# Sanctum

Laravel Sanctum is an official Laravel package for API authentication, designed to be simple and lightweight. It provides:


- SPA Authentication (Session + CSRF)
    - The frontend logs in via /login (using email/password).
    - Sanctum issues a session cookie.
    - The SPA sends requests with that cookie — Laravel automatically identifies the user.
    - CSRF protection is handled with /sanctum/csrf-cookie.


- API Token Authentication (mobile apps, Postman, etc.)
    - The user logs in (or you authenticate them via API).
    - you create a token `$token = $user->createToken('mobile-app')->plainTextToken;`
    - mobile app stores this token
    - Each request includes the token in the header `Authorization: Bearer {token}`

- API token authentication for mobile apps or third-party clients.
- SPA authentication (single-page applications) using Laravel’s session cookies.
- Optional token abilities / scopes to limit what a token can do.


## Install

`composer require laravel/sanctum`

`php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"` config/sanctum.php and migrations

`php artisan migrate`

migration is 

```php

Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable');
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
});

```

## SPA Authentication (Session + CSRF)

### Flow:
- The frontend logs in via /login (using email/password).
- Sanctum issues a session cookie.
- The SPA sends requests with that cookie — Laravel automatically identifies the user by auth:santum guard
- CSRF protection is handled with /sanctum/csrf-cookie.

### Middleware
by default laravel treat api as stateless. no session and no cookie and no csrf check. let me prove it to you if you go to app/Http/kernel.php and look at api middlewares, you wont find start session or encrypt cookie. if sanctum will use session based authentication, there are multiple middlewares needs to run like StartSession. so it grouped all of these middlewares in one middleware called EnsureFrontendRequestsAreStateful::class

what EnsureFrontendRequestsAreStateful middleware is doing is this:
- check if api request is coming from a domain allowed in .env `SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,spa.myapp.test`
- if matches, then it will start multiple of middlewares encrypt cookies, start session, verify csrf token

### Client
- you should first make a request to the `/sanctum/csrf-cookie` to have XSRF-TOKEN cookie in client.
- `axios.defaults.withXSRFToken = true;` automatically send the X_XSRF-TOKEN on header using XSRF-TOKEN cookie
- `axios.defaults.withCredentials = true;` send cookies with the request .. If you forget this option, no cookies will be sent

### Guard
axios uses web guard. its defined in config/sanctum.php. when you login with guard it use the session to login

### login/logout

TODO::