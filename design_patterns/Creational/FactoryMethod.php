<?php

// every factory creates one type of product

abstract class PaymentFactory {
    // Factory Method
    abstract public function makePayment(): PaymentGateway;
}

class StripePaymentFactory extends PaymentFactory {
    public function makePayment(): PaymentGateway {
        return new StripePayment();
    }
}

class PayPalPaymentFactory extends PaymentFactory {
    public function makePayment(): PaymentGateway {
        return new PayPalPayment();
    }
}