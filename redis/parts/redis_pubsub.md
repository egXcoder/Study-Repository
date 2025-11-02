## Redis pub/sub (message system)

Redis provides a lightweight pub/sub functionality

Basics:
- Subscriber → Listens for messages from one or more channels.
- Publisher → Sends messages to a channel.
- if no online subscribers then message is missed. and no way to see it again

commands
- `subscribe news` subscribe to a channel called news (listen to channel news)
- `publish news "hello"` publish a message "hello" into news channel
- `PUBSUB CHANNELS` show active channels which has online listeners, once listeners disconnected channels will disappear
- `PUBSUB NUMSUB news` how many listeners to news channels
- `subscribe news jobs` subscribe to two channels in same time


### Multi-Server Coordination

When you have multiple backend servers (e.g., behind a load balancer) that must share state or events:

Example: Server 1 updates user data → publishes an event to user:updated.

Servers 2 and 3 receive that event and refresh their in-memory cache or notify connected users.

This is the most common reason to use Redis Pub/Sub in large applications.

Tip: message is not guranteed to be delivered and you are okay with it. because in redis pub/sub if listeners crashed, message is lost and you can't get it back..


### Pub/Sub between frontend and backend
By Convention frontend listening to event from your backend is done through `Websocket`
- Client is listening to Websocket Server such as pusher or laravel websockets
- when event happens in backend, app will broadcast event to the websocket server
- Websocket server will send this event to client

#### Q: is redis can be used between frontend and backend?
Not directly — Redis is not meant for frontend ↔ backend communication. Redis is a backend-to-backend tool

if you have multiple websocket servers
- Client A is listening to websocket 1
- Client B is listening to websocket 2
- Client C is listening to websocket 3
- the three websockets are listening to redis pub/sub
- When event happens in backend, app will broadcast event to redis pub/sub
- the three websockets will notify the clients