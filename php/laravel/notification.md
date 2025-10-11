# Notification


In Laravel, notifications are a simple way to send short messages or alerts to users.

They are usually things like:

- “Your order has been shipped.”
- “You have a new friend request.”
- “Password reset link.”


## Where can notifications be delivered?

Laravel supports multiple channels, meaning you can send the same notification in different ways:

- Mail → send email (MailChannel)
- Database → store in DB for in-app notifications (DatabaseChannel)
- Broadcast → send real-time notifications via WebSockets (BroadcastChannel)
- Slack → send to Slack channel (SlackChannel)
- SMS / Nexmo / Vonage → send via text message
- Custom channels → you can build your own (e.g. push notifications via Firebase)


## How to create a Notification?

`php artisan make:notification InvoicePaid`

```php

//app/Notifications/InvoicePaid.php

class InvoicePaid extends Notification
{
    use Queueable;

    public function __construct(public $invoice) {}

    // Tell Laravel which channels to use
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    // Format for email
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Invoice Paid')
                    ->line('Your invoice has been successfully paid.')
                    ->action('View Invoice', url('/invoices/'.$this->invoice->id));
    }

    // Format for database
    public function toDatabase($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->amount,
        ];
    }
}

```

## Sending a Notification
```php
$user->notify(new InvoicePaid($invoice));

Notification::send($users, new InvoicePaid($invoice));

```

## Reading Notification

```php

// Unread notifications
$user->unreadNotifications;

// Mark as read
$user->unreadNotifications->markAsRead();

```

## Database Channel

if to use database channel, you have to do below migration

`php artisan notifications:table`
`php artisan migrate`

| Column          | Type      | Description                                               |
|-----------------|-----------|-----------------------------------------------------------|
| **id**          | UUID      | Unique notification ID                                    |
| **type**        | string    | Class name of notification (`App\Notifications\InvoicePaid`) |
| **notifiable_id** | integer | ID of the user (or model)                                 |
| **notifiable_type** | string | Class of the notifiable (`App\Models\User`)              |
| **data**        | JSON      | Payload data                                              |
| **read_at**     | timestamp | When it was read                                          |
| **created_at**     | timestamp | Created at                                          |
| **updated_at**     | timestamp | Updated at                                          |




## In Practice

If you’re building a chat app, you’ll use Events + Broadcasting.

If you’re building a “system alerts” panel (like Facebook bell 🔔), you’ll use Notifications.