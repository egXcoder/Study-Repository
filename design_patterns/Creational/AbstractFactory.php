<?php

// Every Factory able to make family of similar products
// It’s like Factory Method on steroids.


interface PaymentProviderFactory {
    public function makePaymentGateway(): PaymentGateway;
    public function makeInvoiceGenerator(): InvoiceGenerator;
    public function makeRefundProcessor(): RefundProcessor;
}


class StripeFactory implements PaymentProviderFactory {
    public function makePaymentGateway(): PaymentGateway {
        return new StripePayment();
    }
    public function makeInvoiceGenerator(): InvoiceGenerator {
        return new StripeInvoice();
    }
    public function makeRefundProcessor(): RefundProcessor {
        return new StripeRefund();
    }
}

class PayPalFactory implements PaymentProviderFactory {
    public function makePaymentGateway(): PaymentGateway {
        return new PayPalPayment();
    }
    public function makeInvoiceGenerator(): InvoiceGenerator {
        return new PayPalInvoice();
    }
    public function makeRefundProcessor(): RefundProcessor {
        return new PayPalRefund();
    }
}