# Session

A session is a storage place in server side. each client have unique id which points to a session in server side


## Drivers

- file: Stores sessions in storage/framework/sessions (default).
- database: Saves session data in a database table
- redis: Stores sessions in Redis. Great for scale.
- memcached: Similar to Redis.
- cookie: Stores the entire session in an encrypted cookie (not just an ID).
- array: Stores sessions in memory (only for testing)


## How Laravel identifies a session

When a new session starts, Laravel:
- Generates a random session ID
- Saves it in a cookie (default name: laravel_session)
- Stores the session data on the backend (file/DB/Redis)

When the user sends another request:
- The browser automatically includes that cookie.
- Laravel extracts the session ID.
- Loads the session data from storage.
So the cookie only holds a random identifier, not the user’s data.

example `laravel_session=eyJpdiI6Imt5b05wbG5jTnJhR2w9PSIsInZhbHVlIjoibGk1RmR1aWdpM0N...`

Tip: laravel_session is added into cookie as `http only` which means it cant be reached by js document.cookie

Tip: `"laravel_session"` key can be renamed to anything else in config/session.php

Tip: session lifetime is configured in config/session.php .. default is 120 minutes being idle

Tip: storage path is configured in config .. default is `storage_path('framework/sessions')`


## Laravel Session vs PHP SESSION

Laravel does not use PHP’s $_SESSION or session_start(). Instead, it has its own session manager.

Why $_SESSION doesn’t exist?

Because Laravel never calls PHP’s session_start(), the global $_SESSION superglobal is never created. Even though Laravel supports a “native” session driver that could wrap PHP sessions, by default (file driver, etc.) it doesn’t rely on $_SESSION at all.

When Laravel Clean Session?
- garbage collection randomly run at StartSession middleware
- `'lottery' => [2, 100],` in config/session.php. it means gc runs On 2 out of every 100 requests
- clean up Behavior is declared inside the driver such as FileSessionHandler.gc() also DatabaseSessionHandler.gc(),etc..

## Session Encryption

As Extra safety for session data you can encrypt it using your laravel application key before storing data into (file, database, etc..).. default is unencrypted. you can enable encryption in config/session.php. it will have little cpu overhead for encryption/decryption if you are concerned with safety


## Session Regeneration

after login/logout/register.. you probably want to `session()->regenerate()` and this will regenerate id in server and in cookie but keeps the session data the same. the only reason to do that is to prevent session fixation attack [Explained here](../attacks/session_fixation.md)

Tip: with every request sent, there is a middleware called EncryptCookie which take the cookie data and encrypt it using app_key and random values and put it in client cookie as encrypted. everytime encryption run it generate different text. from client perspective it feels like cookie values are changing but not really it points to same thing after laravel decrypt it