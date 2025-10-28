# Authentication

Are you a user?


## Guard

Guard is the class responsible to say if you are a user? laravel out of the box offer:
- Session Guard [Explained here](./guard//session.md)
- Token Guard (api_token column in users table)
- Custom Guard




- Guard extract session or token data from request
- Guard uses UserProvider with the extracted data to see if it matches one of database users

### Laravel Supported Drivers

1️⃣ Session guard (session) – default for web

- Stores authentication info in sessions and uses cookies.
- Used for traditional web login.
- Tracks logged-in user via $_SESSION.

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```

2️⃣ Token guard (token) – simple API tokens

- Checks static token stored in DB (api_token column).
- Stateless: client sends token in header (Authorization: Bearer ... or query string).
- Minimal; does not support JWT.

```php
'guards' => [
    'api' => [
        'driver' => 'token',
        'provider' => 'users',
    ],
],
```

3️⃣ Custom guards

- Laravel allows any driver via Auth::extend().
- Useful for: JWT, OAuth tokens, API keys, LDAP, SSO, etc.

Tip: Out-of-the-box, Laravel only ships session and token.

### Custom JWT Guard

```php

namespace App\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class JwtGuard implements Guard
{
    protected $request;
    protected $provider;
    protected $user;
    protected $tokenKey = 'Authorization';

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Get the currently authenticated user.
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();

        if (!$token) {
            return null;
        }

        $this->user = $this->provider->retrieveByToken($token);

        return $this->user;
    }

    public function check()
    {
        return (bool) $this->user();
    }

    public function guest()
    {
        return !$this->check();
    }

    public function id()
    {
        return $this->user() ? $this->user()->getAuthIdentifier() : null;
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the token from request headers
     */
    protected function getTokenFromRequest()
    {
        $header = $this->request->header($this->tokenKey);

        if (!$header) {
            return null;
        }

        // Expecting "Bearer <token>"
        if (strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }

        return null;
    }

    public function validate(array $credentials = [])
    {
        $token = $credentials['token'] ?? null;
        return $token && $this->provider->retrieveByToken($token) !== null;
    }
}


```

### Custom User Provider

```php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class JwtUserProvider implements UserProvider
{
    protected $key = 'your-secret-key'; // Use env('JWT_SECRET')

    public function retrieveById($identifier)
    {
        return User::find($identifier);
    }

    public function retrieveByToken($token)
    {
        try {
            $payload = JWT::decode($token, new Key($this->key, 'HS256'));
            return User::find($payload->sub);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Not used for JWT
    }

    public function retrieveByCredentials(array $credentials)
    {
        // JWT is token-based; credentials not needed
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // JWT is token-based; credentials not needed
        return false;
    }
}

```

### Register Guard in AuthServiceProvider

```php

Auth::extend('jwt', function ($app, $name, array $config) {
    $provider = new JwtUserProvider();
    return new JwtGuard($provider, $app['request']);
});

```

### Configure in config/auth.php

```php

'guards' => [
    'api_jwt' => [
        'driver' => 'jwt',
        'provider' => '', //can be empty since we have replaced it
    ],
]

```

### Use Guard

```php

Route::middleware('auth:api_jwt');

$user = Auth::guard('api_jwt')->user();

if (Auth::guard('api_jwt')->check()) {
    echo "Hello, " . $user->name;
}

```


Tip: Custom Guard i have declared is doing the bare minimum if authentication. guards though in production level is expected to be more powerful. has more methods and logic. for example
- Automatically refresh tokens or rotate them.
- More robust validate() logic, sometimes supporting multiple credential types.
- Ability to logout: Clear session, revoke token, etc.
- Authorization helpers: Sometimes guards expose convenience methods like hasRole(), can(), or abilities().
- Fire events when user logs in, logs out, fails authentication.
- Cache user object to avoid hitting the database repeatedly.

so its better to rely on laravel internal guard or on a package that has written powerful guard that you can use instead of writting it yourself


### How User Login/out?

#### Session Guard

there is a login/logout methods declared within guard that helps you login/logout

- Login

    ```php
    $user = User::where('email', $request->email)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        Auth::login($user); // <- session guard stores user ID in session
    }
    ```

    - Behind the scenes:
    - $this->user = $user; inside the guard
    - $request->session()->put('login_key', $user->id);

- Logout

    ```php
    Auth::logout();
    ```

    Behind the scenes:
    - Guard removes the session key.
    - $this->user is cleared.
    - Next request, $guard->user() will return null.

#### Token Guard

no login/logout methods. you have to do it manually

- Login

    ```php
    $user = User::where('email', $request->email)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        $token = Str::random(60);
        $user->api_token = hash('sha256', $token);
        $user->save();

        return ['token' => $token];
    }
    ```

    Client stores the token and sends it in Authorization header for future requests

- Logout

    ```php
    $user = Auth::guard('api')->user();
    $user->api_token = null; // or delete the token
    $user->save();
    ```

    After this, the token is invalid, and subsequent requests will fail authentication.


#### Custom Guard

up to you to declare login/logout methods inside guard or do it manually in controller

- Login

    ```php
    $user = User::where('email', $email)->first();

    if (!$user || !Hash::check($password, $user->password)) {
        return null;
    }

    if (TokenBlacklist::has($token)) {
        return null; // token is revoked
    }

    $payload = [
        'sub' => $user->id,           // subject: user ID
        'iat' => time(),              // issued at
        'exp' => time() + 3600,       // expiry: 1 hour
    ];

    $jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

    return ['token' => $jwt];
    ```

    Client stores the token (in localStorage, cookie, or mobile app) and sends it on every request:

- Logout
    
    JWT is stateless, so you cannot “destroy” it server-side. Common approaches:
    - Client-side logout: just delete the token in the browser/app.
    - Server-side token blacklist: store revoked tokens in DB/cache and check against them

    ```php
    $token = $this->getTokenFromRequest();
    
    if (!$token) {
        return;
    }
    
    try {
        $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

        // Calculate remaining TTL for the token
        $ttl = $payload->exp - time();
        if ($ttl > 0) {
            // Store token in cache as "blacklisted" with TTL
            Cache::put("jwt_blacklist:$token", true, $ttl);
        }

    } catch (\Exception $e) {
        // Invalid token, nothing to do
        return;
    }
    ```

    After this, the token is invalid, and subsequent requests will fail authentication.



## Sanctum and Passport

Laravel itself doesn’t ship many guard implementations beyond session and token, but it fully supports custom guards, either via your own implementation or via official packages


Sanctum [Explained here](./sanctum.md)
- Lightweight API authentication.
- Personal access tokens, SPA authentication, mobile apps.
- Uses a custom sanctum guard.
- Stateless or session-based.

Passport [Explained here](./passport.md)
- Full OAuth2 server implementation.
- For more complex scenarios: token scopes, clients, refresh tokens.
- Provides a passport guard.