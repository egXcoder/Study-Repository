# Redis


## Intro
Redis (REmote DIctionary Server) is an in-memory data store. It’s commonly used as a database, cache, and message broker.


## Common Uses

- Redis Naturally live in memory, so its fast but volatile
    - Caching → Store frequently accessed data to reduce database load.
    - Session Store → Keep user sessions in memory for fast access.
    - Leaderboards & Counters → Increment values quickly.

- Redis can persist data to disk
    - Message Broker → Publish/subscribe (pub/sub) system.
    - Queue → Using lists or streams for task processing.

## Persistence

Redis has two main persistence options:

- RDB (Snapshotting) → Saves a snapshot of the database at intervals.
- AOF (Append Only File) → Logs every write command for durability.


## Why Redis is Fast

- Stored entirely in memory → no disk I/O for reads/writes (unless persistence is enabled).
- Simple data structures → fast atomic operations.
- Single-threaded → avoids locking overhead.
- Supports pipelining → multiple commands sent in bulk.

## Scaling

- Replication → Master-slave replication for read scaling.
- Sharding → Partition data across multiple Redis instances.
- Redis Cluster → Automatic sharding and high availability.


## Install
- `apt update && apt install redis-server` to install
- `systemctl enable redis-server --now` enable redis and start it
- `systemctl status redis-server` anytime if you want to see what is redis status
- redis by default runs on port 6379

## redis cli
- `redis-cli` this will log you in into redis then you can start commanding

## Commands
- Setting keys
    - `SET first_name ahmed`
    - `GET first_name` .. if doesnt exist will return nil
    - `EXISTS first_name`
    - `DEL first_name`

- Expiration
    - `SETEX first_name ahmed 10` .. set value with time-to-live as well 
    - `SET first_name "data" EX 300` ..  expires in 5 mins`
    - `EXPIRE first_name 60` .. set time-to-live for key 60
    - `TTL first_name` .. check time-to-live for key .. if forever then it will return -1

- Increment and decrement
    - `SET counter 1`
    - `INCR counter` 
    - `DECR counter` 
    - `INCRBY counter 5` 
    - `DECRBY counter 5` 

- List (useful for queue or stack you have)
    - `LPUSH tasks task1` .. add to left
    - `RPUSH tasks task1` .. add to right
    - `LPOP tasks task1` .. remove from left
    - `RPOP tasks task1` .. remove from right
    - `GET tasks` .. will give error because GET only works with strings instead you can use lrange
    - `LRANGE tasks 0 -1`..  get all items

- Sets (unique collections)
    - `SADD tags "php"` .. return 1
    - `SADD tags "redis"` .. return 1
    - `SADD tags "php"` ... return 0 as duplicate ignored
    - `SMEMBERS tags` ... get all
    - `SREM tags "php"` .. remove from set

- Hashes (like hashmap with key value pair)
    - `HSET first_user name "Ahmed" age 30` ... string first_user can be "users:1" .. its like a convention 
    - `HGET first_user name`
    - `HGETALL first_user`
    - `HEXISTS first_user name`
    - `HDEL first_user name`
    - no allowed nested hashes


- Various commands
    - `KEYS *` .. get all keys
    - `KEYS c*` .. get all keys start with c
    - `FLUSHALL` .. get rid of everything in redis


## Redis Data Structures

Redis isn’t just strings. It supports multiple types:

- String ... "name" => "Ahmed"
- List Ordered collection of strings ... "tasks" => ["task1", "task2"]
- Hash Map of fields for a key ... "user:1" => {name:"Ahmed",age:30}
- Set Unordered unique elements ... "tags" => {"php","redis","cache"}
- Sorted Set with score for ordering ... "leaderboard" => {user1:100,user2:90}


## Protecting Server Memory

Q: while putting keys and values into redis, how can i gurantee i am not going to take the server down by consuming all server memory?

Redis stores everything in RAM, so if you’re not careful, you can crash or slow down your server by filling up memory. Luckily, Redis provides built-in mechanisms to protect against this.

- within /etc/redis/redis.conf
    - `maxmemory 1GB` Set a Maximum Memory Limit 
    - `maxmemory-policy <policy>` What redis will do if exceeds set maximum memory limit

- Common policies
    - noeviction (default) .. Rejects new writes when memory is full .. good for Persistent data only
    - allkeys-lru .. Remove least-recently-used keys (any key) .. good for General-purpose caching
    - volatile-lru .. Remove least-recently-used keys (keys with ttl) .. good for Cache with explicit expirations
    - allkeys-random .. Remove random keys when full .. good for Simple cache
    - volatile-ttl .. Remove keys that will expire soonest .. good for Time-based cache

- Always Set TTL for Cache-like Data (This ensures old data auto-expires and prevents buildup.)
    - `SET user_cache:123 "data" EX 300` ...   # expires in 5 mins 


- Best Practice Setup 
    - If you're using Redis as a cache, the best configuration is:
        - maxmemory 1gb
        - maxmemory-policy allkeys-lru
    
    - If you’re using Redis for persistent critical data, then:
        - maxmemory 1gb
        - maxmemory-policy noeviction

Q: in a website, where bottle necks are queries that is not repeated, like searching.. is redis can be applied to it. or for such queries redis is difficult to optimize anything?
- Redis is excellent for repeated or predictable lookups.
- But for one-off, highly dynamic search queries, Redis is usually not the right tool for acceleration.

- tools that can help you instead
    - Full-text search ...	Elasticsearch / Meilisearch / OpenSearch
    - Simple filtering + indexing ... MySQL/Postgres with proper indexes
    - Repeated result caching ... Redis


Q: why would i use redis since i can use browser cache with cache-control header to do the job?
    - browser cache is done by user, its acceptable for low traffic site where its just a user or two query anyway
    - for high traffic sites, then redis is better to cache data in central place so that all users can benefit from  cache
    - also for browser cache, you cant invalidate easily. while redis cache its on your control
    - also for browser cache, you cant save private/sensitive data. while in redis you can

## Redis used for session data

- Redis WITHOUT persistence (pure in-memory cache) ... All data wiped on restart ... users will need to relogin after restart
- Redis WITH persistence (RDB or AOF enabled) ... Data reloaded from disk on restart .. Users remain logged in (mostly)

- So Why Do Most Modern Web Stacks Prefer Redis for Sessions?
    - A high-traffic site doing thousands of login/session lookups per second will stress MySQL.
    - Native Session Expiry (TTL) .. keys are automatically expire. rather than saving timestamp in mysql and cron job to remove them

When Not to Use Redis for Sessions?
    - If your app is low-traffic (like an admin panel or small site)
    - If you don’t want extra infrastructure
    - If you don’t want to deal with persistence setup or Redis crashes
    - Then MySQL/Postgres sessions are perfectly fine.

## Enable Redis Presistence
- RDB (Snapshotting), the best configuration is:
    - configuration within redis.conf
    ```nginx 
    save 900 1  # If at least 1 key was modified in the last 900 seconds (15 mins) → Take an RDB snapshot
    save 300 10  # If 10 or more keys changed within 5 minutes → Take a snapshot
    save 60 10000  # If 10,000+ keys changed within 1 minute → Take a snapshot quickly
    ```
    - Why Multiple Conditions?
        - It gives Redis flexibility:
            - If only a few changes happened → wait longer before saving.
            - If many changes happen fast → save sooner to avoid losing too much data.

- AOF (Append-Only File):
    - configuration within redis.conf
    ```nginx 
    appendonly yes        # Enables AOF persistence
    appendfsync everysec  # Balanced performance + durability
    ```
    - appendfsync mode
        - always: Write every command to disk immediately .. slowest but safest
        - everysec: Flush to disk every second .. Recommended
        - no : Let OS decide .. Fastest but less safe

- Best Practices
    - Cache-only (data can be lost on restart)                     .. ❌RDB   ❌AOF
    - Sessions / Authentication / Queues (Web apps)                .. ✅RDB   ✅AOF (with everysec)
    - High-Durability Data (e.g. financial counters, chat history) .. ✅ RDB  ✅AOF (always)
    - RDB and AOF are both enabled in same time as best practices for AOF = safety, RDB = fast recovery + insurance policy.


- AOF keeps appending to a file forever… will it grow infinitely?
By default, yes — the AOF file will continuously grow as Redis writes each operation to it.
But Redis does have built-in mechanisms to shrink (rewrite) it automatically, if configured properly.

- Redis uses a process called AOF Rewrite (BGREWRITEAOF).
    - This compacts the file by writing only the minimal set of commands needed to recreate the current dataset, instead of replaying every single historical write.
    - inside redis.conf
    ```nginx
    auto-aof-rewrite-percentage 100 # If the AOF file size doubles since last rewrite → Compact it
    auto-aof-rewrite-min-size 64mb # Don’t trigger a rewrite until file is at least 64MB
    ```
    - ✅ Example of AOF Lifecycle
        - Start Redis ... 1MB AOF File Size
        - After 10,000 writes ... 100 MB
        - Rewrite triggered → New compact AOF ... 5MB
        - Keeps growing again until next rewrite

- AOF Rewrite Deep
    - imagine you have used redis like this
        ```nginx
        SET count 1
        INCR count
        INCR count
        INCR count
        SET user:1 "John"
        SET user:1 "Johnny"
        ```
    - The AOF file would contain all of these commands, which might look like:
        ```nginx
        SET count 1
        INCR count
        INCR count
        INCR count
        SET user:1 "John"
        SET user:1 "Johnny"
        ```
    - After AOF Rewrite: Redis analyzes the current state of the database (not the history), and writes only what’s needed to recreate that state:
        ```nginx
        SET count 4
        SET user:1 "Johnny"
        ```

## Redis transaction

- Yes — Redis supports transactions using MULTI, EXEC with atomic execution.
- However, it's NOT like SQL — there's no rollback on failure.
- All commands inside MULTI ... EXEC run sequentially and atomically — no other client can run commands in between.

```nginx
MULTI
INCR account:balance
INCR account:transactions
EXEC #Execute all queued commands atomically
```

- It clears all queued commands and exits the transaction mode.

```nginx
MULTI
INCR account:balance
INCR account:transactions
DISCARD # Cancel Transaction
```


## Redis pub/sub (message system)

Redis provides a built-in Publish/Subscribe (Pub/Sub) messaging system that lets one or more clients publish messages to channels, while other clients subscribe to those channels to receive them in real-time.

- basics
    - Subscriber → Listens for messages from one or more channels.
    - Publisher → Sends messages to a channel.
    - Redis delivers the message instantly to all active subscribers.

- commands
    - `subscribe news` subscribe to a channel called news (listen to channel news)
    - `subscribe news jobs` subscribe to two channels in same time
    - `publish news "hello"` publish a message "hello" into news channel
    - `PUBSUB CHANNELS` show active channels which has online listeners, once listeners disconnected channels will disappear
    - `PUBSUB NUMSUB news` how many listeners to news channels

- steps
    - once a message is published to channel 
    - all online subscribers will get the message instantly
    - if no online subscribers then message is missed. and there is no way to subscriber once back online to see the missed messages
    - the published doesnt gurantee message was received. so there is no way to replay sending
    - publisher just send the message and receivers listening receive them (simple as that)  
    - if you weren’t listening at the time, you miss the message. Use it when low-latency broadcast > reliability.
    

- Realworld applications
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

- Websockets vs Redis
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

    

## Redis Databases

In Redis, "databases" are not like traditional SQL databases. They’re more like logical namespaces or partitions within a single Redis instance.

- A Redis instance can contain multiple numbered databases, e.g., DB 0, DB 1, DB 2, … (default is usually 0).
- They don’t have names, only integer indexes.
- They share the same memory and configuration, but data stored in one DB is isolated from others.
- select a database by `SELECT 0   # or 1, 2, etc.`

Q: Are Redis Databases Commonly Used?

Not really in production. While Redis supports multiple databases, most users stick to a single database (DB 0) and instead separate data by key prefixes, like:


✅ Best Practice
Use a single Redis database and separate data using key prefixes.



## Redis Clusters

A Redis Cluster is a group of Redis servers (called nodes) that:

 - Sharding (Data Distribution) : Automatically splits keys across multiple nodes, so you’re not limited by the memory of a single machine.

 - High Availability: Each node has a replica (slave) — if a node fails, another takes over.

 - Horizontal Scalability: You can add or remove nodes without downtime.

 - No Single Point of Failure: Unlike standalone Redis, a cluster survives node failures.

Sharding: 

- Redis Cluster divides the key space into 16,384 hash slots.
- Each master node is responsible for a range of hash slots.
- When you set or get a key, Redis hashes the key and forwards it to the correct node.

Key: "user:123" -> Hash -> Slot 8123 -> Node 2
Key: "order:567" -> Hash -> Slot 12000 -> Node 4


TODO:: this redis cluster will need practice at some point after we practice redis itself in a project


## Questions

Q: Can redis have two blocks where one is used for caching only and i dont want to bother to persist it and one is for session data where i need persistence?
- You have to create two redis instance. each with its own configuration. each listen to a different port 


## Redis Stream

i have explained redis stream in message queue section