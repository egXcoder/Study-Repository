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
yes, they are the same in core idea.. The main differences are where versions are stored

Mysql
- old version stored in undo log
- new version try to be in-place if row can fit in page otherwise be in different location and original location point to it 

Postgres
- old version kept in its location
- new version is inserted into heap appending