<?php

//show you when template method can be bad

abstract class PaymentProcessor {
    // Template method
    public final function process(float $amount) {
        $this->validate($amount);
        $this->debit($amount);
        $this->sendReceipt($amount);
    }

    protected function validate(float $amount) {
        if ($amount <= 0) {
            throw new Exception("Invalid amount");
        }
    }

    // Hooks
    abstract protected function debit(float $amount);

    protected function sendReceipt(float $amount) {
        echo "Receipt: Paid $amount\n";
    }
}

class PaypalProcessor extends PaymentProcessor {
    protected function debit(float $amount) {
        echo "Debiting $amount via PayPal\n";
    }
}

class StripeProcessor extends PaymentProcessor {
    protected function debit(float $amount) {
        echo "Debiting $amount via Stripe\n";
    }
}

// ✅ Good when the skeleton is fixed (validate → debit → receipt).
// ❌ But if different payment providers have different steps (e.g., Stripe needs 3D Secure, PayPal doesn’t), you start bloating the base class with conditionals or adding awkward hooks → inheritance pain.
// so template method is good as long as there are one template method, but when there are many. it will better to look for alternative



//using strategy instead
interface PaymentStrategy {
    public function pay(float $amount);
}

class PaypalStrategy implements PaymentStrategy {
    public function pay(float $amount) {
        echo "Paying $amount with PayPal\n";
    }
}

class StripeStrategy implements PaymentStrategy {
    public function pay(float $amount) {
        echo "Paying $amount with Stripe + 3D Secure\n";
    }
}

class PaymentContext {
    private PaymentStrategy $strategy;

    public function __construct(PaymentStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function checkout(float $amount) {
        $this->strategy->pay($amount);
    }
}

// Usage
$payment = new PaymentContext(new StripeStrategy());
$payment->checkout(100);

// ✅ Each payment provider has complete freedom to define its algorithm.
// ✅ Easy to switch strategies at runtime.
// ❌ Slightly more wiring code (need a context or DI).