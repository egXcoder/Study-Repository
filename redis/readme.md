# Redis

## Intro
Redis (REmote DIctionary Server) is an in-memory data store. It’s commonly used as a database, cache, and message broker.

## Common Uses
- Caching → Store frequently accessed data to reduce database load.
- Session Store → Keep user sessions in memory for fast access.
- Message Broker → Publish/subscribe (pub/sub) system.
- Queue → Using lists or streams for task processing.

## Why Redis is Fast
- Stored entirely in memory → no disk I/O for reads/writes (unless persistence is enabled).
- Single-threaded → avoids locking overhead.
- Simple data structures → fast atomic operations.

## Install
- `apt update && apt install redis-server` to install
- `systemctl enable redis-server --now` enable redis and start it
- `systemctl status redis-server` anytime if you want to see what is redis status
- redis by default runs on port 6379

## redis cli
- `redis-cli` this will log you in into redis then you can start commanding

## Links
- [Redis Data Structure](./parts/redis_data_structure.md)
- [Redis Persistence](./parts/redis_persistence.md)
- [Redis Protecting Server Memory](./parts/redis_protecting_server_memory.md)
- [Redis Pubsub](./parts/redis_pubsub.md)
- [Redis Stream](./parts/redis_stream.md)
- [Redis Miscellaneous](./parts/redis_miscellaneous.md)