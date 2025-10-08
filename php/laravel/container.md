# Container


## Idea
It’s a central Place that knows how to construct objects and inject their dependencies automatically.

Its Used to assist on complying with Dependency Inversion Principle

### DI: 

high level modules shouldnt depend on low level module. both should depend on abstractions

```php

class Software{
    public function __construct(Ahmed $ahmed){} //bad
    public function __construct(WebDeveloperContract $ahmed){} //good
}

```

above example, cause everytime i want to instantiate Software. i have to pass new Ahmed .. later on if i want to replace Ahmed, i will have to search for everywhere new Ahmed and replace it with new John which is not ideal

### IOC Container

instead of keep instantiating every time you are constructing, you will invert this control..you will create a container and you are going to declare for this container anytime you ask for WebDeveloperContract from container then instantiate Ahmed.. then if later on i would replace Ahmed then it will be just matter of changing it in container


## Bind and Singleton

```php
# Bind, so that everytime you ask for class from container it will instantiate new instance
$this->app->bind(PaymentGateway::class, StripePaymentGateway::class);

// or

app()->bind('PaymentGateway', function ($app) {
    return new StripePaymentGateway(config('services.stripe.secret'));
});

```

```php
# Singelton, so that everytime you ask for class from container, it will give you the singleton
$this->app->singleton(CacheManager::class, function ($app) {
    return new CacheManager($app);
});

```

## Resolve

### Resolve Directly
```php
$gateway = app(PaymentGateway::class);
```

### Auto Resolve Dependency

- Laravel auto-resolves dependencies automatically when laravel asks for the class or the method such in controllers + jobs + listeners + commands + middlewares

- Even if you call `app(CheckoutController::class)` this will auto resolve

- but manually calling new CheckoutController wont auto inject
    

```php
class CheckoutController extends Controller
{
    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }
}
```

```php

public function handle(PaymentGateway $gateway)
{
    $gateway->charge(100);
}

```


## Where is binding happens

binding happens with service providers in register method


## When To Use it

### When using abstractions (interfaces or contracts) .. If your class depends on an interface, not a concrete class —
the container is what maps which concrete class to inject.

### When dependencies themselves have dependencies

```php

class OrderService {
    public function __construct(PaymentGateway $gateway) { ... }
}

class CheckoutController {
    public function __construct(OrderService $orderService) { ... }
}

```

If you use the container: `$controller = app(CheckoutController::class);`

without container: `$controller = new CheckoutController(new OrderService(new StripeGateway()));`

### When you want to make code easily testable

`$this->app->bind(PaymentGateway::class, FakePaymentGateway::class);` .. Now, your controller or service uses the fake automatically.


### When writing reusable packages or modules

you want easy way that you can override instantiation of classes, 
- you rely on the core BPService class 
- if bp module is enabled then rely on this BPService instead.. 
- if client overrided bp module BPService then use it instead..

all of that overriding can be declared within service providers and its just to amend the container mapping of what class to instantiate if asked for BPService


### When deferring expensive initialization

if you have expensive object to create like requesting api etc..

its similar to factory, but factory can be used anywhere to instantiate the object. while the logic of container is only if you are trying to instantiate with containers

i would recommend using factory if object is expensive unless you are very sure it will be always instatiated from container
then its fine to put creation logic into the service provider