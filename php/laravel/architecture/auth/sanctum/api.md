# API Token Authentication (mobile apps, Postman, etc.)

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


