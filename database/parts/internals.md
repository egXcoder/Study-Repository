# Internals

## Row:
Each row is assigned a row identifier (row ID) 
- either user-defined (like a primary key in MySQL) 
- or system-defined (like a tuple ID in PostgreSQL).

## Pages:

The smallest unit of I/O in most databases. A page is a fixed-size chunk of data read from disk (not a single row). Example sizes:
- PostgreSQL: 8 KB
- MySQL (InnoDB): 16 KB

So when a database needs to read a single row, it actually loads the entire page containing that row into memory.


## I/O (Input/Output)

An I/O is one read/write operation between disk and memory. Disk I/O is expensive, so databases aim to: Minimize how many pages they read and Reuse pages already in memory (via caching). A single I/O usually fetches multiple rows at once (the whole page).

## Clustered Table
- table’s data itself is physically stored in the order of the index. (usually the primary key). 
- MySQL, SQL Server

## The Heap (Non-Clustered)

- data stored un-ordered. A full table scan means scanning all heap pages — which is slow. Hence the need for indexes to avoid scanning the entire heap. 
- PostgreSQL

## Indexes

An index is a separate data structure (stored on disk) that helps locate rows in the heap more efficiently. Most relational databases use B-trees as the underlying structure. Each index entry contains:
- The indexed value (e.g., employee_id = 40)
- A pointer (page + row ID) to the actual data in the heap

When you search for a record: 
- The database looks up the value in the index (I/O #1).
- It finds the page and row ID in the heap.
- It fetches that page from the heap (I/O #2) and extracts the row.
This is index lookup + heap lookup — faster than scanning the whole table.


## POSTGRES 

## MVCC (Multi Version Concurrent Control)


### Core idea is to prevent locking 

In old databases: To ensure data consistency, a transaction would lock rows (Pessmistic Concurrency Control)
- Readers issue a shared lock on rows
- Writters blocked till shared lock is released
- ...............
- Writers issue an exclusive lock on rows
- Readers can't read till lock is released

In MVCC — “readers don’t block writers, writers don’t block readers” (Optimistic Concurrency control)
- Readers dont issue locks
- Writters can write while other readers reading
- ...............
- Writters issue an exclusive lock but only for other updates
- Readers can read the old version till this one is committed


it has become the prevailing approach in the design of modern relational database systems. such as PostgreSQL, Oracle, and MySQL (InnoDB)

| Database                           | MVCC Implementation                                         |
| ---------------------------------- | ----------------------------------------------------------- |
| **PostgreSQL**                     | Native MVCC built into the core (xmin/xmax)                 |
| **MySQL (InnoDB)**                 | MVCC inside the InnoDB storage engine (undo logs)           |
| **Oracle**                         | MVCC via undo segments since the 1980s (very mature)        |
| **Microsoft SQL Server**           | Optional (via READ_COMMITTED_SNAPSHOT / SNAPSHOT isolation) |


### Tuples

in mvcc, a row may have multiple tuple versions alive at the same time.

A tuple = a row version (not just a row). 

Each tuple stores:
- The actual column data.
- Hidden system columns for MVCC and addressing:
    - ctid: Current Tuple ID (page_number, tuple_index) such as ctid (3, 17) page number = 3 , Tuple slot number = 17 within page
    - xmin: transaction ID that created it
    - xmax: transaction ID that deleted/replaced it

Tip: The word tuple comes from mathematics — specifically, from the idea of an ordered list of values.
- Table: Likes(user_id, post_id, count)
- Tuples:
    - (1, 5, 3)
    - (2, 8, 2)
    - (3, 5, 1)


### Transaction Ids
- Transaction IDs are sequential 32-bit integers.. 
- XID start from 3 -> 4 billion
- Each new transaction that modifies data gets a Transaction ID (XID) = previous + 1
- After roughly 4 billion transactions, PostgreSQL’s transaction ID counter wraps around to 3 again.

Why Wrap around can be dangerous?
- typically when we run a query like `select * from likes;` it is effectivly querying `select * from likes where xmin<=101` where 101 is the latest transaction id so far
- suppose the transaction id is 4 billion, all is good `select * from likes where xmin<=4b`.  
- when wrap around happens and transaction id is back to be 3.. then `select * from likes where xmin<=3` is going to ignore alot of records 
- causing data corruption or visibility chaos. so there has to be a fix for it 

Freezing old tuples (Fix)
- When a tuple’s xmin refers to a transaction that committed long ago (and thus can be considered definitely visible to everyone), Postgres replaces its xmin with a special value called FrozenXID = 2.
- Frozen XIDs are treated as infinitely old, meaning they are visible to all transactions forever.

This happens during:
- Postgres runs autovacuum periodically, and one of its tasks is to freeze old tuples
- `VACUUM` command manually
- Or explicitly via `VACUUM FREEZE`
So freezing old tuples is essential maintenance for long-lived databases.

### CLOG (Commit Log)

PostgreSQL keeps a separate data structure called the commit log. stored in $PGDATA/pg_xact/

Each transaction ID (XID) has an entry there corresponds to 2 bits in pg_xact. Because of this, the commit log is tiny:

| Bits | Meaning                                            |
| ---- | -------------------------------------------------- |
| 00   | Transaction in progress                            |
| 01   | Committed                                          |
| 10   | Aborted                                            |
| 11   | Subcommitted (used internally for subtransactions) |

How It’s Used During Visibility Checks: when you do a select query
- Read the tuple’s xmin and xmax.
- Look up their commit status in pg_xact.
- Use that status to dynamically decide if the tuple is visible.

| Tuple | xmin | xmax | pg_xact[xmin] | pg_xact[xmax] | Visible?              |
| ----- | ---- | ---- | ------------- | ------------- | --------------------- |
| v1    | 100  | NULL | committed     | —             | ✅ visible             |
| v2    | 101  | NULL | in progress   | —             | ❌ invisible           |
| v3    | 102  | NULL | aborted       | —             | ❌ invisible           |
| v4    | 100  | 103  | committed     | committed     | ❌ deleted (invisible) |


Cleaning Up Old Commit Log Data:
- as part of vaccum freezing cleaning happens when transaction ids become old. 
- committed tuples which are very old will take xmin = 2 and xid entries will be removed from the commit log


### WAL (Write Ahead Log) in postgres / Redo Log in mysql

#### Challenge
- Even small changes in the database often modify multiple pages scattered across storage.
- Flushing each changed page immediately to disk causes high I/O overhead, which can hurt performance and wear out the disk.
- One might think: “Why not group multiple changes and flush them together?” — this would reduce I/O.
- However, if the database crashes while these grouped changes are still in memory, data could be lost.
- therefore, there is no escape from flushing into disk but it has to be very quick i/o and to be in one place
- Therefore, flushing changes to disk cannot be avoided — it must happen safely, quickly, and in a sequential, centralized place.

#### Solution
- The WAL is an append-only log that records database changes sequentially.
- It stores minimal information — typically the deltas (what has changed) since the last checkpoint.
- WAL tracks committed changes: when a transaction commits, an entry is added to the WAL marking it as committed.
- Periodically, the database applies WAL changes in batches to the main data files, improving I/O efficiency.
- In case of a crash, the database can replay the WAL to redo committed changes, restoring the database to a consistent state.


#### Q: what do you mean by checkpoint and how its set?

a checkpoint is the point up to which all changes have been safely written to the data files.

#### Q: when WAL is cleaned because surely its not going to grow forever?

When Wal entries flushed to disk and a checkpoint is set .. these WAL entries can be truncated or recycled.

#### Q: how WAL enries are truncated?
- WAL is split into segments (or log files).
- Database writes WAL entries sequentially into the current segment.
- When a segment fills up, the database moves to the next segment to continue writing.
- All changes in that segment have been persisted to the main data files (i.e., a checkpoint has been performed).
- Once this is true, the segment can be marked as reusable for future WAL entries.


#### Q: where uncommitted data is stored?
Uncommitted changes are primarily stored in memory, specifically in the buffer pool in InnoDB.

Tip: Even though uncommitted changes originated in memory, they can reach disk under normal operation.

#### Q: is WAL tracks uncommitted data?
WAL Tracks Both Uncommitted and Committed Changes
- in InnoDB (or PostgreSQL), WAL entries are written, even if the transaction hasn’t committed yet.
- on commit, a commit record is written into WAL. This marks all previous WAL entries for that transaction as committed.
- During crash recovery: WAL is scanned sequentially.
    - Entries for committed transactions are reapplied to the data files.
    - Entries for uncommitted transactions are ignored / rolled back.

#### Q: Why uncommitted data can reach disk?
- Dirty pages flushed by background threads or checkpoints
    - InnoDB periodically flushes dirty pages to reduce memory pressure and keep I/O smooth.
    - These dirty pages may contain changes from uncommitted transactions.
- Memory eviction
    - If the buffer pool is full, the database must evict pages.
    - Any dirty page must be written to disk, even if some transactions are still uncommitted.
- Performance batching
    - Writing pages to disk in batches is faster than writing every page immediately.
    - This may include uncommitted changes.


### What Happens on Create?

T1 create a record .. T1 xid = 101..

Row:
- ctid(3,17)
- xmin = 101 .. created by 
- xmax = null
- row data here

lets suppose T1 didnt commit yet.. 
- T1 run a query `select * from likes`
    - query is converted to `select * from likes where xmin<=101`
    - it will find one tuple, and do visibility check .. xmin = 101 which is my transaction id .. so show it

- T2 run a query `select * from likes`
    - query is converted to `select * from likes where xmin<=102`
    - it will find one tuple, and visibility check .. xmin = 101 .. hey commit log what is the status of xid=101? still in progress!! okay .. don't show it

lets support T1 commit now..
- T2 run a query `select * from likes`
    - query is converted to `select * from likes where xmin<=102`
    - it will find the new tuple, and visibility check .. xmin = 101 .. hey commit log what is the status of xid=101? committed!! okay .. show it


### What Happens on UPDATE?
- It doesn’t modify the row in place. just marks the old one as expired (xmax)
- It creates a new tuple (new CTID)
- it add entry in all indexes and mark old index as dead till vacuum later on which mark it as reusable

T2 update record .. T2 xid = 102.. even before commit it will write to heap the below

old Row:
- ctid (3,17)
- xmin = 101
- xmax = 102
- row data here

New Row:
- ctid (8,2)
- xmin = 102
- xmax = null
- new row data

lets suppose T2 not committed yet
- T2 run a query `select * from likes`
    - query is converted to `select * from likes where xmin<=102`
    - it will find two tuples, and visibility check .. get me the latest tuple with xmin value .. xmin=102 thats me.. then show it

- T3 run a query `select * from likes`
    - query is converted to `select * from likes where xmin<=103`
    - it will find two tuples, and visibility check .. get me the latest tuple with xmin value .. xmin=102 .. hey commit log, what is the status with xid = 102? still in progress!! okay read the other one .. xmin=101 committed!! then show this tuple

lets suppose T2 committed
- T3 run a query `select * from likes`
    - query is converted to `select * from likes where xmin<=103`
    - it will find two tuples, and visibility check .. get me the latest tuple with xmin value .. xmin=102 .. hey commit log, what is the status with xid = 102? committed then show this tuple

then vacuum run .. 
- i can see there is old tuple with xmax = 102, hey commit log, what is status of xid=102. committed!! okay .. lets delete this tuple then from heap
- i can see there is old tuple with xmax = 100, hey commit log, what is status of xid=100. aborted!! okay .. lets wipe this xmax then from this tuple to back to null


### How primary key operates in postgres?

In PostgreSQL, all indexes — even the primary key — are non-clustered by design.

id=1 → ctid(page_id=42, tuple_id=7)
id=2 → ctid(page_id=42, tuple_id=8)
id=3 → ctid(page_id=42, tuple_id=9)

so when you lookup for primary key, it do two lookups. first on this structure then on heap to get the data

### How Non-Clustered Indexes Use the TID?

When you create an index in PostgreSQL (e.g., on email), it create a structure like below

('john@example.com') → ctid(page_id=42, tuple_id=7)

so when you lookup for email, first it will find possible keys and gather their ctid. then go and fetch them from heap

Tip:if you have many indexes on a table, and you are doing updates even for not indexed column. you are going to touch all indexes which is bad and unncessary unless you enable (heap only update)

Tip: because updating records is essentially adding data, postgres tends to always grow file size. vacuum try to oppose that by keeping size reusable as best as it can



### Pros and Cons of Postgres or MMVC (multi version concurrent control) Model

Pros:
- Readers Don’t Block Writers (and vice versa)
    - PostgreSQL is famous for this — SELECT queries don’t block INSERT/UPDATE/DELETE.

- Fewer Locks & Deadlocks
    - Because readers don’t take locks, lock contention is much lower. 
    - There are fewer cases of “deadlock detected” errors compared to traditional locking models.

Cons:
- Storage Bloat: 
    - Every update creates a new version of a row, which increase table size rapidly.

- Vacuum / Garbage Collection Overhead: 
    - adds background I/O load
    - can fragment data and slow down sequential scans
    - needs careful tuning

- If No Vacuum:
    - tables become bloated
    - queries get slower
    - disk usage spikes
    - transaction ID wraparound risks appear in PostgreSQL

- Index Maintenance Cost:
    - When a row version changes, all indexes pointing to it may need updates too, increasing write amplification.

- Visibility Checks Add Minor CPU Overhead:
    - Every read must check: whether a row’s xmin (created by tx) and xmax (deleted by tx) are visible This adds a little logic to each read. Usually minor — but measurable at scale.


#### Q: is postgres work with clustered index?
- PostgreSQL tables are heap-organized by default, meaning rows are stored in insertion order, not sorted by any index.
- Creating an index (primary or secondary) does not automatically sort the table. Indexes point to the ctid of the rows in the heap.

## Mysql

### MVCC

The main goal of MVCC is to allow: Concurrent reads and writes without locking.


InnoDB tables have hidden system columns for every row:

| Column        | Description                                                               |
| ------------- | ------------------------------------------------------------------------- |
| `DB_TRX_ID`   | The ID of the transaction that last modified this row                     |
| `DB_ROLL_PTR` | A pointer to the **undo log record** for the previous version of this row |
| `DB_ROW_ID`   | A unique row ID (used internally if there’s no primary key)               |


### Transaction Ids
- InnoDB transaction IDs are sequential, monotonically increasing numbers.
- Modern MySQL uses 64-bit trx IDs
- wraparound is essentially impossible in normal use because its 64bit so possible max value of xid is enormous

### Commit Log

there is no separate commit log which track transactions (in progress, committed, rolledback, etc..) like postgres


### Redo Logs (Similar to WAL of postgres)

redo log is same idea of postgres WAL


### Undo Logs

store the previous versions of rows that are modified by transactions.

- it Provide older versions of rows for consistent reads. So other transactions can see a snapshot of the database as it was before your update.

- If a transaction rolls back, InnoDB uses the undo log to restore the row to its previous state.

- Cleaning: When no active transaction needs an old version, the purge thread removes it.


### What Happens on Create?

T1 creates a record … T1 trx_id = 101

Row (in buffer pool):
- trx_id = 101 … created by T1
- DB_ROLL_PTR → points to undo log (for rollback)
- row data = { user_id=1, post_id=42 }


Suppose T1 did not commit yet
- T1 runs: SELECT * FROM likes
    - Visibility check: row trx_id = 101 → created by T1 itself → show it
- T2 runs: SELECT * FROM likes
    - Visibility check: row trx_id = 101 → check in-memory transaction list → T1 still active / not committed → do not show it

Suppose T1 commit
- T2 runs: SELECT * FROM likes
    - Visibility check: row trx_id = 101 → check in-memory transaction list → T1 committed → show it


### What Happens on UPDATE?
- Old row is copied to undo log to
    - ability to rollback the heap to this version if transaction rollback
    - other transactions can see this record
- New row behavior:
    - if it can fill within page it will be updated in place
    - if it cant, then put it into another page, and put a pointer in the original location to point to the new location

Tip: InnoDB uses a clustered index, meaning rows are physically stored in primary key order, which helps maintain sequential organization even when row versions move between pages.

T2 updates a record … T2 trx_id = 102

Old Row:
- trx_id = 101 … created by T1
- DB_ROLL_PTR → points to undo log for old version
- row data = { user_id=1, post_id=42 }

New Row:
- trx_id = 102 … created by T2
- DB_ROLL_PTR → points to undo log for rollback if T2 aborts
- row data = { user_id=1, post_id=43 }

Suppose T2 is not committed yet
- T2 runs: `SELECT * FROM likes`
    - Visibility check: latest row with trx_id = 102 → created by T2 → show it

- T3 runs: `SELECT * FROM likes`
    - Finds two rows (trx_id 101 and 102)
    - Visibility check: latest row trx_id = 102 → T2 not committed → ignore
    - Fallback to older row trx_id = 101 → committed → show this row

lets suppose T2 committed
- T3 runs: `SELECT * FROM likes`
    - Latest row trx_id = 102 → committed → show this row

When purge purge thread runs
- delete old row versions by marking as reusable space
- Any old row with trx_id = aborted → undo log cleaned → trx_id reset or row discarded


## Q: it feels like mysql and postgres both are doing almost same thing in terms of mvcc
yes, they are the same.. The main differences are where old versions are stored (in-place vs undo log) and how commits are tracked internally.