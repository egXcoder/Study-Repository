# Laravel Payments

you can generate temp email to use it in testing at https://temp-mail.org/


## Laravel Cashier

Laravel Cashier is Laravel’s out-of-the-box solution for recurring payments and subscription billing with Stripe or Paddle. It wraps the payment provider SDK into expressive Laravel syntax.


gateways always deal with money as cents, for example 20 USD .. gateways api always will be 2000.. since its easier to work with integer more safer and quicker.. so its like a convention for payment gateways to work with integer rather than decimals


`php artisan make:model Cart -mc` .. create model + migration + controller


```php 

//format amount into money like 20.00 USD
Cashier::formatAmount(2000)

```


## Cart

- cart and cart content is stored in database (header and lines)
- cart header is based on session .. in carts table there is a column called session_id .. so every cart is associated with session
- cart header can be associated with user_id if possible as well.
- whern user login/logout/register.. session id is regenerated
- when this happens .. you should update cart with the new session_id then user dont lose his cart


Q: if i am going to create a cart for every session.. i will end up with carts table to be untouchable, so how developers commonly address this?

- Link carts to users as soon as possible then reuse user cart without creating new cards
- Run a scheduled job (cron) to remove or archive carts older than X days.
- Delay creating a cart record until the user actually adds the first item.
- For guest users, you can rely on redis to store carts and hit the database only when user login
- On success payment, then delete cart


Q: so what if guest added lines to his cart .. then he logged in while he was having cart already?
- Merge guest cart into user’s existing cart (most common)
- discard guest cart/or user cart
- Ask the user to choose guest cart or user cart (rare, but safest)


### Checkout On Gateway Site

this is explained on another page


### Direct Payments


this is explained on another page


### Webhooks

when event happens in stripe, they can send you the event with its data to your webhook url.. https://example.com/stripe/webhook

if failed to send you the data that your server didnt respond with 2x response code, stripe will try to retry after 1min, 2m, 4m,16m,256m etc... till 3 days then it wont retry

use webhooks as your backup so that if any issue happens with regular flow, it will still notify you if payment reached final state of succeeded or payment_failed

```php
// this secret is used to make sure requests into webhook are coming from stripe themselves
// .env file
// STRIPE_WEBHOOK_SECRET=your-stripe-webhook-secret 


// command will create a webhook in Stripe platform that listens to all of the events required by Cashier:
// customer.subscription.created
// customer.subscription.updated
// customer.subscription.deleted
// customer.updated
// customer.deleted
// payment_method.automatically_updated
// invoice.payment_action_required
// invoice.payment_succeeded
php artisan cashier:webhook


// you can disabe stripe webhook
php artisan cashier:webhook --disabled


// Stripe webhooks need to bypass Laravel's CSRF protection
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->validateCsrfTokens(except: [
        'stripe/*',
    ]);
})


// to listen for a webhook, you can create a listener which listen to event WebhookReceived.. 
// this event is dispatched with the data when https://example.com/stripe/webhook is visited

namespace App\Listeners;
 
use Laravel\Cashier\Events\WebhookReceived;
 
class StripeEventListener
{
    /**
     * Handle received Stripe webhooks.
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->payload['type'] === 'invoice.payment_succeeded') {
            // Handle the incoming event...
        }
    }
}

```



### What are the failure points that can happen while paying

Card / Payment Method Issues
- Insufficient funds (the bank rejects).
- Card expired.
- Invalid card number or CVC.
- Card blocked / stolen (fraud detection).
- Unsupported card (e.g., local-only card being used internationally).
- 3D Secure (SCA) required but not completed by user.

👉You should show a clear error message and allow the customer to retry with another payment method.


Double charges
- customer clicks "Pay" twice

👉 Use Idempotent keys.

Payment Flow Issues + Network Issues
- Payment stuck in "requires_action" (like waiting for 3D Secure authentication).
- Canceled midway — user closes the window before confirmation.
- Slow bank response → user leaves thinking it failed, but later succeeds.
- Timeouts between your server and the payment gateway.

can be because
- User closed the popup instead of approving.
- Bank app didn’t send the confirmation.
- Browser didn’t redirect back properly.
- network dropped after paying on stripe and going back to my site

👉 Webhook is your backup to mark payment as succeeded if any issue happens

Third Backup (CronJob)
- if webhook was down because of your endpoint was down or something.. 
- its good idea to do a cron job to review payment intents that didnt reached final state 
- query these payment intents to check if payment has reached final state as succeeded or failed and act accordingly



Fraud & Compliance

- High-risk transactions (gateway may flag/suspend).
- Chargebacks / disputes — customer later says “I didn’t authorize”.
- KYC / AML restrictions — payment blocked due to compliance rules.

👉 Have a fraud prevention policy and handle disputes properly.



Currency & Amount Issues

- Wrong currency conversion (e.g., charging in USD instead of EUR).
- Amount mismatch between your system and gateway.
- Rounding errors if you use floating-point math instead of integers for money.

👉 Always store money in minor units (e.g., cents, pence, fils).


User Experience / UX Issues

- User retries unnecessarily and creates duplicate orders.
- User pays but doesn’t get redirected back → order marked unpaid.
- Failed to link payment to the right order/session.

👉 Always reconcile payments against your order IDs instead of trusting frontend.