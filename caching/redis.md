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
- `redis-cli` this will log you in into redis

## Commands
- Setting keys
    - `SET first_name ahmed`
    - `GET first_name` .. if doesnt exist will return nil
    - `EXISTS first_name`
    - `DEL first_name`

- Expiration
    - `SETEX first_name ahmed 10` .. set value with time-to-live as well 
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

