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