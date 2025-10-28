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

## Install

`composer require laravel/sanctum`

`php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"` config/sanctum.php and migrations

`php artisan migrate`

## SPA Authentication (Session + CSRF)

in this model, sanctum uses session to authenticate api requests

### Start Session
by default laravel treat api as stateless. no session and no cookie and no csrf check. if you go to `app/Http/kernel.php` and look at api middlewares, you wont find start session or encrypt cookie. if sanctum will use session based authentication, there are multiple middlewares needs to run like StartSession. so it grouped all of these middlewares in one middleware called `EnsureFrontendRequestsAreStateful::class`

what EnsureFrontendRequestsAreStateful middleware is doing is this:
- start multiple of middlewares encrypt cookies, start session, verify csrf token
- as long as request is coming from a domain allowed in .env `SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,spa.myapp.test`

### Client
- you should first make a request to the `/sanctum/csrf-cookie` to have XSRF-TOKEN cookie in client.
- `axios.defaults.withXSRFToken = true;` automatically send the X_XSRF-TOKEN on header using XSRF-TOKEN cookie
- `axios.defaults.withCredentials = true;` send cookies with the request .. If you forget this option, no cookies will be sent

### Guard
axios uses web guard. its defined in config/sanctum.php. when you login with guard it use the session to login

### login/logout

login and logout are typically like what happens with normal web guard and actually its web guard

```php
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $request->session()->regenerate(); // prevent session fixation

    return response()->json(['message' => 'Logged in successfully']);
});


Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    //flush session data completely then regenerate session id
    $request->session()->invalidate(); 
    
    //regenerate csrf token    
    $request->session()->regenerateToken();
    
    return response()->json(['message' => 'Logged out']);
});
```

Frontend Flow (Example with Axios)

```js

// Step 1: always make sure CSRF cookie exists
await axios.get('/sanctum/csrf-cookie');

// Step 2: login
await axios.post('/login', { email, password });

// Step 3: now user is authenticated
const user = await axios.get('/api/user');

// Step 4: logout
await axios.post('/logout');

// Step 5 (optional): prepare for re-login
await axios.get('/sanctum/csrf-cookie');


```

### client asking for csrf token

practically, /sanctum/csrf-cookie is visited
- on app startup (once per load)
- after login/logout/register as csrf token will change cause of session change
- to be safe, do it whenever respond is back with 419
```js

axios.interceptors.response.use(null, async error => {
    if (error.response?.status === 419) {
        await axios.get('/sanctum/csrf-cookie');
        return axios(error.config); // retry original request
    }
    throw error;
});

```


Q: if i want to scale up. is being stateful going to be bottleneck? as its very difficult to refactor everything to be back to stateless. so why dont i write it stateless from start yes it will be harder but its scalable?

being stateful is not bottleneck If you back your sessions with Redis or Memcached, Laravel can easily handle millions of concurrent users.

Why people still start with stateful?
- It’s secure by default — no XSS token leakage issues.
- It’s less boilerplate (no need to build token refresh flows).
- It’s easier to integrate with Laravel’s Auth and middleware.
- Laravel Sanctum handles SPA auth almost magically.

When stateless JWT makes sense from day one?
- Multi-client access (web + mobile + 3rd party APIs),
- Geo-distributed scaling (different regions),
- Non-browser clients,
- Zero session dependency,

then, yes — it’s better to go stateless (JWT) from the start.


## API Token Authentication (mobile apps, Postman, etc.)

Each user can generate one or more API tokens (stored in the personal_access_tokens table).

Each token can have a set of abilities (permissions) — e.g. ['read', 'update']


Tip: its one user can have multiple stored and trackable tokens instead of laravel out of box TokenGuard its one user one token functionality

```php

Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable'); //tokenable_type = usually App\Models\User
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
});


//config/auth.php
'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```


### HasApiTokens Trait

this trait gives functionalities for users to interact with personal tokens
- $user->createToken($name, $abilities = [])
- relationship tokens() that links to the personal_access_tokens table
- $user->currentAccessToken()
- $user->tokenCan('ability')
- $user->withAccessToken($token)

```php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    // ...
}

```


### login/logout

```php

Route::post('/login', function (Request $request) {
    $user = App\Models\User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    // Create a token with ability
    $token = $user->createToken('mobile-app', ['orders:read', 'orders:write']);

    return response()->json(['token' => $token]);
});

Route::post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    // or
    // $request->user()->tokens()->delete(); //delete all tokens

    return response()->json(['message' => 'Logged out']);
});

Route::get('/orders/{order}/read', function (Request $request) {
    if ($user->tokenCan('orders:read')) {
        // Allowed
    }
});

```


### Expiration

By default, sanctum tokens dont expire. but you can define this in config/sanctum.php

if you put expiration on tokens, then you may wish to have a schedule task to run daily to remove expired tokens from database

```php
Schedule::command('sanctum:prune-expired --hours=24')->daily();
```


### Guard

Santum Guard can authenticate via two mechnaism
- if could authenticate with session based then it will authenticate using webguard
- if not it will rely on Bearer Token to authenticate against personal_tokens table


