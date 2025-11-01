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

## POSTGRES Tuple

A tuple = a row version (not just a row). Because of MVCC, a row may have multiple tuple versions alive at the same time.

Each tuple stores:
- The actual column data.
- Hidden system columns for MVCC and addressing:
    - ctid
    - xmin: transaction ID that created it
    - xmax: transaction ID that deleted/replaced it
    - tableoid (table OID)

### CTID (Current Tuple ID):
ctid = (page_number, tuple_index) = ctid (3, 17) .. Block (page) number = 3 , Tuple offset (slot) number = 17 within that block

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

### What Happens on Create?

T1 create a record .. T1 xid = 101..

Row:
- ctid(3,17)
- xmin = 101 .. created by 
- xmax = null
- row data here

### What Happens on UPDATE?
- It doesn’t modify the row in place. just marks the old one as expired (xmax)
- It creates a new tuple (new CTID)

T2 update record .. T2 xid = 102..

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






- All Indexes are updated to point to the new CTID.

Old tuple: ctid = (3, 17)
New tuple: ctid = (8, 2)

Tip:if you have many indexes on a table, and you are doing updates even for not indexed column. you are going to touch all indexes which is bad and unncessary

Tip: because updating records is essentially adding data, postgres can have bloating as old tuple versions stay until vacuumed by a postgres command

### Transaction Ids
- Transaction IDs are sequential 32-bit integers.. 
- Each new transaction that modifies data gets a Transaction ID (XID) = previous + 1
- there are about 4 billion possible XIDs.
- After roughly 4 billion transactions, PostgreSQL’s transaction ID counter wraps around to 0 again.

Why Wrap around can be dangerous?
- typically when we run a query like `select * from likes;` it is effectivly querying `select * from likes where xmin<=101` where 101 is the latest transaction id so far
- suppose the transaction id is 4 billion, all is good `select * from likes where xmin<=4b`.  
- when wrap around happens and transaction id is back to be 10 or something.. then `select * from likes where xmin<=10` is going to ignore alot of records 
- causing data corruption or visibility chaos. so there has to be a fix for it 

Freezing old tuples (Fix)
- When a tuple’s xmin refers to a transaction that committed long ago (and thus can be considered definitely visible to everyone), Postgres replaces its xmin with a special value called FrozenXID = 2.
- Frozen XIDs are treated as infinitely old, meaning they are visible to all transactions forever.

This happens during:
- Postgres runs autovacuum periodically, and one of its tasks is to freeze old tuples
- `VACUUM` command manually
- Or explicitly via `VACUUM FREEZE`
So freezing old tuples is essential maintenance for long-lived databases.


### Pros and Cons of Postgres or MMVC (multi version concurrent control) Model

Pros:
- Readers Don’t Block Writers (and vice versa)
    - PostgreSQL is famous for this — SELECT queries don’t block INSERT/UPDATE/DELETE.

- Each transaction works on its own version of the database, so it never sees “half-done” changes from others.

- Fewer Locks & Deadlocks
    - Because readers don’t take locks, lock contention is much lower. There are fewer cases of “deadlock detected” errors compared to traditional locking models.

Cons:
- Storage Bloat: 
    - Every update creates a new version of a row, which increase table size rapidly.

- Vacuum / Garbage Collection Overhead: 
    
    Dead tuples must be periodically cleaned up (in PostgreSQL by autovacuum). This process:
    - adds background I/O load
    - can fragment data and slow down sequential scans
    - needs careful tuning

- VACUUM Lag Can Hurt Performance:

    If autovacuum can’t keep up:
    - tables become bloated
    - queries get slower
    - disk usage spikes
    - transaction ID wraparound risks appear in PostgreSQL

- Index Maintenance Cost:
    - When a row version changes, all indexes pointing to it may need updates too, increasing write amplification.

- Visibility Checks Add Minor CPU Overhead:
    - Every read must check: whether a row’s xmin (created by tx) and xmax (deleted by tx) are visible This adds a little logic to each read. Usually minor — but measurable at scale.
