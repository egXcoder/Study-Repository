<?php

//Prototype pattern lets you clone an existing object instead of creating it from scratch.
// Object creation is expensive (e.g., involves DB queries, complex calculations).
// You want to avoid re-initializing an object every time.


// Prototype Registry: its a class which stores predefined templates of objects, so that when you need instance
// you would bring the object from registry and clone it, instead of re-instantiating it every time with BuilderDirector maybe 


// many high-level languages like Java, C#, php), cloning is built into the language, 
//so you can use Prototype pattern by “just use clone”. unless you want custom things then you can override clone methods


//clone in PHP is shallow copy by default.
interface Prototype {
    public function __clone();
}

class User implements Prototype {
    public string $name;
    public string $role;

    public function __construct(string $name, string $role) {
        $this->name = $name;
        $this->role = $role;
    }

    public function __clone() {
        // You can customize what happens during cloning
        $this->name = $this->name . " (copy)";
    }
}

// Usage
$admin = new User("Ahmed", "Admin");
$editor = clone $admin;  // Prototype cloning
$editor->role = "Editor";

echo $admin->name . " - " . $admin->role . "\n";   // Ahmed - Admin
echo $editor->name . " - " . $editor->role . "\n"; // Ahmed (copy) - Editor



//example of applying Prototype design patten without the usage of clone keyword
interface Prototype {
    public function copy(): Prototype;
}

class User implements Prototype {
    private string $name;
    private string $role;

    public function __construct(string $name, string $role) {
        $this->name = $name;
        $this->role = $role;
    }

    public function copy(): Prototype {
        // manual "cloning" instead of PHP clone
        return new User($this->name, $this->role);
    }

    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function info(): void {
        echo "{$this->name} is {$this->role}\n";
    }
}

// Usage
$admin = new User("Ahmed", "Admin");
$editor = $admin->copy();   // instead of clone
$editor->setRole("Editor");

$admin->info();  // Ahmed is Admin
$editor->info(); // Ahmed is Editor




// Prototype Registry
class UserRegistry {
    private $prototypes = [];

    public function addPrototype(string $key, User $user): void {
        $this->prototypes[$key] = $user;
    }

    public function getUser(string $key): ?User {
        if (!isset($this->prototypes[$key])) {
            return null;
        }
        return $this->prototypes[$key]->copy();
    }
}


// Create registry
$registry = new UserRegistry();

// Register some prototypes
$registry->addPrototype("admin", new User("Default Admin", "admin", ["manage_users", "view_reports", "delete"]));
$registry->addPrototype("guest", new User("Guest User", "guest", ["view"]));
$registry->addPrototype("customer", new User("Default Customer", "customer", ["buy", "review"]));

// Get users from registry
$admin1 = $registry->getUser("admin");
$admin1->name = "Ahmed"; // modify as needed

$guest1 = $registry->getUser("guest");
$guest1->name = "Visitor123";

$customer1 = $registry->getUser("customer");
$customer1->name = "Fatima";