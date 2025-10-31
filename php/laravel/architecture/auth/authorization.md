# Authorization 

Laravel gives you two powerful tools to control access:

- Gates — simple, closure-based checks
- Policies — organized, model-specific permission classes
- Role-Based Access Control (RBAC) — Most Common Real-World Approach

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

## Role-Based Access Control (RBAC) — Most Common Real-World Approach

In most professional Laravel apps, teams add roles and permissions tables and control access based on that.

`composer require spatie/laravel-permission`
`php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
`php artisan migrate`

| Table                   | Description                                          |
| ----------------------- | ---------------------------------------------------- |
| `roles`                 | List of roles (admin, editor, user, etc.)            |
| `permissions`           | List of permissions (edit posts, delete posts, etc.) |
| `role_has_permissions`  | Links roles to permissions                           |
| `model_has_roles`       | Links users to roles                                 |
| `model_has_permissions` | Links users directly to permissions                  |


```php

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}

```


```php

if ($user->hasRole('admin')) {
}

if ($user->can('articles.edit')) {
}

//in controller
$this->authorize('articles.edit');

//in blade
@role('admin')
    <a href="/admin">Admin Panel</a>
@endrole

@can('articles.delete')
    <button>Delete</button>
@endcan


//in route
Route::middleware('role:admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

Route::middleware('permission:articles.view')->group(function(){
    Route::get('/articles', [ArticleController::class, 'index']);
}) 

```

### Define super admin in AuthServiceProvider.php

```php

public function boot()
{
    // Implicitly grant "Super Admin" role all permissions
    // This works in the app by using gate-related functions like auth()->user->can() and @can()
    Gate::before(function ($user, $ability) {
        return $user->hasRole('Super Admin') ? true : null;
    });
}

```

### Helper methods

| Method                                         | Description              |
| ---------------------------------------------- | ------------------------ |
| `$user->assignRole('writer')`                  | Give role                |
| `$user->removeRole('writer')`                  | Remove role              |
| `$user->hasRole('writer')`                     | Check if user has a role |
| `$user->givePermissionTo('articles.delete')`   | Add permission           |
| `$user->revokePermissionTo('articles.delete')` | Remove permission        |
| `$user->can('articles.edit')`                  | Check permission         |
| `$user->getRoleNames()`                        | Get all role names       |
| `$user->getAllPermissions()`                   | List permissions         |


### Seeding 

`php artisan make:seeder RolesAndPermissionsSeeder`

```php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2️⃣ Create permissions
        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3️⃣ Create roles and assign permissions
        Role::firstOrCreate(['name' => 'admin'])->givePermissionTo(Permission::all());

        Role::firstOrCreate(['name' => 'manager'])->givePermissionTo(['users.view', 'orders.view', 'orders.create', 'orders.edit']);

        Role::firstOrCreate(['name' => 'staff'])->givePermissionTo(['orders.view']);

        if ($user = \App\Models\User::first()) {
            $user->assignRole('admin');
        }
    }
}

```

### What permissions user get from permissions and roles

permissions are cumulative (merged together). Nothing gets overridden or cancelled — all granted permissions are added up.


Tip: By default, Spatie caches permissions. When you change any role/permission, call: `php artisan permission:cache-reset`. you can disable cache from spattie config but its not recommended in production environment because it would be hundreds of lookup per request