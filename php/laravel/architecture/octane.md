# Octane

Laravel Octane is a package that makes Laravel run on top of high-performance application servers like Swoole or RoadRunner, instead of PHP-FPM.

Normally, PHP runs in a “shared nothing” model:

- Each HTTP request boots Laravel from scratch (autoload, service providers, configs, routes, etc.).
- After the request finishes, PHP throws everything away.
- Next request → reload everything again.

This wastes CPU/memory for heavy apps.

👉 Octane solves this by keeping the application in memory between requests.


## How Octane Works

- Octane Manager
-    ├── Worker #1 (Laravel loaded + DB connection reused)
-    ├── Worker #2 (Laravel loaded + DB connection reused)
-    ├── Worker #3
-    └── Worker #4

`php artisan octane:start --server=swoole --workers=8 --max-requests=500`

| Setting            | Meaning                                            |
| ------------------ | -------------------------------------------------- |
| `workers=8`        | Spawn 8 Laravel worker processes                   |
| `max-requests=500` | Restart worker every 500 requests (prevents leaks) |


## Performance
- Faster response time (50–200% faster).
- Less CPU usage.
- DB connections are reused (per worker).


## Drawbacks / Things to Watch
Memory leaks
- Since the app lives in memory, objects aren’t automatically destroyed each request.
- Code must be written carefully (e.g. no static state that “accumulates”).

Single-threaded workers
- If a request takes a long time (e.g., heavy DB query, API call, or sleep), the worker is busy, and any subsequent requests assigned to this worker will wait.
- If you start a transaction and forget to commit/rollback, the next request in the same worker can inherit that transaction state. This is why Laravel Octane provides the Octane::tick() and Octane::flush() helpers, and why it’s recommended to reset the DB state between requests.

Unsupported packages
- Some PHP libraries assume “shared nothing” model.
- Code that caches things globally may cause strange bugs.

Different deployment setup
- You run php artisan octane:start instead of relying on PHP-FPM.
- Still need a reverse proxy (Nginx) in front, usually.


Tip: How to mitigate slow requests
- Increase the number of workers to handle more concurrent requests.
- Offload slow tasks to queues or async jobs instead of processing them in the HTTP request.
- Use Swoole coroutines (if using Octane with Swoole) for certain async operations like HTTP requests or I/O — the worker can continue processing other coroutines while waiting.


## Usage

`composer require laravel/octane`

`php artisan octane:install`

`php artisan octane:start --server=swoole --host=127.0.0.1 --port=8000`


## Memory Leaks Examples

### Example 1 (Static Variables)
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

In Octane:
- The $cache array lives in memory across requests (per worker).
- Every request adds more items → memory grows without limit → leak.

### Example 2 (File Streams)

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

### Example 3 (Singleton Objects)

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

## How to Prevent Leaks in Octane
- Don’t rely on static properties for request-specific state.
- Release resources (file handles, DB connections, sockets) properly.
- Prefer Redis / DB cache over in-memory arrays for large data.
- Use php artisan octane:status and monitoring tools to watch memory.