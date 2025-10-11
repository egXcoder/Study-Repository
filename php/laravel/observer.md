# Observer


An Observer is simply a class that listens to lifecycle events on a model.


| Event      | When it triggers                          |
|------------|-------------------------------------------|
| creating   | Before a model record is created          |
| created    | After a model record is created           |
| updating   | Before a model is updated                 |
| updated    | After a model is updated                  |
| saving     | Before creating or updating               |
| saved      | After creating or updating                |
| deleting   | Before a model is deleted                 |
| deleted    | After a model is deleted                  |
| restoring  | Before a soft-deleted model is restored   |
| restored   | After a soft-deleted model is restored    |


## Create Observer

`php artisan make:observer UserObserver --model=User`

```php

//app/Observers/UserObserver.php.

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user)
    {
        if (empty($user->uuid)) {
            $user->uuid = Str::uuid();
        }
    }

    public function created(User $user)
    {
        \Log::info("New user created: {$user->id}");
    }
}


```


## Register Observer

```php

use App\Models\User;
use App\Observers\UserObserver;

public function boot()
{
    User::observe(UserObserver::class);
}

```