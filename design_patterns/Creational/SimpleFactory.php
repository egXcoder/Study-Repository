<?php

//create one product by one factory using logic

class PaymentFactory {
    public static function create(string $type): PaymentGateway {
        return match (strtolower($type)) {
            'stripe' => new StripePayment(),
            'paypal' => new PayPalPayment(),
            'cash'   => new CashPayment(),
            default  => throw new InvalidArgumentException("Unsupported payment type: $type"),
        };
    }
}

interface PaymentGateway {
    public function pay(float $amount);
}

// Concrete products
class StripePayment implements PaymentGateway {
    public function pay(float $amount): void {
        echo "💳 Processing \${$amount} payment via Stripe.\n";
    }
}

class PayPalPayment implements PaymentGateway {
    public function pay(float $amount): void {
        echo "🅿️ Processing \${$amount} payment via PayPal.\n";
    }
}

class CashPayment implements PaymentGateway {
    public function pay(float $amount): void {
        echo "💵 Processing \${$amount} payment via Cash.\n";
    }
}

// Client code
$payment = PaymentFactory::create('paypal');
$payment->pay(100);

$payment = PaymentFactory::create('stripe');
$payment->pay(200);



//in laravel  
$cache = Cache::store('redis'); // returns Redis cache driver

// The Cache manager internally works like a simple factory that returns the correct driver based on a string key.