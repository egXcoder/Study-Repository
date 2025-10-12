# Identity and State


## Object Identity

Definition: Every object has its own unique identity, which distinguishes it from all other objects, even if they look identical.

Think of it like a passport number or memory address.

```php
$user1 = new User("Ahmed");
$user2 = new User("Ahmed");
```

Both have the same name "Ahmed".

But $user1 !== $user2 because they are two different objects (different identity in memory).

✅ Why it matters:

- track which exact object you’re working with.
- Identity stays the same throughout the object’s lifetime, even if its state changes.



## Object State

Definition: The data (fields/attributes) inside an object at a given point in time.

State is what makes one instance behave differently from another.

```php
class User {
    public $name;
    public $balance = 0;

    function __construct($name) {
        $this->name = $name;
    }
}

$u1 = new User("Ahmed");
$u2 = new User("Sara");

$u1->balance = 100;
$u2->balance = 200;
```

Both User objects are of the same class.

Their identity is different ($u1 !== $u2).

Their state differs (balance = 100 vs balance = 200).

✅ Why it matters:

- State evolves during the lifetime of the object.
- Behavior often depends on state (e.g., “if balance < 0 → overdraft”).


## Putting it together

Identity = who the object is (unique reference, doesn’t change).

State = what the object currently knows/contains (changes over time).

Behavior = what the object can do (methods/functions).