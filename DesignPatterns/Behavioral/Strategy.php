<?php


//idea: define a family of algorithms, put each of them into a separate class, and make their objects interchangeable.

//- strategy: is the algorithm
//- context: is the class which need the strategy to work


// Let’s say you have an e-commerce app. Customers can pay with Credit Card, PayPal, or Bitcoin.
// If you don’t use Strategy, you’d end up with a big if/else inside your payment service.


interface PaymentStrategy {
    public function pay(float $amount): void;
}


class CreditCardPayment implements PaymentStrategy {
    public function pay(float $amount): void {
        echo "💳 Paying $$amount using Credit Card\n";
    }
}

class PayPalPayment implements PaymentStrategy {
    public function pay(float $amount): void {
        echo "💻 Paying $$amount using PayPal\n";
    }
}

class BitcoinPayment implements PaymentStrategy {
    public function pay(float $amount): void {
        echo "₿ Paying $$amount using Bitcoin\n";
    }
}

class PaymentContext {
    private PaymentStrategy $strategy;

    public function __construct(PaymentStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function setStrategy(PaymentStrategy $strategy): void {
        $this->strategy = $strategy;
    }

    public function checkout(float $amount): void {
        $this->strategy->pay($amount);
    }
}

// Customer chooses PayPal
$payment = new PaymentContext(new PayPalPayment());
$payment->checkout(99.99);

// Later switches to Credit Card
$payment->setStrategy(new CreditCardPayment());
$payment->checkout(49.50);

// Later switches to Bitcoin
$payment->setStrategy(new BitcoinPayment());
$payment->checkout(0.005);



//we can take the strategies and create a factory method for them, if its required to have a factory
class PaymentFactory {
    public static function create(string $type): PaymentStrategy {
        return match($type) {
            'paypal' => new PayPalPayment(),
            'credit' => new CreditCardPayment(),
            'bitcoin' => new BitcoinPayment(),
            default => throw new Exception("Unknown payment type: $type")
        };
    }
}