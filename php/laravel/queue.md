# Queue

## Set Queue Connection
QUEUE_CONNECTION=database
QUEUE_CONNECTION=redis

config/queue.php


## Migrate Queue Structure in database
php artisan queue:table
php artisan queue:failed-table

php artisan migrate



## Create Job

php artisan make:job TestJob


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

## can i use redis for queue?
