# Best Practices

## Use the smallest suitable type:
- TINYINT(1) for 0 or 1
- TINYINT instead of INT if values < 255.
- CHAR(2) for fixed-length
- VARCHAR(100) instead of TEXT when appropriate.
- ENUM for fixed string sets. but if ever set would be amended then its better to not use it


- Char or varchar
    - CHAR(32) always allocates space for 32 characters per row
    - If the stored string is shorter than 32 characters, MySQL pads it with spaces (not zeros) to the right when storing.
    - On retrieval, MySQL automatically trims the trailing spaces (except in some binary/string comparisons).

    - while varchar have some overhead logic to store length of word 

    - so if we are talking fixed length, its same space anyway either char or varchar but using char will reduce the overhead logic of varchar so this improve performance slightly


- varchar or text
    - varchar is stored within row space. row space has a maximum space cant be exceeded 
    - text is stored in separate page, so it doesnt contribute to row space. but of course harder to query since they live outside


- Use DATETIME vs TIMESTAMP carefully (TIMESTAMP is timezone-aware but limited to 1970–2038).

    - Timestamp stored in 4 bytes while datetime in 8 bytes

    - Timestamp '1970-01-01 00:00:01 UTC' → '2038-01-19 03:14:07 UTC' (due to Unix epoch)

    - Timestamp supports automatic CURRENT_TIMESTAMP on INSERT and UPDATE by default.

    ```sql
    CREATE TABLE example (
        ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        dt DATETIME DEFAULT CURRENT_TIMESTAMP /** this is datetime because in mysql < 5.6 Only one TIMESTAMP column per table could have: DEFAULT CURRENT_TIMESTAMP or ON UPDATE CURRENT_TIMESTAMP **/
    );
    ```

    - Timestamp Stored in UTC internally, automatically converted to current session timezone on retrieval.

    ```sql
    SET time_zone = '+00:00';
    INSERT INTO t VALUES ('2025-09-29 19:00:00'); -- TIMESTAMP stored as UTC

    SET time_zone = '+03:00';
    SELECT ts_column FROM t; -- Returns '2025-09-29 22:00:00'
    ```

    - If you need historical dates before 1970, use DATETIME.


Why This Matters

- Smaller types = smaller indexes → faster queries.
- Less disk space → less I/O → better performance.
- More rows fit into InnoDB pages / memory cache.

## Avoid Storing Files or Large Blobs:
- Don’t store images, PDFs, or videos directly in the database.
- Store them in S3 / filesystem and save only the path / URL in MySQL.


## Think About Character Set & Collation

Use utf8mb4 instead of utf8 (because MySQL’s utf8 is not real UTF-8).

``` sql 
CREATE DATABASE mydb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
/** Creating a table/column without specifying charset/collation would fall back to utf8 
and already existing columns wont change **/
```


## Choose Primary Key Strategy Wisely

- Prefer AUTO_INCREMENT INT/BIGINT for most tables.
- Consider UUIDs only if needed, but they are slower for indexing.
- Avoid using natural keys (like email, passport number) as primary keys.


## Use Soft Deletes Instead of Hard Deletes (Optional)

- Instead of deleting rows, add is_deleted TINYINT(1) or deleted_at DATETIME.
- Helps with auditability and accidental delete recovery.

## Logically Split Large Tables (Partitioning)

If a table grows beyond millions of records, consider partitioning by date, ID, or geography.
Example: logs table partitioned by month.

you have to say how many partitions you want to create explicitly


## Backup & Disaster Strategy

Set up automatic backups (mysqldump or XtraBackup).
Regularly test restore procedures.