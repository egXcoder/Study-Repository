# Why MVCC


## The Problem MVCC Solves

Imagine multiple users accessing a database simultaneously:
- User A wants to read a row.
- User B wants to update the same row at the same time.
- User C wants to update the same row at the same time.

If we left the above operations to race to resource. dangerous things can happen:
- User A might read garbage, if user B is partially updated row
- User B and C they can destroy the row binary data as both of them partially writting data 
- User A can read uncommitted row By User B (Dirty Read)

We need to control the concurrent access to rows

## Locking Model (Pessimistic Concurrency Control)
- Reading issue shared lock S-lock.. so other readers can read but no one can write (prevent reading garbage or uncommitted data)
- Writting issue exclusive lock X-lock
    - no readers can read the row.. prevent reading garbage or uncommitted data 
    - no writters can write on the row.. (prevent destroying the row binary data)

### Q: What is bad in locking model?

- Blocking & Reduced Concurrency
    - locking is very restrictive and hurt the performance by forcing other queries to block and wait
- Deadlocks
    - locking causes deadlocks when transactions wait each other 
    - doing many locking causes many deadlocks
- Blocking Readers
    - reads can be blocked if a writer holds an exclusive lock. 
    - This is inefficient, especially for read-heavy workloads.
- Performance Overhead
    - Every lock must be tracked in memory, which consumes resources.
    - Frequent locking and unlocking adds CPU overhead, especially for short transactions.

Tip: this locking model is used in SQL Server


## MVCC (Multi version Concurrency Control) (Optimistic Concurrency control)

avoid locking and yet concurrency control

### Core Concepts

- Row Version
    - Instead of updating a row in place, MVCC keeps multiple versions of the row.
    - Each version represents the state of the row at a specific point in time.

- Transaction IDs (TrxID / XID)
    - Every transaction gets a unique ID.
    - Each row version stores:
    - Creation transaction ID (xmin in Postgres, stored in InnoDB undo log metadata)
    - Deletion/expiry transaction ID (xmax in Postgres, or end-trx in undo log)
    - These IDs are used to determine row visibility for each transaction.

- Visibility Rules
    - When a transaction reads a row, the database decides which version is visible:

- Undo / Rollback Logs
    - Old versions of updated or deleted rows are stored in undo logs (InnoDB) or heap (Postgres).
    - Purpose:
    - Let readers see previous versions.
    - Rollback uncommitted changes if the transaction aborts.

- Commit and WAL
    - When a transaction commits:
    - Its changes are marked as committed in the Write-Ahead Log (WAL) or redo log.
    - New versions of rows become visible to other transactions.

- Vacuum / Purge
    - Over time, old row versions accumulate.
    - Databases clean them up to save disk/memory:
    - Postgres: VACUUM removes obsolete tuples.
    - MySQL InnoDB: purge undo logs for committed transactions.

### Key Benefits of MVCC
- Readers don’t block writers → high read concurrency.
- Writers don’t block readers → consistent snapshots.
- No deadlocks for read-only queries (writes can still conflict).
- Supports consistent snapshots for reporting and long-running queries.


### Q: Why Transactions shouldn't be long?
- Higher chances of conflicts:
    - Deadlocks if multiple long transactions touch overlapping rows.

- MVCC cleanup issues (Vacuum / Purge Thread)
    - Long transactions prevent cleanup because: The database must preserve the old row versions for any transaction that started before the long transaction. which reduce functionality of vacuum (postgres) / Purge Thread in mysql
    - Undo log in mysql is filled with old record versions till its committed

- Recovery is slower
    - database has to replay or roll back all uncommitted changes.

- Human/logical reasons
    - Long transactions are harder to reason about.

- In systems using locks like sql server (even MVCC systems have some locks):
    - Long transactions may hold row-level or table-level locks for a long time.
    - Other transactions waiting on those locks stall, creating bottlenecks.