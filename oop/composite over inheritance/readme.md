# Composite Over Inheritance


Both of them are ways to reuse code

## Inheritance

```java
class User {
    public $name;
}

class AdminUser extends User {
    function manageUsers() {}
}

class CustomerUser extends User {
    function placeOrder() {}
}

class SupplierUser extends User {
    function sendInvoice() {}
}

// Combining issue: What if a Supplier is also a Customer? PHP doesn’t allow multiple inheritance??
// Explosion issue: What if you add a new role like Manager, Employee, Auditor? You’ll explode into dozens of subclasses.
// LSP issue: what if we have APIUser .. he is not really user but he needs some code from User Parent to work
// Change issue: what if i want to change User parent.. would i break childs? would all childs still follow parent contract?
// Add issue: what if i want to add more logic to User.. would User become god object?
```

Relying on inheritance:
- tight coupling (sub classes are tight to parent)
- inflexible (you have to inherit parent to reuse code)
- Hierarchy Explosion (you may end up with 3 or 4 levels of inhertiance)

Inheritance is good thought till some point where you start to rely on it more, then it will fail you. as long as parent is small and childs are obeying parent contract then no issue with it.


## Composition

building behavior by combining smaller, independent object.

OOP in principle encourage modularity. where every part exists separately then you can break big systems into smaller, reusable, testable parts. so by nature oop encourage composition more


```php

interface Capability {
    function execute();
}

class ManageUsersCapability implements Capability {
    function execute() { echo "Managing users...\n"; }
}

class PlaceOrderCapability implements Capability {
    function execute() { echo "Placing order...\n"; }
}

class SendInvoiceCapability implements Capability {
    function execute() { echo "Sending invoice...\n"; }
}

class User {
    public $name;
    private $capabilities = [];

    function __construct($name) {
        $this->name = $name;
    }

    function addCapability(Capability $capability) {
        $this->capabilities[] = $capability;
    }

    function performAll() {
        foreach ($this->capabilities as $capability) {
            $capability->execute();
        }
    }
}
```

Tip: Strategy design pattern is the clearest way to follow composition. but composition idea are used by other patterns as well to do intersting things... actually most of design patterns are about composition.. maybe one or two is about inheritance (template method, Abstract Factory)

Tip: it doesnt have to be this or that.. inheritance can work with composition.. inheritance is very good as long as you keep it short..