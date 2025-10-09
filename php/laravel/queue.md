# Queue

## When To Use Queue
- When you have a task that needs to run in the background such as ProcessPayment, GenerateInvoice etc... to reduce stress on server and requests would be quick and delegate the task to background

## Set Queue Connection
- QUEUE_CONNECTION=database
- QUEUE_CONNECTION=redis
- config/queue.php


## Migrate Queue Structure in database
- `php artisan queue:table`
- `php artisan queue:failed-table`
- `php artisan migrate`



## Create Job

`php artisan make:job SendWelcomeEmail`


```php

// app/Jobs/SendWelcomeEmail.php
class SendWelcomeEmail implements ShouldQueue //ShouldQueue Interface “Don’t run this job immediately — push it to the queue.”
{
    //This trait adds dispatch static method, SendWelcomeEmail::dispatch() instead of dispatch(new SendWelcomeEmail($user));
    use Dispatchable, 

    // This trait adds helper methods for when your job is being processed by the queue worker
    // $this->release(30); // Requeue the job to try again after 30 seconds 
    // $this->delete();    // Manually delete the job
    // $this->fail($exception); // Mark it as failed
    // Useful when You want to retry later if an API call failed
    // Useful when you want to fail it maybe at specific point
    InteractsWithQueue, 

    // This trait gives your job access to all queue configuration options
    // public $tries = 5;
    // public $timeout = 120;
    //also
    //SendWelcomeEmail::dispatch($user)
    // ->onQueue('emails')     // use custom queue
    // ->delay(now()->addMinutes(5))  // run later
    // ->onConnection('redis'); // use Redis instead of default
    // So it gives jobs flexibility in where and how they’re queued.
    Queueable, 


    // this trait tells Laravel: “Store only the model’s ID when serializing, and automatically re-fetch it from the database when the job runs.”
    // if you dont use the trait laravel would try to serialize all eloquent model properties and relationships .. takes too much data
    SerializesModels;

    public function __construct(public User $user) {}

    public function handle()
    {
        Mail::to($this->user)->send(new WelcomeMail($this->user));
    }
}

```


## What can be put into queue?

- Jobs .. the most common thing you push to a queue.
- Listeners (that ShouldQueue) .. A listener normally runs when an event is fired, but if it implements ShouldQueue, then it’s queued instead of running immediately.
- Mailables.. call ->queue() instead of ->send() `Mail::to($user)->queue(new WelcomeMail($user));`
- Notifications (that ShouldQueue)



## Configure Job Processing

- By default job will be tried only once and if failed it will be moved to failed jobs with no delay

- you can configure job for no of trial and delay before first procesing and time between trials

```php

class ProcessOrder implements ShouldQueue
{
    public $tries = 5; //max attempts .. after 5 tries it will be sent to failed jobs
    public $backoff = 10; //wait 10 seconds between trials
    public $backoff = [10, 30, 60]; //wait 10 then 30 then 60
    public $delay = 60; //wait 60 seconds before first trial
}

```

- you can override whatever job says by `php artisan queue:work --tries=5 --backoff=10`

- you can also declare only delay when you are dispatching `SendEmailJob::dispatch($user)->delay(now()->addSeconds(30));`


### Run queue

`php artisan queue:work` .. run queue

Typially if job throw exception that wouldnt exit queue, but queue worker can crash though for multiple reasons .. to be safe its recommended to use supervisor to keep worker running always


### Process

`php artisan queue:work`

- Laravel continuously polls the jobs table (or Redis list) for pending jobs. When it finds one, it reserves it then no other workers can use it `UPDATE jobs SET reserved_at = NOW(), attempts = attempts + 1 WHERE id = 1`
- if job succeeded `DELETE FROM jobs WHERE id = 1`
- If job fails with exception .. Has it exceeded max attempts .. if yesn it delete it and insert it into failed jobs
- if job fails with exception.. it didnt exceed max attempts .. then release `update job reserved_at = null and available_at = now + backoff`

Tip: if you dont want job to be moved to failed_jobs at all, you can on the job to try and catch and instead of throwing exception, you will catch the exception but instead of returning normally which means success job.. you will instead call $this->release($backoff); which will trigger `update job reserved_at = null and available_at = now + backoff`


### Failed Jobs

Laravel does not touch Failed Jobs again unless you manually retry them. by moving them from failed jobs back to normal table

- `php artisan queue:retry {id}`
- `php artisan queue:retry all`


If you want failed jobs to be automatically retried

- Option 1 — Use release() inside the job then job doesnt move to failed_jobs ever
```php

public function handle()
{
    try {
        $this->processSomething();
    } catch (\Throwable $e) {
        // Retry again after 30 seconds
        $this->release(30);
    }
}

```

- Option 2 — Schedule a command to retry failed jobs

```php

// in your app/Console/Kernel.php scheduler:

protected function schedule(Schedule $schedule)
{
    $schedule->command('queue:retry all')->everyFiveMinutes();
}

```

## pass eloquent into listener

listener is the object which gets serialized but it contains reference to event object.. and event itself which can contain the eloquent data .. we have used SerializeModels then event object to serialize only the id of the model and not all the data to reduce space usage

```php

OrderCreated::dispatch(Order::find(1));


// app/Events/OrderCreated.php
namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
    }
}


namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event)
    {
        // You can safely access the model here
        $order = $event->order;

        \Log::info('Sending order email for order #' . $order->id);

        // Example: send mail
        // Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
    }
}

```


## can i use redis for queue?

- `QUEUE_CONNECTION=redis`

- config/queue.php

```php

'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
]

```
This means Laravel will use the default Redis connection (from config/database.php).

- config/database.php

- you use the typical laravel jobs, event listeners etc... nothing changed

- behind the scene, laravel add a key in redis called laravel_database_queues:default which is type list and every entry will be json encoded data

```json
{
    "uuid": "1531c3b3-87b8-4921-ac19-9fdc41aa3e93",
    "displayName": "App\\\\Listeners\\\\SendWelcomeEmail",
    "job": "Illuminate\\\\Queue\\\\CallQueuedHandler@call",
    "maxTries": 5,
    "maxExceptions": null,
    "failOnTimeout": false,
    "backoff": null,
    "timeout": null,
    "retryUntil": null,
    "data": {
        // job serialized        
    },
    "id": "mZQwJRmakeHHWnuxzPrbJBcWcv7anZl2",
    "attempts": 0
}

```

- when processing it moves the processor into laravel_database_queues:default:reserved and its type of sorted set

- when job fails, it doesnt move to failed jobs in redis.. instead its sent to database .. its separately configured in config/queue.php on failed section and this because Laravel’s failed job mechanism is meant for persistent storage and easy inspection.

- laravel doesnt provide failed jobs in redis out of the box. only databases



### Laravel Horizon

horizon has two jobs
- Supervisor: Keeps track of your defined worker configurations (config/horizon.php). Starts and restarts workers automatically.
- Workers : Horizon spawns real queue:work processes behind the scenes — same code, just managed automatically.
- UI to show you what is the progress of jobs

`composer require laravel/horizon:"^5.0"`
`php artisan horizon:install`
`php artisan horizon`
`https://localhost/horizon`




Q: if job is reserved and the worker crashed before releasing it, how long worker will take before retrying it?

retry_after is defined in your config/queue.php under redis/database

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90, // seconds
        'block_for' => null,
    ],
],
```

