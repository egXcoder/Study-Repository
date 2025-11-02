# Token Guard

rely on api_token column within users table to identify users

## Login

no login/logout method inside guard. you do it manually

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

## Logout

```php
$user = Auth::guard('api')->user();
$user->api_token = null; // or delete the token
$user->save();
```

After this, the token is invalid, and subsequent requests will fail authentication.


## Config

```php
'guards' => [
    'api' => [
        'driver' => 'token',
        'provider' => 'users',
    ],
],
```

