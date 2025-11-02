## Protecting Server Memory

### Q: while putting keys and values into redis, how can i gurantee i am not going to take the server down by consuming all server memory?

Redis stores everything in RAM, so if you’re not careful, you can crash or slow down your server by filling up memory. Luckily, Redis provides built-in mechanisms to protect against this.

#### [1] Always Set TTL
Always Set TTL .. `SET user_cache:123 "data" EX 300` ...   # expires in 5 mins 

#### [2] Configure Redis 
```redis
maxmemory 1GB #Set a Maximum Memory Limit 
maxmemory-policy <policy> #What redis will do if exceeds set maximum memory limit
```

Common policies
    - noeviction (default) .. Rejects new writes when memory is full .. good for Persistent data only
    - allkeys-lru .. Remove least-recently-used keys (any key) .. good for General-purpose caching
    - volatile-lru .. Remove least-recently-used keys (keys with ttl) .. good for Cache with explicit expirations
    - allkeys-random .. Remove random keys when full .. good for Simple cache
    - volatile-ttl .. Remove keys that will expire soonest .. good for Time-based cache

Best Practice Setup 
- If you're using Redis as a cache, the best configuration is:
    - maxmemory 1gb
    - maxmemory-policy allkeys-lru

- If you’re using Redis for persistent critical data, then:
    - maxmemory 1gb
    - maxmemory-policy noeviction

### Q: if bottlenecks are queries that is not repeated, like searching.. is redis can be applied to it?
- Redis is excellent for repeated or predictable lookups. But for one-off, highly dynamic search queries, Redis is usually not the right tool for acceleration.

- tools that can help you instead
    - Full-text search ...	Elasticsearch / Meilisearch / OpenSearch
    - Simple filtering + indexing ... MySQL/Postgres with proper indexes
    - Repeated result caching ... Redis


### Q: why would i use redis since i can use browser cache with cache-control header to do the job?
- browser cache is per user, its acceptable for low traffic site where its just a user or two query anyway
- for high traffic sites, then redis is better to cache data in central place so that all users can benefit from cache
- also for browser cache, you can't invalidate easily. while redis cache its on your control
- also for browser cache, you can't save private/sensitive data. while in redis you can