# Events

When your code do the below 

```php
// step 1
// step 2 
// step 3
```

[1] one way to optimize it from strucural point of view (to separate step 3 into event/listner)
    these listners are php classes so its going to run synchronously 
```php
//step 1
//step 2
//dispatch event (one of the listeners will do step 3)
```

[2] another way to optimize it, if step 3 takes time and it can be delegated to be done later
    listeners are queuable then they can be processed later
```php
//step 1
//step 2
//dispatch event then his listners are queued to be processed separately and this an be speed things up greatly
```

[3] if step 3 can be delegated to another server completely. 

[pub/sub basic]
- Publisher sends a message
- Subscribers receive it
- They are decoupled (publisher doesn’t know who’s listening or what listening will do)

In production systems, people don’t usually do basic pub/sub directly (like Redis SUBSCRIBE), because it’s:
- messages are lost if subscriber is offline
- no acknowledgment, retries, or persistence

Message Brokers are the real engines behind pub/sub (Redis Streams, Kafka, RabbitMQ)

When raw pub/sub is used .. rare .. (redis pub/sub):
- basic communication between backend servers (that if one of listener fails then tolerate it like chat room)
- Realtime notifications (e.g. broadcasting events in Laravel/Echo)
- Websockets & chat messages
- Dashboard updates