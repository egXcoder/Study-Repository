<?php

// ❌ Bad Example (violates DIP)
class PaymentController extends Controller
{
    public function pay()
    {
        // Controller depends on a concrete class
        $gateway = new StripePaymentGateway();
        $gateway->charge(1000);
    }
}


// 🔴 Problems:
// If you switch from Stripe to PayPal, you must edit the controller.
// Hard to unit test because the controller directly creates Stripe.


// ✅ Good Example (follows DIP)
interface PaymentGatewayInterface
{
    public function charge(int $amount);
}

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function charge(int $amount)
    {
        // Stripe API logic
    }
}

class PaypalPaymentGateway implements PaymentGatewayInterface
{
    public function charge(int $amount)
    {
        // PayPal API logic
    }
}

// Use constructor injection in the controller
class PaymentController extends Controller
{
    protected $gateway;

    // Depends on abstraction, not a concrete class
    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function pay()
    {
        $this->gateway->charge(1000);
    }
}

// Bind the interface to a concrete class in a Service Provider
public function register()
{
    $this->app->bind(
        PaymentGatewayInterface::class,
        StripePaymentGateway::class
    );
}



// Testability: You can pass a FakePaymentGateway when unit testing.
class FakePaymentGateway implements PaymentGatewayInterface
{
    public $charges = [];

    public function charge(int $amount)
    {
        $this->charges[] = $amount;
    }
}


// Now in tests:
$this->app->bind(PaymentGatewayInterface::class, FakePaymentGateway::class);
