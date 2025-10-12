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


## Question

when i use composition.. i sometimes get lost in the system because there are many parts on it.. how do you think i should address that.. god objects though are straight forward they can be understood quickly?

Inheritance / god objects feel simple at first: but with time they are rigid/fragile/bloadted

Composition gives flexibility and reuse, but yes — it can feel like a jungle of tiny classes, strategies, roles, decorators… and you may get lost navigating them.

to keep the benefits of composition without drowning in complexity

- Group related behaviors
    - Don’t split things into microscopic classes unless you need to.
    - Instead of AddUserRole, DeleteUserRole, UpdateUserRole, maybe just one UserManagementRole.
    - Composition doesn’t mean every method gets its own class — it means coherent responsibilities get their own class.

- Use clear naming and structure
    - Put related components in modules or namespaces.
    - Example: roles/AdminRole.java, roles/GuestRole.java.
    - A future reader sees the “parts box” in one place, not scattered.


- Document contracts / interfaces
    - When you use composition, the “glue” is the interface.
    - Document what an interface means (e.g., a Role must define doAction() or permissions()).
    - That way, when someone reads the code, they understand the rule of the game quickly.

- Favor common patterns
    - Composition often looks like known design patterns:
    - Strategy (swap behaviors)
    - Decorator (add features around an object)
    - Adapter (make an object look like another)
    - If you use these patterns explicitly, developers immediately recognize the intent → less confusion.

- Avoid over-engineering
    - If a feature is unlikely to change or combine with others → don’t over-compose.
    - Example: if your system only ever has two user types, inheritance might be fine.
    - Composition shines when you know roles/features will grow or mix.


👉 My rule of thumb:
- Start simple (even if it looks a bit god-objectish).
- When a class starts to have too many reasons to change → refactor into composition.