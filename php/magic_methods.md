# Magic Methods

- __construct() — Constructor

- __destruct() — Destructor

- __get($name) — When accessing undefined property

- __set($name, $value) — When setting undefined property

- __isset($name) / __unset($name) .. Called when using isset() or unset() on inaccessible properties.

- __call($method, $args) — Call undefined instance method

- __callStatic($method, $args) — Call undefined static method

- __toString() — When object is converted to string

- __invoke() — When object is called like a function

- __clone() — When object is cloned


## Serialization

### Idea

Serialization = converting a PHP value (object, array, etc.) into a string representation
that can be stored or sent somewhere — and later restored back to its original structure.

```php

namespace App\Models;

class User {
    public $name = 'Ahmed';
    public $role = 'Admin';

    public function doxyz(){

    }
}

$user = new User();
$str = serialize($user);

echo $str;
// O:15:"App\Models\User":2:{s:4:"name";s:5:"Ahmed";s:4:"role";s:5:"Admin";}

$clone = unserialize($str);
var_dump($clone instanceof User); // true
```

### __sleep()

Called before serialization.

```php
class User {
    private $password = 'secret';
    private $name = 'Ahmed';
    
    public function __sleep() {
        //serialize only name
        return ['name'];
    }
}
```

### __wakeup()

Called after unserialization, maybe to reestablish database connection or something

```php

class User {
    public function __wakeup() {
        echo "User restored\n";
    }
}

```

### __serialize() and __unserialize()  PHP 7.4+

These replace the old methods and give you more control. 

```php

class User {
    private $name;
    private $role;

    public function __construct($name, $role) {
        $this->name = $name;
        $this->role = $role;
    }

    public function __serialize(): array {
        return ['n' => $this->name, 'r' => $this->role];
    }

    public function __unserialize(array $data): void {
        $this->name = $data['n'];
        $this->role = $data['r'];
    }
}

```