# ACID


## What is a Transaction?

A transaction is a collection of one or more SQL statements treated as one logical unit of work. It begins with BEGIN and ends with either:
- COMMIT → persist all changes permanently to disk.
- ROLLBACK → undo all changes made during the transaction.

Tip: you dont have to explicitly say rollback, crashing before commit will rollback automatically

## Atomicity = “All or nothing.”

Even if 100 queries in a transaction succeed, if one fails → rollback everything. if database went down prior to commit a transaction. all successful queries should rollback


## Consistency

### Consistency in Data (User Level Fault)
- If invoices.no_of_lines = 5, there must be 5 rows in lines table [Consistent]
- If invoices.no_of_lines = 5, and there are 3 rows in lines table, [that’s an inconsistent state]
- If invoice doesnt exist, and there are some lines referencing it,[that’s an inconsistent state]

### Consistency in Read (System-Level Consistency)

If a transaction commits a change, then all subsequent reads should reflect that change

You update a user’s balance from $100 to $50 on the primary database. If a replica still returns $100 for some time because replication is delayed, that’s read inconsistency.

Types:
- Strong consistency: Reads always see the latest committed value (often via synchronous replication).
- Eventual consistency: Reads may see stale data temporarily, but replicas will eventually converge to the same state.

Achieving strong consistency usually means slower writes or reads, because replication must synchronize before acknowledging success. Eventual consistency gives you speed and scalability but allows short-term inconsistency.

Tip: whenever you add a caching layer like redis in memory, you will become inconsistent till you update the cache

Tip: whenever you have the data in two places, you will become inconsistent till all database nodes take the updated data

Q: as a software engineer, can you tolerate eventual consistency?
- if not critical: such as how many likes this post have, it doesnt matter if i see 1000 likes or 1005 likes. its okay to be the correct value in few seconds so eventual consistent is okay here.
- if critical: such as financial value like i have done a transaction with 1k usd, when i do a select again its better be there. i can't tolerate eventual consistentcy.. it has to be consistent now


## Isolation:

Full Isolation means that concurrent transactions behave as if they were executed sequentially. Full Isolation though is slow, so we have to do trade-off and reduce isolation level restrictions for better performance


### Isolation Levels:
- Read Uncommitted: Sees all data, even uncommitted

- Read Committed: 
    - Can only see committed data
    - Prevents Dirty Reads only.
    - Default for many DBs (POSTGRES, SQLSERVER, Oracle)

- Repeatable Read:
    - Once a row is read, it can’t change during the same transaction.
    - Prevents Dirty and Non-repeatable Reads
    - Default for Mysql

- Snapshot Isolation:
    - Transaction sees a consistent snapshot of the DB as of its start time.
    - Prevents Dirty, Non-repeatable, Phantom

- Serializable
    - Transactions execute sequentially
    - Prevents all 4 (Dirty, Non-repeatable, Phantom, Lost Update)


### Read Phenomena
- Dirty Read: T1 reads a price updated by T2 before T2 commits — if T2 rolls back, T1 read invalid data.
- Non-repeatable Read: A transaction re-reads the same row and finds it changed by another committed transaction.
- Phantom Read: A transaction re-executes a range query and finds new rows inserted by another committed transaction
- Lost Update: T1 adds +10, T2 adds +5, both start from 10 → final value 15 instead of 25.


### Implementation Approaches

| Approach                            | Mechanism                               | Used By                                 | Notes                                              |
| ----------------------------------- | --------------------------------------- | --------------------------------------- | -------------------------------------------------- |
| **Pessimistic Concurrency Control** | Locks data (rows, pages, or tables).    | Traditional RDBMS (Mysql,SQL Server, Oracle). | Prevents conflicts by blocking other transactions. |
| **Optimistic Concurrency Control**  | Doesn’t lock; validates at commit time. | Many NoSQL DBs, PostgreSQL’s MVCC.      | Fast, but may roll back on conflict.               |


### MVCC (Multi-Version Concurrency Control)
- Used by PostgreSQL and others to avoid locking readers.
- Each transaction sees a snapshot version of the data as of its start time.
- Old versions are stored until they’re no longer visible to any transaction.


## Durability

If the database says “✅ committed,” Then even if you pull the plug immediately afterward, The committed data must still be there when the system restarts

### The Challenge:

Persisting data permanently means writing to non-volatile storage — disks (HDDs, SSDs, or similar). But disk I/O is slow compared to memory.

So, databases must balance:
- Speed (writing quickly in memory)
- Safety (writing persistently to disk)

That’s the core tradeoff of durability.

### Techniques for Durability

- Write-Ahead Log (WAL)
    Most relational databases (PostgreSQL, MySQL InnoDB, SQL Server, etc.) use this. Instead of rewriting entire tables, the DB logs only the changes (deltas) to a special WAL file. When Transaction commit The WAL is immediately flushed to disk before db confirming the commit. If the database crashes, it can replay the WAL entries to restore the latest consistent state.

    Advantages:
    - Fast (sequential I/O)
    - Reliable recovery after crash

- Snapshots / Checkpointing
    Some systems (like Redis) write all in-memory data to disk periodically. The database keeps data in memory for speed. In the background, it takes snapshots (e.g., every few seconds/minutes) and writes them to disk. If the system crashes before the next snapshot, you lose only the changes since the last one.

    Tradeoff:
    - ✔️ Fast writes
    - ❌ Potential data loss if crash occurs before next snapshot

- Append-Only File (AOF)

    Same Idea as WAL but used by Redis. Instead of overwriting data, every operation is appended to a log file. On restart, Redis replays the AOF to rebuild the in-memory dataset.

    You can configure it to:
    - Write to disk every operation (always)
    - Every second (everysec)
    - Or let the OS decide (no)
    This gives the user a choice between strong durability and performance.

### The OS Cache Problem

When a database “writes” to disk, it actually asks the operating system to perform the write. But many OSes cache writes in memory before flushing to disk — to optimize I/O.

The OS might say: “✅ Write successful” even though the data is still in memory (not yet physically written).

If a crash happens before the flush, data is lost. → Database becomes non-durable.

Fix By Datatabases:
Use the fsync() (or fdatasync()) system call, which forces the OS to flush data all the way to disk before confirming the commit.

Tradeoff:
- ✔️ Guaranteed durability
- ❌ Slower commits (because you’re waiting for disk I/O)


## Summary

| Property        | Meaning                                    |
| --------------- | ------------------------------------------ |
| **Atomicity**   | All-or-nothing                             |
| **Consistency** | Data always in good shape                  |
| **Isolation**   | Transactions don’t interfer                |
| **Durability**  | if db says committed, its better be there even if db crashed at this moment          |
