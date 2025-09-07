<?php

interface PaymentGateway {
    public function charge(float $amount): Transaction;
}


// StripeGateway → charges via Stripe API.
// PaypalGateway → charges via PayPal API.
// ✅ Both return a Transaction object.
// ❌ If PaypalGateway returns null or throws for certain amounts (“we don’t support amounts under $1”), 
// then it breaks LSP. Clients shouldn’t need to know about these differences.


// what if we are in multi tenant environment
// and one of the tenant asked we don't want amounts less than 1$ in our paypal gateway.. 
// so we will have to extend the class in their tenant and set this rule for them which break lsp.. 
// i can't force always to respect the parent because clients ask for different things in the subclasses


// That’s a great real-world scenario 👍 and exactly where the Liskov Substitution Principle (LSP) gets tricky.
// You’re right:
// If a tenant-specific rule (like “no charges < $1 in PayPal”) is added inside the subclass 
// → you break LSP because callers expect all PaymentGateway implementations to behave the same.


// ❌ Wrong Way (LSP Violation)
class TenantPaypalGateway implements PaymentGateway {
    public function charge(float $amount): Transaction {
        if ($amount < 1) {
            throw new Exception("Not supported");
        }

        // normal PayPal charge...
    }
}


// ✅ Better Approach 1: Use a Validation Layer
// Instead of breaking the contract in PaymentGateway, enforce tenant rules before calling the gateway.
class TenantPaymentValidator {
    public function validate(string $gateway, float $amount, Tenant $tenant): void {
        if ($tenant->id === 123 && $gateway === 'paypal' && $amount < 1) {
            throw new Exception("This tenant does not allow amounts below $1 on PayPal");
        }
    }
}


// Then in your service:
$validator->validate('paypal', $amount, $tenant);
$gateway->charge($amount); // Here, PaypalGateway still respects the global PaymentGateway contract → ✅ LSP holds.



// Why it "easier" to just extend? and break lsp
// …it feels convenient:
// No extra service layer.
// You can override behavior right where it’s needed.
// Quick to implement for one tenant.


// Why is breaking LSP a problem then?
// 1. Surprises for Callers
// The whole point of having PaymentGateway is so you can class $paymentService->checkout($gateway,float $amount)
// …and trust that all gateways behave the same way.
// If one suddenly rejects amounts < 1, your code has to special-case it:
// Now every caller has to know about this exception → the abstraction is broken.


// 2. You Lose Extensibility
// The whole benefit of having an interface like PaymentGateway is polymorphism:
// “I don’t care if it’s Stripe, PayPal, or whatever — as long as it implements charge().”
// If some implementations don’t behave the same, you lose polymorphism.
// Now every feature that depends on it has to branch.


// 3. Testing Becomes Painful
// Unit tests relying on the PaymentGateway contract won’t pass for tenant-specific subclasses, because the behavior changed.
// This leads to:
// more special cases testing for every subclass
// more mocks/stubs
// duplicated test logic
// more brittle tests