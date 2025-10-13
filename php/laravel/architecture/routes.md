# Routes


Routing is one of the core concepts in Laravel — it’s how you tell your app what to do when someone visits a URL.


## Returning a view:

```php
Route::get('/about', function () {
    return view('about');
});
```

This looks for resources/views/about.blade.php.

## Using Controller

```php

Route::get('/contact', [ContactController::class, 'index']);

// or

Route::get('/contact', 'ContactController@index');
```

## Parameters

```php

Route::get('/users/{id}', function ($id) {
    return "User ID: $id";
});


//optional parameter
Route::get('/users/{name?}', function ($name = 'Guest') {
    return "Hello, $name";
});

```

## Eloquent Auto Binding

```php

//If you visit /posts/10, Laravel automatically finds Post::find(10).
Route::get('/posts/{post}', function (App\Models\Post $post) {
    return $post->title;
});


//bind with another column
Route::get('/posts/{post:slug}', [PostController::class, 'show']);

```


## Naming

```php

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

//then in blade
// <a href="{{ route('dashboard') }}">Go to Dashboard</a>
```

## Groups

```php

Route::prefix('admin')->group(function () {
    Route::get('/users', 'AdminUserController@index');
    Route::get('/posts', 'AdminPostController@index');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/profile', 'ProfileController@index');
    Route::get('/settings', 'SettingsController@index');
});


Route::controller(PostController::class)->prefix('posts')->group(function () {
    Route::get('/', 'index');
    Route::get('/{post}', 'show');
});

```


## Resources

```php

Route::resource('posts', PostController::class);

// GET /posts → index
// GET /posts/create → create
// POST /posts → store
// GET /posts/{post} → show
// GET /posts/{post}/edit → edit
// PUT/PATCH /posts/{post} → update
// DELETE /posts/{post} → destroy

```