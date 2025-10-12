# Octane

Laravel Octane is a package that makes Laravel run on top of high-performance application servers like Swoole or RoadRunner, instead of PHP-FPM.

Normally, PHP runs in a “shared nothing” model:

- Each HTTP request boots Laravel from scratch (autoload, service providers, configs, routes, etc.).
- After the request finishes, PHP throws everything away.
- Next request → reload everything again.

This wastes CPU/memory for heavy apps.

👉 Octane solves this by keeping the application in memory between requests.


## How Octane Works

- Laravel is booted once at worker start.
- Subsequent HTTP requests reuse the same Laravel instance in memory.
- This removes a lot of bootstrap overhead (autoloading, config loading, container bootstrapping).
- Requests are still isolated, but bootstrapping is skipped.


## Performance

- Much faster response times (especially for APIs and apps with many requests per second).
- Eliminates the constant “reboot Laravel” overhead.

Extra features

- Background tasks, task workers, WebSockets (with Swoole).
- Serving static files directly.
- Tick tasks (periodic scheduled code).


## Drawbacks / Things to Watch
Memory leaks
- Since the app lives in memory, objects aren’t automatically destroyed each request.
- Code must be written carefully (e.g. no static state that “accumulates”).

Unsupported packages
- Some PHP libraries assume “shared nothing” model.
- Code that caches things globally may cause strange bugs.

Different deployment setup
- You run php artisan octane:start instead of relying on Apache/Nginx + PHP-FPM.
- Still need a reverse proxy (Nginx) in front, usually.



## Usage

`composer require laravel/octane`

`php artisan octane:install`

`php artisan octane:start --server=swoole --host=127.0.0.1 --port=8000`


👉 So, Octane isn’t required for every Laravel project — but if you’re building high-performance APIs, real-time apps, or handling thousands of requests/sec, Octane gives you a serious boost.


## Memory Leaks Examples

### Example 1
```php

class SomeService
{
    public static $cache = [];

    public function handle($data)
    {
        self::$cache[] = $data;
    }
}

```

In PHP-FPM:
- Every request starts with a fresh process.
- The $cache array resets on each request.

In Octane:
- The array lives in memory across requests.
- Every request adds more items → memory grows without limit → leak.

### Example 2

```php

Route::get('/leak', function () {
    $file = fopen('/tmp/test.log', 'w');
    fwrite($file, "Request at " . now() . "\n");
    // Forgot to fclose($file);
    return 'done';
});

```

- Without Octane → process ends after request → OS closes file handle.
- With Octane → file handles remain open → can exhaust system resources.

### Example 3

```php

class Counter
{
    public $count = 0;
}

app()->singleton(Counter::class, function () {
    return new Counter;
});

Route::get('/count', function (Counter $counter) {
    $counter->count++;
    return $counter->count;
});

```

With normal Laravel (PHP-FPM):
Each request creates a new app → $count resets to 0 every request.

With Octane:
Singleton stays in memory → $count keeps incrementing → unexpected behavior and possible memory bloat.


### Example 4

```php
// config/cache.php  .. 'driver' => 'array',
Route::get('/users', function () {
    $users = User::all(); // loads thousands of users
    Cache::put('users', $users); // using array cache driver (in-memory)
    return $users->count();
});

```

In PHP-FPM: memory freed after request.

In Octane: cached users collection stays in memory permanently. If it grows or is refreshed often, memory usage balloons.



## How to Prevent Leaks in Octane
- Don’t rely on static properties for request-specific state.
- Release resources (file handles, DB connections, sockets) properly.
- Prefer Redis / DB cache over in-memory arrays for large data.
- Use php artisan octane:status and monitoring tools to watch memory.