# Session guard – default for web

it rely on the mechanism of sessions.. sessions [Explained here](../../session.md)


## login

login method is declared inside session guard

```php
$user = User::where('email', $request->email)->first();

if ($user && Hash::check($request->password, $user->password)) {
    Auth::login($user); // session guard stores user ID in session
    session()->regenerate();
}
```

Tip: there is a method with session guard called attempt(['email'=>'test@gmail.com','password'=>123]) which will try to do the check and if pass it will login and return true


## logout

```php
Auth::logout(); //Guard removes the user identifier from session
session()->regnerate(); //If someone obtained your old session ID (e.g., via XSS or network sniffing), they can reuse it to re-authenticate as you after you log out. its prefered to regenerate the session id after amending user prvilieague 
```

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```