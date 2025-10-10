# Broadcast

Laravel Broadcasting is a feature that lets your backend push real-time events to clients (browser, mobile app, etc.)

It’s not meant for backend-to-backend communication like a message bus (Kafka, RabbitMQ, NATS, etc.).

It builds on top of:

- Events in Laravel (things happening inside your app)
- WebSockets / PubSub (to deliver them instantly to clients)

So instead of clients polling the server (“do I have updates yet?”), Laravel pushes updates in real-time.


## Common use cases

- Chat applications (new messages appear instantly)
- Live notifications (new order, friend request, etc.)
- Real-time dashboards (stock prices, server monitoring, analytics updates)
- Collaborative apps (document editing, task boards, Kanban, etc.)


## How to broadcast

Create event and implement ShouldBroadcast .. please notice event will notify its local listeners + broadcast driver

```php

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewMessageEvent implements ShouldBroadcast {
    public $message;

    public function __construct($message) {
        $this->message = $message;
    }

    public function broadcastOn() {
        return ['chat-room']; // channel name
    }
}

```


## Broadcast driver: 

Laravel can broadcast events through different systems:

- Pusher (SaaS WebSocket service)
- Ably (similar SaaS)
- Laravel WebSockets (self-hosted, package by BeyondCode)
- Redis (when combined with socket servers)


## Client-side

Clients subscribe to channels using Laravel Echo (a JS library).

When the server broadcasts, subscribed clients receive it immediately.

Echo.channel('chat-room')
    .listen('NewMessageEvent', (e) => {
        console.log("New message:", e.message);
    });


## Channels

Laravel supports channels for grouping connections:

- Public channels → Anyone can subscribe (e.g. live score updates).
- Private channels → Require authentication (user must be logged in).
- Presence channels → Like private channels, but also track who is online.

Example in routes/channels.php:

```php
Broadcast::channel('chat-room', function ($user) {
    return $user != null; // allow only logged-in users
});
```