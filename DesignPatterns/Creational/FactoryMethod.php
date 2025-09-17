<?php

//In the Factory Method pattern, the idea is that each subclass factory is responsible for creating one type of product
//subclass can be create it directly or subclass can create it from available list.

abstract class PaymentFactory {
    // Factory Method
    abstract public function createPayment(): PaymentGateway;
}

class StripePaymentFactory extends PaymentFactory {
    public function createPayment(): PaymentGateway {
        return new StripePayment();
    }
}

class PayPalPaymentFactory extends PaymentFactory {
    public function createPayment(): PaymentGateway {
        return new PayPalPayment();
    }
}


interface PaymentGateway {
    public function pay(float $amount): void;
}

class StripePayment implements PaymentGateway {
    public function pay(float $amount): void {
        echo "💳 Paid \${$amount} with Stripe.\n";
    }
}

class PayPalPayment implements PaymentGateway {
    public function pay(float $amount): void {
        echo "🅿️ Paid \${$amount} with PayPal.\n";
    }
}