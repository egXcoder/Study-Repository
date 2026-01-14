# MVCC


### The Problem MVCC Solves

to have the database allows for concurrent data queries, there need to be some sort of organzing these queries

Imaging a concurrent world without any locking mechanism

- User A wants to read a row.
- User B wants to update the same row at the same time.
- User C wants to update the same row at the same time.

If we left the above operations to race to resource. dangerous things can happen:
- User A might read garbage, if user B is partially updated row
- User B and C they can destroy the row binary data as both of them partially writting data 
- User A can read uncommitted row By User B (Dirty Read)

Because of the above, we need a model to organize concurrency and we have two ways
- Locking Model (Pessimistic Concurrency Control)
    - read and writes lock rows with shared lock and exclusive lock like in sqlserver

- MVCC (Multi version Concurrency Control) (Optimistic Concurrency control) 
    - Read and writes dont lock each others
    - MVCC doesnt mean 0 lock but it means minimum locking
    - only lock exists here is when updating a row no other updates can be done on same row till first update finish or you can issue a (SH/EX) lock explicitly in the query

---

### Locking Model (Pessimistic Concurrency Control)
- Reading issue shared lock S-lock.. so other readers can read but no one can write (prevent reading garbage or uncommitted data)
- Writting issue exclusive lock X-lock
    - no readers can read the row.. prevent reading garbage or uncommitted data 
    - no writters can write on the row.. (prevent destroying the row binary data)

### Q: What is bad in locking model?
- locking and blocking hurt the performance by forcing other queries to block and wait
- doing many locking increases possibilities of deadlocks
- reads can be blocked if a writer holds an exclusive lock. This is inefficient, especially for read-heavy workloads.
- Every lock must be tracked in memory, which consumes resources.

Tip: this locking model is used in SQL Server

---

### MVCC (Multi version Concurrency Control) (Optimistic Concurrency control)

avoid locking and yet concurrency control using row versions. 

### Core Concepts
- every write add a new row version while keeping old version to be read by previous transactions
- Each version represents the state of the row at a specific point in time.
- every transaction have a readview of the active transactions during the transaction and this readview will be used to decide if row is visible or not

### Key Benefits of MVCC
- Readers don’t block writers → high read concurrency.
- Writers don’t block readers → consistent snapshots.
- No deadlocks for read-only queries (writes can still conflict).
- Supports consistent snapshots for reporting and long-running queries.

### Q: what are the locks done in mvcc model?
- [1] Update a row do Exclusive Lock which prevent other updates/deletes but reading is fine
- [2] Explicit Shared Lock `SELECT ... FOR SHARE` .. prevent others writting to row but can read with no issue
- [3] Explicit Exclusive Lock `SELECT ... FOR Update` .. prevent any read or write to row till it finish

Tip: typical exclusive lock on this model is to prevent two updating same row in same time.. but reading is okay

Tip: select for update .. can hurt the performance greatly. its transaction must be very short. because as long as transaction still open. if we are doing select * it will block. it feels almost like table is locked.


| Database                           | MVCC Implementation                                         |
| ---------------------------------- | ----------------------------------------------------------- |
| **PostgreSQL**                     | Native MVCC                                                 |
| **MySQL (InnoDB)**                 | MVCC inside the InnoDB (undo logs)                          |
| **Oracle**                         | MVCC since the 1980s (very mature)                          |
| **Microsoft SQL Server**           | Optional (via READ_COMMITTED_SNAPSHOT / SNAPSHOT isolation) |