## Persistence

Redis has two main persistence options:

- RDB (Snapshotting) → Saves a snapshot of the database at intervals.
- AOF (Append Only File) → Logs every write command for durability.


## RDB (Snapshotting)

### configuration within redis.conf
```nginx 
save 900 1  # If at least 1 key was modified in the last 900 seconds (15 mins) → Take an RDB snapshot
save 300 10  # If 10 or more keys changed within 5 minutes → Take a snapshot
save 60 10000  # If 10,000+ keys changed within 1 minute → Take a snapshot quickly
```

#### Q: Why Multiple Conditions?
- It gives Redis flexibility:
    - If only a few changes happened → wait longer before saving.
    - If many changes happen fast → save sooner to avoid losing too much data.


## AOF (Append-Only File):

### Configuration within redis.conf
```nginx 
appendonly yes        # Enables AOF persistence
appendfsync everysec  # Balanced performance + durability
```
### appendfsync mode
- everysec: Flush to disk every second .. Recommended
- always: Write every command to disk immediately .. slowest but safest
- no : Let OS decide .. Fastest but less safe

## Questions:

### Q: What are the best practices of using persistence?
it depends on the use cases:
- Cache-only (data should be in memory only)                   .. ❌RDB   ❌AOF
- Sessions / Authentication / Queues (Web apps)                .. ✅RDB   ✅AOF (with everysec)
- High-Durability Data (e.g. financial counters, chat history) .. ✅ RDB  ✅AOF (always)

Tip: RDB and AOF are both enabled in same time as best practice for AOF = safety, RDB = fast recovery + insurance policy.


### Q: AOF keeps appending to a file forever… will it grow infinitely?

By default, yes — the AOF file will continuously grow as Redis writes each operation to it. But Redis have built-in mechanisms to shrink (rewrite) it automatically called AOF Rewrite

```nginx
#inside redis.conf
auto-aof-rewrite-percentage 100 # If the AOF file size doubles since last rewrite → Compact it
auto-aof-rewrite-min-size 64mb # Don’t trigger a rewrite until file is at least 64MB
```

✅ Example of AOF Lifecycle
- Start Redis ... 1MB AOF File Size
- After 10,000 writes ... 100 MB
- Rewrite triggered → New compact AOF ... 5MB
- Keeps growing again until next rewrite

### Q: what does AOF Rewrite do to the file?
- imagine you have used redis like this
    ```nginx
    SET count 1
    INCR count
    INCR count
    INCR count
    SET user:1 "John"
    SET user:1 "Johnny"
    ```

- The AOF file would contain all of these commands, which might look like:
    ```nginx
    SET count 1
    INCR count
    INCR count
    INCR count
    SET user:1 "John"
    SET user:1 "Johnny"
    ```
- After AOF Rewrite: Redis analyzes the current state of the database (not the history), and writes only what’s needed to recreate that state:
    ```nginx
    SET count 4
    SET user:1 "Johnny"
    ```
