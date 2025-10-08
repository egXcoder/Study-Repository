# Event


An Event in Laravel is a way for your app to say: “Hey, something just happened! Whoever cares, please react.”

## Create Event

`php artisan make:event UserRegistered` .. generate event in app/Events/UserRegistered.php

```php
<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class UserRegistered
{
    use Dispatchable; // gives you static dispatch() method, thats all

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}

```

## Create Listener

`php artisan make:listener SendWelcomeEmail --event=UserRegistered` .. generate app/Listeners/SendWelcomeEmail.php

```php

namespace App\Listeners;

use App\Events\UserRegistered;
use Mail;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        $user = $event->user;

        // logic to send an email
        Mail::to($user->email)->send(new \App\Mail\WelcomeMail($user));
    }
}

```

### Define Mapping

there are two ways of how laravel knows this event have these listeners

- Auto Discovery..
    - by default laravel will auto try to discover mapping between event and listeners
    - it will scan path /app/Listeners
    - will see listener handle method if it type hinted Event, then it will add it to map
    - you can switch off auto discover by overriding EventServiceProvider
    - all of this auto discovery logic is happening on EventServiceProvider

- Manual..
    - you can put the mapping of Event has many Listners in EventServiceProvider

    ```php
    class EventServiceProvider extends ServiceProvider
    {
        /**
        * The event listener mappings for the application.
        *
        * @var array<class-string, array<int, class-string>>
        */
        protected $listen = [
            Registered::class => [
                SendEmailVerificationNotification::class,
            ],
        ];
    }
    ```

- Hybrid..
    - the default logic is merging not overriding, so if manually said there is one listener and auto discovery says there is 4 listeners .. then laravel will merge both to get all potential listeners


- Tip: you can amend the logic of hybrid to be overriding.. so manually put listeners override auto discovery.. you can do that one way or another in EventServiceProvider

- Tip: `php artisan event:list` .. to see the mapping of event with listeners


## Fire the event

```php
UserRegistered::dispatch($user);

#or

event(new UserRegistered($user));
```


## Queue Listeners

If your listener takes time (e.g. sending emails), you can make it run in background easily:
That's literally it

```php

use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    public function handle(UserRegistered $event)
    {
        // heavy logic...
    }
}

```


## Subscriber

In Laravel:
- A Listener = reacts to one specific event.
- A Subscriber = can listen to multiple events in one class.


### Add Subscriber

```php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Events\UserLoggedIn;
use App\Events\UserDeleted;

class UserEventSubscriber
{
    public function handleUserRegistered($event)
    {
        // send welcome email
        Mail::to($event->user->email)->send(new \App\Mail\WelcomeMail());
    }

    public function handleUserLoggedIn($event)
    {
        // update last login timestamp
        $event->user->update(['last_login_at' => now()]);
    }

    public function handleUserDeleted($event)
    {
        // log deletion or clean up
        \Log::info("User deleted: {$event->user->email}");
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events)
    {
        $events->listen(UserRegistered::class,[self::class, 'handleUserRegistered']);
        $events->listen(UserLoggedIn::class, [self::class, 'handleUserLoggedIn']);
        $events->listen(UserDeleted::class, [self::class, 'handleUserDeleted']);

        // or

        // return [
        //     UserRegistered::class => 'handleUserLogin',
        //     UserLoggedIn::class => 'handleUserLogout',
        //     UserDeleted::class => 'handleUserLogout',
        // ];
    }
}


```

### Mapping Subscriber

we use EventServiceProvider

Laravel does not automatically discover subscribers.
👉 You must register them manually in your EventServiceProvider.

```php

<?php

namespace App\Providers;

use App\Listeners\UserEventSubscriber;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        //
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        UserEventSubscriber::class,
    ];
}

```


### when to use it
you can use subscriber when
- you can group listeners to one domain group this will be cohesive.
- when listeners are doing small logic then you can group them instead of many separate classes

dont use subscriber if
- listeners are for different domains like handleUserRegistered, handleOrderPaid, handleInventoryOut


### Q: if one of the methods become messy, what should we do?

- first approach: extract the logic into a service and return subscriber back to be small

- second approach: extract only this method to listener, but this is little risky because if you will randomly have listeners and subscribers you will get confusion if this logic live in listener or subscriber

- third approach: Convert the subscriber entirely into individual listeners
