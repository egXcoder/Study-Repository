## Webhooks

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