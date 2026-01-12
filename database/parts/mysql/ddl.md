# DDL (Data Definition Language) changes table structure, not data.


| ALGORITHM | Blocking?                                             |    Possible LOCKs |          
| --------- | -------------------------------                       | ----------------- |
| `INSTANT` | ✅ No blocking for reads or writes                    |    NONE only      |         
| `INPLACE` | ⚠️ Doesn't copy table                                 |    NONE or SHARED |
| `COPY`    | ❌ Fully blocking as it copy table to temporary table |    EXCLUSIVE only |

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








Mysql try as best as it can to make ddl operations are non-blocking, however Whether it blocks depends on the operation, not just the default.

MySQL will try to use ALGORITHM=INPLACE or ALGORITHM=INSTANT (MySQL 8.0+) when possible. otherwise it may use COPY



| `INSTANT` DDL Operation          |
| -------------------------------- |
| ADD COLUMN (NULL, no DEFAULT)    |
| ADD COLUMN with DEFAULT constant |
| RENAME COLUMN                    |
| SET DEFAULT / DROP DEFAULT       |


| `INPLACE` DDL Operation          |
| -------------------------------- |
| ADD COLUMN NOT NULL with DEFAULT |
| ADD INDEX                        |
| DROP INDEX                       |
| ADD FOREIGN KEY                  |
| DROP COLUMN                      |
| SET NOT NULL                     |


| `COPY` DDL Operation        |
| --------------------------- |
| MODIFY / CHANGE COLUMN TYPE |
| OPTIMIZE TABLE              |
| TRUNCATE TABLE              |