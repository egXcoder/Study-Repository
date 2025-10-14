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