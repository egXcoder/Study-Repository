<?php


//API Facade Pattern (Gateway)

//When To Use:
// You have multiple services (say 10) that all belong to the same business domain and public client directly depends on all of them.
// so you can group your classes into one gateway class which exposes the below services
// its like gateway where it can direct you to any route of internal possibilities depending on your choice
// ApprovingGateway -> ApprovingDataService
//                  -> ApprovingEmailService
//                  -> ApprovingPolicy


// When Not to use:
// Don’t turn each helper into a separate “service” in the container or module if they’re not meant to be used independently by other domains.
// (That just bloats the codebase and introduces circular dependencies.)
// Don’t make them depend on each other — only the main orchestrator (ApprovingService) should coordinate them.
// ApprovingService -> ApprovingDataHandler
//                  -> ApprovingEmailer
//                  -> ApprovingPolicy

class PaymentIntents {
    /**
     * methods are here to work with stripe payment intents
     */
}

class Customers {
    /**
     * methods are here to work with stripe customers
     */
}

class Refunds {
    /**
     * methods are here to work with stripe refunds
     */
}

// API Facade Pattern (Gateway Pattern)
class StripeClient{
    public $paymentIntents;
    public $customers;
    public $refunds;

    public function __construct()
    {
        $this->paymentIntents = new PaymentIntents();
        $this->customers = new Customers();
        $this->refunds = new Refunds();
    }
}

$stripe = new StripeClient(env('STRIPE_SECRET'));

// Create a payment
$paymentIntent = $stripe->paymentIntents->create([
    'amount' => 1000,
    'currency' => 'usd',
    'payment_method' => 'pm_card_visa',
    'confirm' => true,
]);

// Retrieve a customer
$customer = $stripe->customers->retrieve('cus_123');

// Refund a payment
$refund = $stripe->refunds->create([
    'payment_intent' => $paymentIntent->id,
]);