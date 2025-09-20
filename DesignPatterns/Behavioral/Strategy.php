<?php


//idea: when you have multiple ways to do the same thing, define each way in a class

// you would use strategy for:
// - when you have multiple ways to something
// - you want to switch between these algorithms dynamically in run time
// - alternatively, you can have big class like SortingAlgorithms and within you can declare methods, each method
//  define one sorting algorithm, but you end up with big object that doing multiple things (Violating SRP)
//  also change frequently if to add/edit sorting algorithm (violating OCP)
//  you can do it though for small project but while your project growing up
//  you will start feel the rigidity and you will refactor it to strategy pattern 


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