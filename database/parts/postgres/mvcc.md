## POSTGRES


### MVCC

The main goal of MVCC is to allow: Concurrent reads and writes without locking.

POSTGRES tables have hidden system columns for every row:

| Column | Meaning                                                 |
| ------ | ------------------------------------------------------- |
| `xmin` | Transaction ID that created the row version             |
| `xmax` | Transaction ID that deleted/updated the row version     |
| `ctid` | Physical location of the row version                    |



### (this is the heart)

When a row is updated or deleted:
- Old version is kept in its location and just update its xmax
- New version is appended to the heap

- T1: insert a row:

    | Rows                                                    |
    | ------------------------------------------------------- |
    | ctid=(1,1), xmin = T1, xmax = null                      |


- T2: update the row:

    | Rows                                                    |
    | ------------------------------------------------------- |
    | ctid=(1,1), xmin = T1, xmax = T2                        |
    | ctid=(1,2), xmin = T2, xmax = null                      |


Tip: ctid is in form ctid(page_number, row_offset)


---


### Read View (transaction snapshot)

When a transaction starts:
- Postgres creates a Read View
- It contains:

| Field            | Meaning                                      |
| ---------------- | -------------------------------------------- |
| `creator_trx_id` | Transaction that owns the Read View          |
| `xip`            | List of transactions active at snapshot time |
| `xmin`           | Smallest active transaction ID               |
| `xmax`           | Next transaction ID to be assigned           |


This assist on querying rows visibility dynamically 


---

### How POSTGRES uses Read View (step-by-step)

When reading a row:
- Look at row’s xmin,xmax
- Compare it with current transaction Read View both xmin and xmax

Visibility Rules:

Checking xmin
| Row `xmin`              | Visible? | Why                       |
| ----------------------- | -------- | ------------------------- |
| Own transaction XID     | ✅ Yes    | Read your own writes      |
| `< snapshot.xmin`       | ✅ Yes    | Committed before snapshot |
| `>= snapshot.xmax`      | ❌ No     | Created after snapshot    |
| In `snapshot.xip[]`     | ❌ No     | Still active              |
| Not in `snapshot.xip[]` | ✅ Yes    | Committed before snapshot |


Checking xmax
| Row `xmax`              | Visible? | Why                     |
| ----------------------- | -------- | ----------------------- |
| `NULL`                  | ✅ Yes    | Not deleted             |
| Own transaction XID     | ❌ No     | You deleted it          |
| In `snapshot.xip[]`     | ✅ Yes    | Delete not committed    |
| `< snapshot.xmin`       | ❌ No     | Deleted before snapshot |
| Not in `snapshot.xip[]` | ❌ No     | Delete committed        |


### Q: is above table is for read committed or repeatable read??

same as mysql..

- READ COMMITTED: Snapshot created per statement
- REPEATABLE READ / SERIALIZABLE: Snapshot created once per transaction

--- 

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


Tip: wrap around of transaction id doesnt happen in mysql, since mysql choose its id to be 64 bit which is enormous
---

# Below Content to be reviewed later since its out of scope of mvcc



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
- WAL is split into segments (or log files).
- Database writes WAL entries sequentially into the current segment.
- When a segment fills up, the database moves to the next segment to continue writing.
- After certain time → checkpoint flushes dirty pages to data files
- When a WAL segment becomes older than the last checkpoint, and no replica needs it: WAL segment is recycled (reused)


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

### Q: Update a row in postgres, does that touch all indexes?
yes, Every index that includes a column affected by the update must also be updated. 

Even indexes not affected by the updated columns still store pointers to the row, so PostgreSQL needs to add a new entry pointing to the new tuple.

Old index entries pointing to the old tuple remain until vacuum cleans them up.

Tip: PostgreSQL 13+ has HOT updates (Heap-Only Tuple updates): If an UPDATE does not change indexed columns, PostgreSQL can sometimes avoid touching the indexes, writing only a new tuple in the heap. This improves performance significantly for updates that modify non-indexed columns.



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


### Q: is postgres work with clustered index?
- PostgreSQL tables are heap-organized by default, meaning rows are stored in insertion order, not sorted by any index.
- Creating an index (primary or secondary) does not automatically sort the table. Indexes point to the ctid of the rows in the heap.