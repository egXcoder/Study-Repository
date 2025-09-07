<?php

// ❌ Bad (modifies every time a new gateway is added):
class PaymentService
{
    public function charge(float $amount, string $gateway)
    {
        if ($gateway === 'stripe') {
            // Stripe logic...
        } elseif ($gateway === 'paypal') {
            // PayPal logic...
        }
    }
}


// ✅ Good (OCP-friendly):
interface PaymentGateway {
    public function charge(float $amount): bool;
}

class StripeGateway implements PaymentGateway {
    public function charge(float $amount): bool {
        // Stripe API
        return true;
    }
}

class PayPalGateway implements PaymentGateway {
    public function charge(float $amount): bool {
        // PayPal API
        return true;
    }
}

class CheckoutService
{
    public function __construct(private PaymentGateway $gateway) {}

    public function processOrder(float $amount) {
        return $this->gateway->charge($amount);
    }
}