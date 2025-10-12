# Broadcast

Laravel Broadcasting is a feature that lets your backend push real-time events to clients (browser, mobile app, etc.)

It’s not meant for backend-to-backend communication like a message bus (Kafka, RabbitMQ, NATS, etc.).

It Rely on web sockets to make a connection between server and client and leave connection open for data transmission

broadcast is only server -> client and not the opposite.. if you are working on chat application and client send message you can use post request which store in db and broadcast to the channel members..and its commonly done like that instead of client sending messages to server through websocket despite its technically possible via raw websocket usage

## When would i use broadcast?

- Chat applications (new messages appear instantly)
- Live notifications to client (new order, friend request, etc.)
- Real-time dashboards to client (stock prices, server monitoring, analytics updates)
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
        return new Channel('chatroom'); // Public channel
    }

    public function broadcastAs()
    {
        return 'new.message';
    }
}

```

then dispatch it

```php 
    event(new NewMessageEvent("Hello world!")); // send to its local listeners + broadcast driver
    NewMessageEvent::dispatch($message); //same as event method

    broadcast(new NewMessageEvent("Hello world!")); //send to only broadcast driver
```

then at client listen to event

```js
Echo.channel('chatroom')
.listen('new.message', (e) => {
    console.log("Received:", e.message);
});
```

## Channels

if channel is public channel, then there is no authorize and no need to define in channels.php

```php

public function broadcastOn()
{
    // Public channel
    return new Channel('chatroom');
}

```

if channel is private channel, then you have to define it in channels.php for the authorize method

```php
//in event
public function broadcastOn()
{
    return new PrivateChannel('orders.' . $this->order->id);
}

//in routes/channels.php
Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    return $user->id === Order::find($orderId)->user_id;
});

```

if channel is presenece channel, you also have to define it in channels.php

```php
//in event
public function broadcastOn()
{
    return new PresenceChannel('chatroom.' . $this->roomId);
}

//in routes/channels.php
Broadcast::channel('chatroom.{roomId}', function ($user, $roomId) {
    if (!$user->canJoinRoom($roomId)) {
        return false;
    }
    return ['id' => $user->id, 'name' => $user->name];
});

```


## Broadcast driver: 

Laravel can broadcast events through different systems:

- Pusher (SaaS WebSocket service)
    - Hosted SaaS service for real-time messaging.
    - You don’t manage servers; Laravel sends events to Pusher’s API, and Pusher delivers them to clients via WebSockets.
    - Simple setup, very reliable, scales automatically.
    - Downside: recurring cost and vendor lock-in.

- Ably (similar SaaS)
    - Similar to Pusher → also a managed WebSocket service.
    - Laravel integrates with it just like Pusher (via broadcaster driver).
    - Pricing may be more flexible depending on use case.

- Laravel WebSockets (self-hosted, package by BeyondCode)
    - A self-hosted replacement for Pusher.
    - You run a WebSocket server in your own infrastructure (usually with php artisan websockets:serve).
    - Clients connect directly to your server, not to Pusher/Ably.
    - Great if you want to avoid third-party costs and keep everything in-house.
    - Downside: you must scale and manage it yourself (clustering, HA, etc.).

- Redis (when combined with socket servers)
    - Redis itself is not a WebSocket service.
    - Backend apps publish events to Redis.
    - WebSocket servers (like Laravel WebSockets, Soketi, or custom Node.js) subscribes to Redis and pushes them to clients.
    - useful when you have multiple laravel instances and/or multiple websockets servers and all of them communicate through redis

## Client Side

on the frontend you usually use Laravel Echo (JS library).

Echo can connect to Pusher, Ably, or your WebSocket server.

If you don’t want Echo, 
- you have to use pusher js api to interact with pusher
- ably js
- raw websocket to interact with laravel websocket

its better though to keep it abstract, then if you want to switch driver at some point. it wont become bottleneck