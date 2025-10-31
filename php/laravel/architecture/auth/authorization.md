# Authorization 

Laravel gives you two powerful tools to control access:

- Gates — simple, closure-based checks
- Policies — organized, model-specific permission classes

## Gate
A Gate is like a function that answers “can this user perform this action?” You usually define them in `app/Providers/AuthServiceProvider.php`

```php

use Illuminate\Support\Facades\Gate;
use App\Models\User;

public function boot()
{
    Gate::define('view-admin-dashboard', function (User $user) {
        return $user->role === 'admin';
    });
}

```

```php

if (Gate::allows('view-admin-dashboard')) {
    // user is admin
} else {
    abort(403);
}

// Or directly in a controller method:
$this->authorize('view-admin-dashboard');

// or in blade
@can('view-admin-dashboard')
    <a href="/admin">Admin Dashboard</a>
@endcan

@cannot('view-admin-dashboard')
    <a href="/admin">Admin Dashboard</a>
@endcannot


//middleware
Route::put('/post/{post}', function (Post $post) {
    // The current user may update the post...
})->middleware('can:update,post');

Route::put('/post/{post}', function (Post $post) {
    // The current user may update the post...
})->middleware('can:create,App\Models\Post');

```

## Policy

Per-Model Authorization

`php artisan make:policy PostPolicy --model=Post`

```php

namespace App\Policies;

class PostPolicy
{
    public function view(User $user, Post $post)
    {
        // Only owner or admin can view
        return $user->id === $post->user_id || $user->isAdmin();
    }

    public function update(User $user, Post $post)
    {
        // Only the owner can edit their post
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }
}

```

Then register it in AuthServiceProvider.php:

```php

protected $policies = [
    \App\Models\Post::class => \App\Policies\PostPolicy::class,
];

```

```php

Gate::authorize('create', Post::class);

Gate::authorize('update', $post);


// Or directly in a controller method:
$this->authorize('update', $post);


// or in blade
@if (Auth::user()->can('update', $post))
    <!-- The current user can update the post... -->
@endif

@canany(['update', 'view', 'delete'], $post)
    <!-- The current user can update, view, or delete the post... -->
@elsecanany(['create'], \App\Models\Post::class)
    <!-- The current user can create a post... -->
@endcanany

//middleware
Route::put('/post/{post}', function (Post $post) {
    // The current user may update the post...
})->middleware('can:update,post');

Route::put('/post/{post}', function (Post $post) {
    // The current user may update the post...
})->middleware('can:create,App\Models\Post');

```