# DDL (Data Definition Language) changes table structure, not data.


| ALGORITHM | Blocking?                                             |    Possible LOCKs     |          
| --------- | -------------------------------                       | -----------------     |
| `INSTANT` | ✅ No blocking for reads or writes                    |    lock cant be used  |          
| `INPLACE` | ⚠️ Doesn't copy table                                 |    NONE or SHARED     |
| `COPY`    | ❌ Fully blocking as it copy table to temporary table |    EXCLUSIVE only     |

---

### Innodb vs MyIsam

- `innodb` instant and inplace algorithms are available
- `Myisam` only copy is available

to know if table is innodb or myisam

```sql
    SHOW TABLE STATUS LIKE 'sme_users';
```

---

### Q: why its available that i can state the algorithm in my query? yet in most cases it doesnt make sense that i do because its auto figured out

MySQL can auto-figure out the algorithm — but you cannot safely trust it in production. ALGORITHM exists to protect you.

Here’s the dangerous part:

```sql
ALTER TABLE users ADD COLUMN status INT NOT NULL DEFAULT 0;
```

You might think: “This should be inplace”

But MySQL decides: ALGORITHM = COPY LOCK = EXCLUSIVE

And it does not warn you by default. Result in production
- Table locks
- App hangs
- Pager goes off 🔥

This is why auto-decision is dangerous.

if you try 

```sql
ALTER TABLE users ADD COLUMN test INT NULL DEFAULT 0 ,ALGORITHM=INSTANT;
```

it will give you #1845 - ALGORITHM=INSTANT is not supported for this operation. Try ALGORITHM=COPY/INPLACE.

---

### Q: you can define lock as well like algorithm?

Yes — exactly 👍 In MySQL you can control two independent things in ALTER TABLE:
- HOW the change is executed → ALGORITHM
- WHO can access the table while it runs → LOCK

```sql
ALTER TABLE sme_users ADD COLUMN test INT NULL,ALGORITHM=COPY, LOCK=NONE;
```

#1846 - LOCK=NONE is not supported. Reason: COPY algorithm requires a lock. Try LOCK=SHARED.


#### Lock Options
| LOCK Option      | Meaning / Behavior                                                       |
| ---------------- | -----------------------------------------------------------------------  |
| `LOCK=NONE`      | Allows read/writes while altering the table.                             |
| `LOCK=SHARED`    | Table is locked for writes, but reads can continue.                      |
| `LOCK=EXCLUSIVE` | Table is fully locked: no reads or writes during operation.              |
| Not specified    | MySQL chooses automatically depending on the operation and algorithm.    |

---

### Instant (metadata only)

```sql
-- this will give error because lock is not allowed at all with instant
ALTER TABLE sme_chat_users ADD COLUMN test DATETIME NULL ,ALGORITHM=INSTANT, LOCK=NONE;

-- this will give error because indexing requires work on building the index tree
-- working with indexes can't be instant
ALTER TABLE sme_chat_users ADD INDEX idx_test(test) ,ALGORITHM=INSTANT;


-- adding null column at end of the table is instant since it will add it to meta data only
-- if column has default then table rows data need to be amended, and hence instant won't work
-- if column is not null and table have data then column must have default value
ALTER TABLE sme_chat_users ADD COLUMN test DATETIME NULL ,ALGORITHM=INSTANT;


-- rename column is instant, since it only change column name in meta data
ALTER TABLE sme_chat_users RENAME COLUMN test TO test2 ,ALGORITHM=INSTANT;


-- remove column by changing meta data only, so the way data is interpreted
-- but if you want to amend data itself to reduce disk space then you shouldnt use INSTANT 
ALTER TABLE sme_chat_users DROP COLUMN test2 ,ALGORITHM=INSTANT;


--adding null column in the middle, since it will amend metadata only
ALTER TABLE sme_chat_users ADD COLUMN nickname VARCHAR(50) NULL AFTER test ,ALGORITHM=INSTANT;
```

---


### INPLACE 

it can do all instant queries + the below

```sql
-- create secondary index as Index is built separately and rows aren’t rewritten
ALTER TABLE sme_chat_users ADD INDEX idx_test(test) ,ALGORITHM=INPLACE,LOCK=none;

-- drop index as index is separately
ALTER TABLE sme_chat_users DROP INDEX idx_test, ALGORITHM=INPLACE, LOCK=NONE;

-- create a column in the middle, amend rows data
ALTER TABLE sme_chat_users ADD COLUMN nickname VARCHAR(50) NULL AFTER test ,ALGORITHM=INPLACE, LOCK=NONE;

-- create a column with default have to amend rows data
-- this lock=none, doesnt stop rows lock.. it only stop table lock
ALTER TABLE sme_chat_users ADD COLUMN nickname VARCHAR(50) NULL Default 0 ,ALGORITHM=INPLACE, LOCK=NONE;
```

#### Q: is it safe for lock=none while creating the index? because if data added while creating the index that cause problems that built index is not consistent?
MySQL handles this internally using online index build mechanisms:
- InnoDB Online Index Build
- Build index in the background from the snapshot of existing rows
- Track changes (insert/update/delete) that happen during the build
- Apply all changes to the new index before making it visible
- Swap index in atomically once finished


#### Q: How Creating Index is inplace? is index is built in the same {table}.ibd file..

Yes. InnoDB indexes are stored as B+Trees, which consist of dynamically allocated pages. Each page contains pointers to its neighbors and internal nodes, so the index does not need to exist sequentially on disk.

- When a new index is created with ALGORITHM=INPLACE, MySQL allocates new pages in the same .ibd file.
- as adding/updating records happen.. Index pages may be physically scattered among table data pages, but the logical order is maintained by the B+Tree structure.
- In an ideal scenario, pages could be sequential for maximum disk locality after you optimize table maybe
- Even though pages are scattered, this does not significantly hurt lookups, because B+Tree traversal plus memory caching keeps searches efficient.

#### Q: how dropping index can be inplace?

InnoDB does:
- Mark the root of the B+Tree for that index as deleted in metadata
- Any new queries cannot see the index anymore
- Existing queries that are using the index continue safely until they finish
- InnoDB re-use the index pages of the B+Tree gradually

---

### COPY

it can do almost anything ... all the instant and inplace

```sql
-- change column data type require copy
ALTER TABLE sme_chat_users MODIFY COLUMN nickname BIGINT, Algorithm=COPY, LOCK=EXCLUSIVE;


-- optimize table require copy
OPTIMIZE table sme_chat_users;
```