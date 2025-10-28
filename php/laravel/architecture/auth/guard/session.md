# Session guard – default for web

it rely on the mechanism of sessions.. 


when user start to use the web application.. he will be offered laravel_session cookie



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