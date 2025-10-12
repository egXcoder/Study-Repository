# Query Builder


## Lazy Loading vs Eager Loading

1- Lazy Loading (default) .. potential N+1 problem if not careful

```php

$users = User::all(); // Only 1 query: SELECT * FROM users

foreach ($users as $user) {
    echo $user->posts->count(); 
    // For each user → runs: SELECT * FROM posts WHERE user_id = ?
}

```


2- Eager Loading

```php

$users = User::with('posts')->get(); 
// 2 queries only:
// 1. SELECT * FROM users
// 2. SELECT * FROM posts WHERE user_id IN (....)

```

3- Lazy Eager Loading (on demand)

```php

$users = User::all(); // Only users are loaded

$users->load('posts'); 
// Runs 1 query to load posts for all users in the collection

```