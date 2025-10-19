# Use cases:
1- guest checkout
2- pay as a user and save payment methods
3- subscribe to a plan with payment upfront
4- subscribe to a plan and pay after trial
5- show invoice after paying
6- cancel or resume subscription
7- refund


Tip: Payment Intent dont trigger 3D secure. you have to manually confirm it to trigger 3D secure. 

Tip: if you create payment intent with confirm flag as yes, it will try to auto confirm it without 3D secure it may succeed or intent will stay in status require_confirmation

Tip: you can use stripe subscription object rather than manage your own subscription via cronjob.. but i don't recommend it in general
- Pros
    - Save you a lot of code and effort
    - you dont have to worry about payment/setup intents etc.. stripe will handle it for you
    - you have invoices functionality out of the box

- Cons
    - tight couple to stripe so you cant use other gateways later on..
    - more effort and manage edge cases and watch out for bugs
    - building your own invoice templates

Tip: common subscription statuses
- trialing: Subscription is active but user is in a free trial period.
- active: Customer has paid
- past_due: Latest invoice is unpaid. Customer’s card failed, or payment requires action. Subscription is still active, but you need to resolve payment. You can configure dunning (retry rules, emails).
- canceled: Subscription has been canceled (either by you or customer).
- incomplete: Created but first invoice not paid yet.Usually happens when PaymentIntent requires action (e.g., 3D Secure) and user hasn’t completed it.
- incomplete_expired: The user never completed the first payment. Stripe auto-cancels the subscription after 23 hours.



### Guest checkout

```php
// 1- create payment intent with no customer details
$paymentIntent = PaymentIntent::create([
    'amount' => $amount,
    'currency' => 'usd',
    // No 'customer' field here -> pure guest
    'metadata' => [
        'order_id' => 1234, // link to your DB order
    ],
]);

// 2- in front end use the intent to confirm card setup
// if 3d secure is required.. then a popup will show to finish the 3D secure
// you should rely on webhooks and cronjobs as backups to always reconcile your created payment intents against the order
fetch("/checkout", { method: "POST" })
  .then(res => res.json())
  .then(data => {
    const clientSecret = data.clientSecret;

    // Confirm card payment
    stripe.confirmCardPayment(clientSecret, {
      payment_method: {
        card: elements.getElement(CardElement), // your Stripe.js card input
        billing_details: {
          email: document.querySelector("#email").value,
          name: document.querySelector("#name").value,
        },
      }
    }).then(result => {
      if (result.error) {
        console.error(result.error.message);
        alert("Payment failed");
      } else if (result.paymentIntent.status === "succeeded") {
        alert("Payment succeeded!");
        // Redirect or mark order as paid
      }
    });
  });

```

### Pay as a user and save payment methods

```php
// 1- create intent
$paymentIntent = PaymentIntent::create([
    'amount' => 5000, // e.g. $50
    'currency' => 'usd',
    'customer' => $user->stripe_id,
    'setup_future_usage' => 'off_session', //tells Stripe: "save this card for later automatic charges"
    'metadata' => [
        'order_id' => 1234,
    ],
]);

// 2- same as guest checkout.. use intent to finalize payment 
fetch("/checkout", { method: "POST" })
  .then(res => res.json())
  .then(data => {
    stripe.confirmCardPayment(data.clientSecret, {
      payment_method: {
        card: elements.getElement(CardElement),
        billing_details: {
          name: document.querySelector("#name").value,
          email: document.querySelector("#email").value
        }
      }
    }).then(result => {
      if (result.error) {
        alert(result.error.message);
      } else if (result.paymentIntent.status === "succeeded") {
        alert("Payment success, card saved!");
      }
    });
  });


// 3 later, use will go to checkout page and i will list him his stripe payment methods
// he shall choose one of them and click pay which will do the below
$paymentIntent = PaymentIntent::create([
    'amount' => 2500, // e.g. $25
    'currency' => 'usd',
    'customer' => $user->stripe_id,
    'payment_method' => $paymentMethodId, // from Stripe's list
]);


// 4 now front end will use the intent to finalize payment same way
fetch("/checkout", { method: "POST" })
  .then(res => res.json())
  .then(data => {
    stripe.confirmCardPayment(data.clientSecret, {
      payment_method: {
        card: elements.getElement(CardElement),
        billing_details: {
          name: document.querySelector("#name").value,
          email: document.querySelector("#email").value
        }
      }
    }).then(result => {
      if (result.error) {
        alert(result.error.message);
      } else if (result.paymentIntent.status === "succeeded") {
        alert("Payment success, card saved!");
      }
    });
  });
```


### Subscribe to a plan with payment upfront


```php
// 1- do your first payment
$paymentIntent = PaymentIntent::create([
    'amount' => 1000, // $10 in cents
    'currency' => 'usd',
    'customer' => $user->stripe_id,
    'setup_future_usage' => 'off_session', // save card for renewals
    'metadata' => [
        'subscription_plan' => 'pro-monthly',
        'user_id' => $user->id,
    ],
]);

//2- frontend to confirm this as above

//3- create subscriber record in your subscribers table and put user_id, plan, status, next_billing_date created_at
// this subscriber table they are the place where you are going to manager your subscribers
// another table to record payment intents raised against subscribers

//4- Renewal .. cronjob
$subscription = Subscription::where('next_billing_date', '<=', now())->first();

$paymentIntent = PaymentIntent::create([
    'amount' => 1000,
    'currency' => 'usd',
    'customer' => $subscription->stripe_customer_id,
    'payment_method' => $subscription->stripe_payment_method_id,
    'off_session' => true, // charge without user present
    'confirm' => true,
    'metadata' => [
        'subscription_id' => $subscription->id,
    ],
]);

if ($paymentIntent->status === 'succeeded') {
    $subscription->next_billing_date = now()->addMonth();
    $subscription->save();
} else {
    $subscription->status = 'past_due'; // handle retries/failed state
    $subscription->save();
}

// You must handle:
// Renewals
// Retry logic
// Invoice history
// Tax, coupons, upgrades/downgrades manually
```


### Subscribe to a plan and pay after trial

```php
// 1- create setup intent
// The SetupIntent is not itself a charge — it’s only a way to collect and save a payment_method_id safely with SCA/3D Secure if required.
$setupIntent = SetupIntent::create([
    'customer' => $user->stripe_id,
    'payment_method_types' => ['card'],
    'usage' => 'off_session', // save for future auto-charges
]);


// 2- confirm payment method, intent is built and ready to be charged
// but up to this point no money moved and its just setup intent saved ready to be billed when required
// You must store that payment_method_id returned from setupIntent.payment_method in your DB alongside the subscription record.
stripe.confirmCardSetup(clientSecret, {
  payment_method: {
    card: elements.getElement(CardElement),
    billing_details: {
      name: "John Doe",
      email: "john@example.com"
    }
  }
}).then(result => {
  if (result.error) {
    alert(result.error.message);
  } else {
    // Save result.setupIntent.payment_method to your DB
  }
});


// 3- End Of trial (Cronjob)
$subscription = Subscription::where('status', 'trialing')
    ->where('next_billing_date', '<=', now())
    ->first();

$paymentIntent = PaymentIntent::create([
    'amount' => 1000, // $10
    'currency' => 'usd',
    'customer' => $subscription->stripe_customer_id,
    'payment_method' => $subscription->stripe_payment_method_id,
    'off_session' => true, // no customer interaction
    'confirm' => true,
    'metadata' => [
        'subscription_id' => $subscription->id,
    ],
]);

if ($paymentIntent->status === 'succeeded') {
    $subscription->status = 'active';
    $subscription->next_billing_date = now()->addMonth();
    $subscription->save();
} else {
    $subscription->status = 'past_due'; // handle retries
    $subscription->save();
}

```

Question: so why to use setup intent, we could have used saving payment method directly?

saving payment method don't trigger the 3d secure .. you have to use setup intent with payment method to trigger the 3d secure then your payment method will be ready to be used later without triggering the 3d secure



### Show invoice after paying

whenever payment happens, you should always be able to generate pdf of this payment. then customers can download their pdf version + you can auto email the invoice to customers if you wish..



### Cancel or resume subscription


you should be able to cancel or resume subscription as long as its active and fall within active period..



### Refund


When the PaymentIntent succeeds .. Stripe automatically creates a Charge object. A Charge is the actual debit on the customer’s card. A Charge object always belongs to a PaymentIntent.


```php
//fully refund
$refund = \Stripe\Refund::create([
    'charge' => $chargeId,
]);

echo "Refunded: " . $refund->amount / 100 . " " . strtoupper($refund->currency);


//partially refund
$refund = \Stripe\Refund::create([
    'charge' => $chargeId,
    'amount' => 5000, // $50.00 if currency is USD (amount in cents)
]);

```


Refund Flow:

- Customer pays invoice/order → PaymentIntent succeeds → Stripe generates a Charge.
- You decide to refund (via dashboard or API).
- Refund object created → Stripe processes refund → money returns to customer’s card/bank (1–10 days depending on bank).
- Refund event fires a charge.refunded (or refund.updated) webhook.
- You should listen for this to update your order status (e.g., mark as refunded).

Webhooks events

- charge.refunded → fired immediately when Stripe creates the refund object.

- refund.updated → fired if status changes (e.g., from pending → succeeded or failed).

Refund statuses:
    - pending → refund created, waiting for bank.
    - succeeded → bank processed it.
    - failed → rare, refund couldn’t be processed.