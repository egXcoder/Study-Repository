<?php

// It’s like Factory Method on steroids.
// While Factory Method creates one product at a time,
// Abstract Factory creates a family of related products that should work together.


interface PaymentProviderFactory {
    public function createPayment(): PaymentGateway;
    public function createInvoice(): InvoiceGenerator;
    public function createRefund(): RefundProcessor;
}


class StripeFactory implements PaymentProviderFactory {
    public function createPayment(): PaymentGateway {
        return new StripePayment();
    }
    public function createInvoice(): InvoiceGenerator {
        return new StripeInvoice();
    }
    public function createRefund(): RefundProcessor {
        return new StripeRefund();
    }
}

class PayPalFactory implements PaymentProviderFactory {
    public function createPayment(): PaymentGateway {
        return new PayPalPayment();
    }
    public function createInvoice(): InvoiceGenerator {
        return new PayPalInvoice();
    }
    public function createRefund(): RefundProcessor {
        return new PayPalRefund();
    }
}


// Stripe family
class StripePayment implements PaymentGateway {
    public function pay(float $amount): void {
        echo "💳 Stripe processed payment of \${$amount}\n";
    }
}

class StripeInvoice implements InvoiceGenerator {
    public function generate(string $orderId): void {
        echo "🧾 Stripe invoice generated for order {$orderId}\n";
    }
}

class StripeRefund implements RefundProcessor {
    public function refund(float $amount): void {
        echo "↩️ Stripe refunded \${$amount}\n";
    }
}


// PayPal family
class PayPalPayment implements PaymentGateway {
    public function pay(float $amount): void {
        echo "🅿️ PayPal processed payment of \${$amount}\n";
    }
}

class PayPalInvoice implements InvoiceGenerator {
    public function generate(string $orderId): void {
        echo "🧾 PayPal invoice generated for order {$orderId}\n";
    }
}

class PayPalRefund implements RefundProcessor {
    public function refund(float $amount): void {
        echo "↩️ PayPal refunded \${$amount}\n";
    }
}


//in laravel
DB::connection('mysql');

// the Database Manager acts like an abstract factory:
// MySqlConnection produces not just the connection, but a family of related objects (query grammars, schema grammars, processors) that all belong to the "MySQL ecosystem".
// If you switch to PostgresConnection, you get a whole consistent family of Postgres-specific objects.