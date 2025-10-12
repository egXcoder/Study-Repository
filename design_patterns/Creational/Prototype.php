<?php

// clone an existing object instead of creating it from scratch.
// as object creation is expensive (e.g., involves DB queries, complex calculations).


// Prototype Registry is useful when you have set of template objects.
// you create these templates only once and whenever you need it you can clone


// many high-level languages like Java, C#, php), cloning is built into the language, 
//so you can use Prototype pattern by “just use clone”. unless you want custom things then you can override clone methods


interface Prototype {
    public function clone(): Prototype;
}

class User implements Prototype {
    private string $name;
    private string $role;

    public function __construct(string $name, string $role) {
        $this->name = $name;
        $this->role = $role;
    }

    public function clone(): Prototype {
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
class PrototypeRegistry {
    private $prototypes = [];

    public function addPrototype(string $key, User $user): void {
        $this->prototypes[$key] = $user;
    }

    public function createClone(string $key): ?Prototype {
        return isset($this->prototypes[$key]) ? clone $this->prototypes[$key] : null;
    }
}


// Create registry
$registry = new PrototypeRegistry();

// Register default invoice
$invoiceTemplate = new Invoice();
$invoiceTemplate->setVatRate(15);
$invoiceTemplate->setPaymentTerms("Net 30");
$registry->addPrototype("default_invoice", $invoiceTemplate);

// Later, create a new invoice based on template
$newInvoice = $registry->createClone("default_invoice");
$newInvoice->setCustomer("ACME Ltd");
$newInvoice->setAmount(5000);



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
