## Mysql

### MVCC

The main goal of MVCC is to allow: Concurrent reads and writes without locking.


InnoDB tables have hidden system columns for every row:

| Column        | Description                                                               |
| ------------- | ------------------------------------------------------------------------- |
| `DB_TRX_ID`   | The ID of the transaction that last modified this row                     |
| `DB_ROLL_PTR` | A pointer to the undo log record for the previous version of this row |
| `DB_ROW_ID`   | A unique row ID (used internally if there’s no primary key)               |


### Undo Log (this is the heart)

When a row is updated or deleted:
- Old version is written to the undo log
- New version is written to the data page
- New Version keeps a pointer to the old Version

This creates a version chain:
current row → undo → undo → undo → ...


### Read View (transaction snapshot)

When a transaction starts:
- InnoDB creates a Read View
- It contains:

| Field            | Meaning                                      |
| ---------------- | -------------------------------------------- |
| `creator_trx_id` | Transaction that owns the Read View          |
| `trx_ids`        | List of transactions active at snapshot time |
| `low_limit_id`   | Smallest active transaction ID               |
| `up_limit_id`    | Next transaction ID to be assigned           |


This assist on querying rows visibility dynamically 


### How InnoDB uses Read View (step-by-step)

When reading a row:
- Look at row’s DB_TRX_ID
- Compare it with current transaction Read View

Decide:

Visibility Rules
| Row trx id       | Visible? | Why                                 |
| ---------------- | -------- | -------------------------           |
| Own transaction  | ✅ Yes    | “Read your own writes”             |
| `< low_limit_id` | ✅ Yes    | Committed before snapshot          |
| `>= up_limit_id` | ❌ No     | Created after snapshot             |
| In `trx_ids`     | ❌ No     | Still active                       |
| Not In `trx_ids` | ✅ Yes    | Committed while transaction working|


### Q: is above table is for read committed or repeatable read??

its for both, but with tiny different

- [-] READ COMMITTED: whenever you do a select it will create a read view

    - T1: do a select .. it will create a read view
    ```js
    {
        creator_trx_id:1000,
        low_limit_id:999,
        up_limit_id:1002,
        active_trx_ids:{999,1000,1001,1002}
    }
    ```

    - T2,T3 which are 999 and 1001 update data and commit

    - now when T1: do another select .. it will create a new read view
    ```js
    {
        creator_trx_id:1000,
        low_limit_id:1002,
        up_limit_id:1005,
        active_trx_ids:{1002,1003,1004,1005}
    }
    ```

    - T1 will read data amended by T2 and T3 since T2,T3 are < low_limit_id


- [-] REPEATABLE READ: one read view per transaction

    - T1: do a select .. it will create a read view
    ```js 
    {
        creator_trx_id:1000,
        low_limit_id:999,
        up_limit_id:1002,
        active_trx_ids:{999,1000,1001,1002}
    }
    ```

    - T2,T3 which are 999 and 1001 update data and commit

    - now when T1: do another select .. it will use same read view

    - T1 will not read data amended by T2,T3 as they are inside active_trx_ids

<br>

Tip: Transaction Ids are sequential, monotonically increasing numbers. wraparound is impossible in normal use because its 64bit so possible max value of xid is enormous

---

### Q: it feels like mysql and postgres both are doing almost same thing in terms of mvcc
yes, they are the same in core idea.. The main differences are where versions are stored

Mysql
- old version stored in undo log
- new version try to be in-place if row can fit in page otherwise be in different location and original location point to it 

Postgres
- old version kept in its location
- new version is inserted into heap appending

---

### Undo Log Vs Redo Logs

- Undo log = (rollback & MVCC)
- Redo log = (crash recovery & durability)


Undo Log — detailed:
- Logical record of old row versions
- Stored in undo tablespace
- Linked via DB_ROLL_PTR
- Used for
    - ROLLBACK
    - MVCC consistent reads
    - DELETE/UPDATE visibility

Redo Log — detailed

- Physical log of page changes
- Sequential, append-only
- Very fast to write
- Used for
    - Crash recovery
    - Durability (ACID D)

```text
Change page 42:
offset 128 → write value 200
```

