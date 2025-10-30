# Passport

Laravel Passport is Laravel’s OAuth2 authentication server implementation.


✅ Use Passport if:
- You’re building a public API for external clients.
- You need OAuth2 flows (e.g., Google-style “Authorize this app to access your data”).
- You want to use access tokens with scopes, expirations, and refresh tokens.


❌ Use Sanctum instead if:
- It’s a first-party app (your own frontend + your backend).
- You only need simple API tokens or session-based login.


Passport uses OAuth2 tokens under the hood, managed by the league/oauth2-server package.

It creates and manages several database tables for tokens:


- oauth_clients: Registered apps that can request tokens
- oauth_access_tokens: Stores issued access tokens
- oauth_refresh_tokens: Stores refresh tokens
- oauth_auth_codes: Used for authorization code flow
- oauth_personal_access_clients: Stores personal access token clients


## Install

- `composer require laravel/passport`
- `php artisan vendor:publish --tag=passport-migrations`
- `php artisan vendor:publish --tag=passport-config`
- `php artisan migrate`
- `php artisan passport:install`
    - Creates encryption keys for token signing (stored in storage/oauth-private.key and storage/oauth-public.key)
    - Inserts two clients into oauth_clients:
        - Password grant client
        - Personal access client

- Add HasApiTokens to your User model
```php

use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}

```



## Core Concepts

- Client: An application that wants to access your API. Stored in oauth_clients.
- Access Token: A short-lived credential used to access protected routes. Stored in oauth_access_tokens.
- Refresh Token: Used to get a new access token when it expires. Stored in oauth_refresh_tokens
- Scope: Defines what the token is allowed to do (permissions).
- Grant Type: Defines how the client obtains the token (different auth flows).




## Authentication Methods (Grant Types)

### Personal Access Tokens (PATs)

they are meant to be long lived tokens with scopes. typically you issue them manually from a screen. once issued now you can use it to do api requests to the resources till token expires or you remove token from the webpage

- The user visits a web page to issue and manage his Personal Tokens
- They click “Generate new token” and define the required scopes

You run:

$token = $user->createToken('My Personal Token', ['read', 'write'])->accessToken;

- The token appears once, and the user stores it somewhere. because it wont be shown again
- That token stays valid until revoked or expired.


#### Configure guard in config/auth.php
```php
'guards' => [
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
```

### Protecting Routes

```php

Route::middleware('auth:api')->get('/orders', function (Request $request) {
    return [
        'user' => $request->user(),
        'orders' => Order::where('user_id', $request->user()->id)->get(),
    ];
});

```

#### Adding Scopes (Optional)

App\Providers\AuthServiceProvider:
```php
Passport::tokensCan([
    'user:read' => 'Retrieve the user info',
    'orders:create' => 'Place orders',
    'orders:read:status' => 'Check order status',
]);

Passport::defaultScopes([
    'user:read',
    'orders:create',
]);
```

Create Token with scope
```php
$token = $user->createToken('Personal Token', ['user:read'])->accessToken;
```

Protect Route with scope
```php

Route::middleware(['auth:api', CheckToken::using('orders:read', 'orders:create')])->get('/orders', function () {
    // user must have all scopes to access this
});

Route::middleware(['auth:api', CheckTokenForAnyScope::using('orders:read', 'orders:create')])->get('/orders', function () {
    // user must have any scope to access this
});

Route::get('/orders', function (Request $request) {
    if ($request->user()->tokenCan('orders:create')) {
        // you can check inside
    }
});

Passport::scopes(); //get all defined scopes list

```

#### Revoke Token

To revoke (invalidate) a personal access token:

```php

$tokenId = $request->user()->token()->id;
$request->user()->tokens()->where('id', $tokenId)->update(['revoked' => true]);

//or

$request->user()->tokens()->delete();

```

#### Expiration

By default, personal access tokens expire after 1 year. You can customize this in your AuthServiceProvider:

```php

use Laravel\Passport\Passport;

Passport::personalAccessTokensExpireIn(now()->addMonths(6));

```


#### Database Rows

| id | user_id | client_id | name | scopes | revoked | created_at | updated_at | expires_at |
|----|----------|------------|------|---------|----------|-------------|-------------|-------------|
| b2f9e3c3d83e4b729b1f22e0e6e235f0a672c16c8b8e5f50f9b6d5b7c4d7ea | 1 | 1 | Personal Access Token | [] | 0 | 2025-10-28 11:00:00 | 2025-10-28 11:00:00 | 2026-10-28 11:00:00 |
| degagase235f0a672c16c8b8e5f50f9b6d5b7c4d7e9a1b8d93f0a3e4d5c6a | 1 | 1 | Personal Access Token | [] | 0 | 2025-12-28 11:00:00 | 2025-12-28 11:00:00 | 2026-12-28 11:00:00 |
| a1b2c3d4e5f60718293a4b5c6d7e8f901234567890abcdef1234567890abcdef | 2 | 1 | Mobile App Token | ["view-orders", "create-orders"] | 0 | 2025-09-10 09:20:00 | 2025-09-10 09:20:00 | 2026-09-10 09:20:00 |
| c5e2d9b3a4f8e7c1b2a3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5 | 3 | 1 | POS Terminal Token | ["sync-products"] | 1 | 2025-08-01 12:30:00 | 2025-08-15 13:00:00 | 2026-08-01 12:30:00 |



Tip: everytime createToken is called it will add a record in oauth_access_tokens even if its same name it will add two records




### Password Grant

Used when your own frontend (e.g. Vue, React, mobile app) wants to authenticate a user directly using email/password. Your own SPA or mobile app (first-party clients).


Example:

```bash

POST /oauth/token
Content-Type: application/json

{
  "grant_type": "password",
  "client_id": "3",
  "client_secret": "your-client-secret",
  "username": "john@example.com",
  "password": "secret",
  "scope": ""
}


```








## Token Types

### Personal Access Token