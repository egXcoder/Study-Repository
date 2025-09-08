# Dependency Inversion Principle


High-level modules (like Controllers, Services) shouldn’t depend on low-level modules (concrete classes).

Both (controllers and concrete classes) should depend on abstractions (interfaces/contracts).

This makes your code flexible, testable, and easier to swap implementations.

With DIP, your controllers/services don’t care about implementation details — they only care about contracts. Laravel’s service container makes this super easy.

## it’s called Dependency Inversion because we invert the usual dependency direction:
- Normally: High-level → Low-level
- With DIP: High-level → Abstraction ← Low-level


# Benefits of DIP here
- Swap implementations easily: Change binding in one place to use PayPal instead of Stripe.
- Testability: You can pass a FakeClass when unit testing.



## Q: when it comes for controller to depend on interface, i understand laravel is pretty powerful on dependecy injection using containers, but this isn't always the case with other languages like java.. i have to pass the class everytime i instantiate controller which is difficult


You’re right — Laravel makes DIP feel natural because of its IoC container (inversion of control container). But in other languages or frameworks (where you don’t have a container), it can feel painful to keep passing dependencies manually.

Let’s break it down 👇

1. Why Laravel feels easy
```php
class PaymentController extends Controller
{
    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }
}
```

You don’t manually instantiate PaymentController. Laravel resolves it for you via the service container:

It looks at the constructor Finds PaymentGatewayInterface Resolves the binding you declared (StripePaymentGateway) Injects it automatically. So, you just call a route → Laravel gives you a ready PaymentController.
No new keywords, no wiring every time. ✨


2. In other languages/frameworks without IoC
Say you’re in plain PHP, Java, or C++ without DI container. Then you’d have to wire things manually:

```php
$gateway = new StripePaymentGateway();
$controller = new PaymentController($gateway);
$controller->pay();
```

Now imagine 20 controllers each with 3 dependencies.
Suddenly you have a huge mess of new keywords and passing arguments.


3. How people solve it outside Laravel
That’s why in other ecosystems you see dependency injection frameworks / containers:

- Java → Spring (IoC container)
- C# → .NET Core Dependency Injection
- Node.js → typedi, inversifyJS
- Python → dependency-injector

They all aim to give the same benefit Laravel gives you for free.


## Q: but if i don't have ioc, i think i should violate dip since having to instantiate dependencies everytime feels overwhelming?

You’re right — if you don’t have an IoC container (like in plain PHP, or a minimal framework), following DIP to the letter can feel overwhelming because:
- You need to manually instantiate dependencies (new StripePaymentGateway() everywhere).
- You end up with a lot of wiring code that feels like boilerplate.
- For small projects, it feels like DIP is adding complexity without giving much benefit.


## When it’s okay to “bend” DIP
- Small scripts or prototypes → You just want things to work, not future-proof everything.
- One developer project → You control all the code; swapping implementations isn’t a priority.
- No IoC container → Manually wiring every dependency adds more friction than value.
In these cases, it’s fine to let your controller directly new a service. You’re trading strict adherence to DIP for simplicity and speed.


## When you should stick with DIP
- Growing projects (teams, multiple features, third-party integrations).
- Multi-tenant systems where requirements vary by client.
- Code that lives for years and will be extended a lot.
- Unit testing matters → DIP makes mocking and swapping dependencies trivial.
In these cases, DIP saves time later even if it feels like extra work now.


## IoC (Inversion of Control)
is a design principle where the control of creating and managing objects is transferred from your code to a container or framework. Instead of your code deciding how and when to create dependencies, the framework handles it for you.

Normally, in plain code:
```php
class PaymentController {
    public function pay() {
        $gateway = new StripeGateway(); // controller controls the creation of StripeGateway. That’s normal control.
        $gateway->charge(100);
    }
}
```

With IoC:
```php
class PaymentController {
    // Now the controller doesn’t control how $gateway is created.
    // The IoC Container (like Laravel’s service container) creates and injects it automatically.
    public function __construct(private PaymentGateway $gateway) {}

    public function pay() {
        $this->gateway->charge(100);
    }
}
```

## How IoC works in Laravel

```php
interface PaymentGateway {
    public function charge(float $amount);
}
```

```php
$this->app->bind(PaymentGateway::class, StripeGateway::class); //bind implemenation into container
```

```php
class PaymentController extends Controller
{
    public function __construct(private PaymentGateway $gateway) {}

    public function pay() {
        $this->gateway->charge(100);
    }
}
```

Laravel’s IoC container: Sees PaymentGateway in the constructor. Finds the binding from container (StripeGateway). Creates and injects it automatically.