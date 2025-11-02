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

Q: What is bad in locking model?

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