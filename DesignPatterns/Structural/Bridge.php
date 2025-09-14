<?php


// You’re building an app that supports different payment methods (PayPal, Stripe, etc.), 
// and you want to allow different types of purchases (Online Order, Subscription, etc.).

// If you don’t use Bridge → you might end up with classes like 
// OnlineOrderWithPayPal, OnlineOrderWithStripe, SubscriptionWithPayPal, SubscriptionWithStripe, … and it explodes quickly.


// With Bridge → you separate:
// Abstraction (Purchase Type) → Online order, Subscription.
// Implementation (Payment Gateway) → PayPal, Stripe.


interface PaymentGateway {
    public function pay(float $amount): void;
}

//concrete payment gateways
class PayPalGateway implements PaymentGateway {
    public function pay(float $amount): void {
        echo "Paying $amount USD using PayPal\n";
    }
}

class StripeGateway implements PaymentGateway {
    public function pay(float $amount): void {
        echo "Paying $amount USD using Stripe\n";
    }
}

//puchase abstraction
abstract class Purchase {
    protected PaymentGateway $gateway;

    public function __construct(PaymentGateway $gateway) {
        $this->gateway = $gateway;
    }

    abstract public function checkout(float $amount): void;
}

//purchase implemenation, depend on interface of payment gateway
class OnlineOrder extends Purchase {
    public function checkout(float $amount): void {
        echo "Processing an online order...\n";
        $this->gateway->pay($amount);
    }
}

class Subscription extends Purchase {
    public function checkout(float $amount): void {
        echo "Processing a subscription...\n";
        $this->gateway->pay($amount);
    }
}