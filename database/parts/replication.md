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
- App reads  → Replica(s)

Important trade-offs:
- Replicas are eventually consistent. meaning .. After a write to primary, The replica may take milliseconds (sometimes more) to catch up.
- Writes must read from the primary if you need immediate consistency.


## Q: i feel this lag thing between write and read is not practical?
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


### Q: if replica lag become bigger, it will be nightmare though? //TODO:
