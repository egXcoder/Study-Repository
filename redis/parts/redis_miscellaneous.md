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


## Redis transaction

Yes — Redis supports transactions using MULTI, EXEC with atomic execution. However, it's NOT like SQL — there's no rollback on failure. All commands inside MULTI ... EXEC run sequentially and atomically — no other client can run commands in between.

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

## Redis Databases

A Redis instance can contain multiple numbered databases, e.g., DB 0, DB 1, DB 2, … (default is usually 0).

They don’t have names, only integer indexes.

They share the same memory and configuration, but data stored in one DB is isolated from others.

select a database by `SELECT 0   # or 1, 2, etc.`

In Redis, "databases" are not like traditional SQL databases. They’re more like logical namespaces or partitions within a single Redis instance.


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

Q: Can redis have two blocks where one is used for caching only (in memory) and one is for session data (persistance)?

You have to create two redis instance. each with its own configuration. each listen to a different port 