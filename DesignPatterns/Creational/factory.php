<?php

//also called factory method Because the factory itself is just a method with big switch inside

//factory method returns concrete implemenation, if it return factories then its abstract factory dp

// Product interface
interface PaymentGateway {
    public function pay(float $amount);
}

// Concrete products
class PaypalPayment implements PaymentGateway {
    public function pay(float $amount) {
        echo "Paying $amount using PayPal\n";
    }
}

class StripePayment implements PaymentGateway {
    public function pay(float $amount) {
        echo "Paying $amount using Stripe\n";
    }
}

// Factory class
class PaymentFactory {
    public static function create(string $type): PaymentGateway {
        return match ($type) {
            'paypal' => new PaypalPayment(),
            'stripe' => new StripePayment(),
            default  => throw new Exception("Unknown payment type: $type"),
        };
    }
}

// Client code
$payment = PaymentFactory::create('paypal');
$payment->pay(100);

$payment = PaymentFactory::create('stripe');
$payment->pay(200);