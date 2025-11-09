# Replication 

Database replication means copying data automatically from one database server to another.

## Why?
- Scaling
- Failover if primary database crashed
- Backup .. Realtime backup instead of midnight backup 
- Analytics.. You can run heavy reporting jobs on replicas without blocking real traffic.


## Types:
- Asynchronous replication (most common)
    - write to primary
    - primary return success
    - replicas will have the data later with a lag of seconds or milliseconds

- Synchronous replication
    - write to primary
    - primary send the data to replicas now and return success after all went through

- Multi-primary (multi-master)
    - More than one database accepts writes.
    - Needs conflict resolution (what if two servers update same row?)

## How:
- Mysql through binary logs:
    - every write is written to the binlog on primary
    - Replica reads the binlog via a replication slave I/O thread
    - Replica replays events using the SQL thread

- Postgres through WAL:
    - primary every change go to WAL
    - Replicas read wal and replay them


## For Scaling most common approach is the asynchronous replication

Typically:
- App writes → Primary
- App reads  → Replicas

Important trade-offs:
- Replicas are `eventually consistent`. meaning .. replicas take a few to catch up, 
- The replica may take `milliseconds` (sometimes more) to catch up.
- Writes must read from the primary if you need immediate consistency.


## Q: is lag between write and read is not practical?
no, its practical and commonly used
- Replication lag is usually tiny (~1–10 ms)
- Lag becomes a problem only when your code assumes immediate consistency
- 95% of queries are "read-only and non-critical"
    - List
    - Dashboards
    - Search results
    - Analytics
    - Reports
    - autocomplete / suggestions
    - These don't care if they're 100ms behind.
- Replicas solve massive scaling problems instead of sharding which is much more complex


### Q: if replica lag become bigger, it will be nightmare though?
yes, it will be
- Users see outdated data
- Inconsistent application logic
- If the primary dies and you promote a lagging replica, you lose data (called RPO data loss).

When lag becomes bigger:
- Writes are heavy (batch jobs, large import, etc.)
- Replica is slower (weaker CPU/disk/network)
- Network suddenly slows/bursts

```text
Primary writes 10,000 rows per second
Replica can only replay 2,000 rows per second
Lag grows by 8,000 events/sec → snowball effect
```

Tip: snowball effect is when a small problem gradually gets bigger and bigger, just like a small snowball rolling downhill becomes larger as more snow sticks to it.

✅ Best practices to prevent lagging:
- Monitor replica lag (Prometheus, Grafana, Percona Exporter, pg_stat_replication)
- Keep replica hardware >= primary
- Throttle heavy writes (batch imports, large updates)
- Enable delayed replica only for backup, not read load