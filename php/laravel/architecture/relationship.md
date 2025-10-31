## one to one + one to many

below scheme represents one to one and one to many in same time.. it depends on the application relation ship not database scheme

```php

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});

Schema::create('profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('bio')->nullable();
    $table->string('phone')->nullable();
    $table->date('birthday')->nullable();
    $table->timestamps();
});

```

## one to one

one user .. one profile

```php

// app/Models/User.php .. is the parent table which dont contain foreigns then is uses has relationship

public function profile()
{
    return $this->hasOne(Profile::class);
}

// app/Models/Profile.php .. is the one with the foreign key then it uses belongsTo

public function user()
{
    return $this->belongsTo(User::class);
}

```


## one to many

one user .. many profiles

```php

// app/Models/User.php

public function profile()
{
    return $this->hasMany(Profile::class);
}

// app/Models/Profile.php
public function user()
{
    return $this->belongsTo(User::class);
}

```

## belongsToMany Relationship 

it represents Many to Many .. one cart can have many courses and one course can belongs to many carts

convention is:
- pivot table to be singular + singular
- pivot table to be strtolower(alphabetical_model1) . '_' . strtolower(alphabetical_model2)
- since word cart is before word course then its cart_course
- foreign ids are course_id , cart_id


```php

Schema::create('cart_course', function (Blueprint $table) { 
    $table->id();
    $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
    $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
    $table->timestamps(); 
});

class Cart extends Model
{
    public function courses(){
        return $this->belongsToMany(
            Course::class,
            'cart_course',// $table is the pivot table
            'cart_id',    // $foreignPivotKey (points to this model — Cart)
            'course_id',  // $relatedPivotKey (points to the related model — Course)
            'id',         // $parentKey on Cart model
            'id'          // $relatedKey on Course model
        )->withTimestamps();
    }


    //by convention
    public function courses(){
        return $this->belongsToMany(Course::class);
    }
}

class Course extends Model{
    //by convention
    public function carts(){
        return $this->belongsToMany(Cart::class);
    }
}

//by default
//if you dont pass pivot table .. it will assume cart_course as by convention

```


### Attach One Eloquent To the other using relationship

- One To One
```php
class User extends Model {
    public function profile() {
        return $this->hasOne(Profile::class);
    }
}

class Profile extends Model {
    public function user() {
        return $this->belongsTo(User::class);
    }
}

$user = User::find(1);
$user->profile()->create(['bio' => 'Hello']);
$user->profile()->update(['firstname' => 'Ahmed']); 


$profile->user()->associate($user)->save(); // you have to call save to persist association
$profile->user()->dissociate()->save();
```

- One To Many
```php

class Post extends Model {
    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
class Comment extends Model {
    public function post() {
        return $this->belongsTo(Post::class);
    }
}


$post = Post::find(1);
$post->comments()->create(['body' => 'Great post!']);
$post->comments()->createMany([
    ['body' => 'First comment'],
    ['body' => 'Second comment'],
]);


$comment->post()->associate($post)->save(); // you have to call save to persist association
$comment->post()->disssociate()->save();
```

- Many to Many

```php
class User extends Model {
    public function roles() {
        return $this->belongsToMany(Role::class);
    }
}

class Role extends Model {
    public function users() {
        return $this->belongsToMany(User::class);
    }
}


$cart = User::find(1);

//Attaching (always all) (if its already there then add it again)
$user->roles()->attach($role); // attach one
$user->roles()->attach($role->id); // attach one
$user->roles()->attach([1, 2, 3]); // attach multiple roles with ids 1,2,3
$user->roles()->attach(2, ['expires_at' => now()->addDays(30)]); // attach with extra pivot data

//detaching (always remove) (if exists multiple times then it will remove them all)
$user->roles()->detach($role);
$user->roles()->detach($role->id); 

//Syncing (always Keeps only the given records) 
//User currently has roles [1, 2, 3]
$user->roles()->sync([2, 4]); //this will make sure database has only 2,4 and remove otherwise

// syncWithoutDetach (syncing without removing)
//User currently has roles [1, 2, 3]
$user->roles()->syncWithoutDetach([2, 4]); //user now will have 1,2,3,4

```

Tip:
- Attaching: always add (if its already there then add it again)
- Detach: always remove (if exists multiple times then it will remove them all)
- Sync: always keep only the given record
- SyncWithoutDetach: sync without remove