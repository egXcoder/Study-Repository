## Redis pub/sub (message system)

Redis provides a built-in Publish/Subscribe (Pub/Sub) messaging system that lets one or more clients publish messages to channels, while other clients subscribe to those channels to receive them in real-time.

basics
- Publisher → Sends messages to a channel.
- Subscriber → Listens for messages from one or more channels.
- if no online subscribers then message is missed. and no way to see it again

commands
- `subscribe news` subscribe to a channel called news (listen to channel news)
- `publish news "hello"` publish a message "hello" into news channel
- `PUBSUB CHANNELS` show active channels which has online listeners, once listeners disconnected channels will disappear
- `PUBSUB NUMSUB news` how many listeners to news channels
- `subscribe news jobs` subscribe to two channels in same time


Realworld applications
- all of below is when you have multiple servers, but if you have one backend server then websocket is enough
- Real-time notifications .. Push alerts to users (new message, system alert, stock price change)
- Chat applications .. One user publishes a message, all users in the room get it instantly
- Live dashboards / analytics: Backend publishes metrics → dashboard updates instantly
- not perfered for Microservices Event Broadcasting, since messages can get lost. so it perfer message queues

Redis Pub/Sub is very simple compared to more advanced messaging systems like Kafka or RabbitMQ. But its simplicity is exactly why it's widely used in real-world applications.

- Redis Pub/Sub excels in lightweight, real-time message broadcasting where:
    - Speed is critical
    - Durability is not required (if a subscriber misses a message, it's okay)
    - System components need loose coupling

Websockets vs Redis
- Websockets
    - Server ↔ Client (frontend real-time communication)
    - Between browser/mobile & backend
    - Push updates to end users over the internet
    - Connection-based

- Redis:
    - Server ↔ Server (backend coordination)
    - Inside backend infrastructure
    - Broadcast events between multiple servers or processes
    - No persistence (fire-and-forget)

- When Do you Use Only Websocket?
    - You have only 1 backend instance and you’re only doing client-to-server real-time updates (then websocket is enough)

- When Do you Use Only Redis?
    - when processes or servers want to communicate in pub/sub way

- When Do You Use Both Together?
    - Example: Let’s say you run a chat app with 3 load-balanced backend servers:

        User A is connected to Server 1 via WebSocket.
        User B is connected to Server 2.
        User C is connected to Server 3.

        Now A sends "Hi" to the chat room.

        If your server uses only WebSockets, Server 1 has no idea how to tell Server 2 and 3.
        
        But if Server 1 publishes to Redis channel "chat:room1" and Servers 2 & 3 subscribe, all WebSocket servers redispatch the message to their connected clients. (so its mainly redis used from the websocket server)


