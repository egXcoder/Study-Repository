<?php

// every factory creates one type of product

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