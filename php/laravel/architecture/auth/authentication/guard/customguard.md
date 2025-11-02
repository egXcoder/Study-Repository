# Custom Guard

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

## Register Guard in AuthServiceProvider

```php

Auth::extend('jwt', function ($app, $name, array $config) {
    $provider = new JwtUserProvider();
    return new JwtGuard($provider, $app['request']);
});

```

### Configure in config/auth.php

```php

'guards' => [
    'jwt' => [
        'driver' => 'jwt',
        'provider' => '', //can be empty since we have replaced it
    ],
]

```

## Usage

```php

Route::middleware('auth:api_jwt'); //in routes

$user = Auth::guard('api_jwt')->user(); // to reach user

if (Auth::guard('api_jwt')->check()) { //to check if user
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



Tip: for JWT there is a method for invalidation called jwt versioning. that whenever you issue a jwt token you give it a version and put in users.jwt_token_version. so when request comes you validate if token is at the correct up to date version.
pros: cheap and fast
cons: assume one user one jwt token all the time


Tip: another way for jwt is refresh tokens, so typically server maintain refresh tokens in database per user. if you want to invalidate specific device you just invalidate refresh token. tokens themselves are short living they will keep running till they need to validate again and thats when it wont be able to do that
pros: its better than blacklist that you dont have to lookup the token for every request
cons: to invalidate refresh token it doesnt do immediately and take some time though depend on how much far for jwt token to expire
