# Data Migration

## Why?

- You want to refactor your database:
    - Split a huge table into smaller, more manageable tables.
    - Change the schema (column types, collation) without downtime.

- Partitioning
    - Moving data from Large tables into smaller, partitioned table

- Migration to Another Database / Technology
    - Moving data to a new database engine (MySQL → Postgres, etc..)
    - Or moving specific tables to a different server for scaling or sharding.


## How?

### if table is small and you can go down for few minutes
- Down the software, then no more insert or update to the original table
- Copy data to the new table (`insert into new_table select * from old_table`) 
- rename new_table to be old_table and remove old_table

### if table is huge and you can go down for few minutes
- Bulk Copy: Copy existing rows from the old table to the new table in batches to avoid memory or DB overload.
- Delta migration: write cron jobs to run periodically and move last amended rows to the new table
- Cutover: pick a timing where usage is minimum. 
    - Down the software, then no more insert or update to the original table
    - Run delta migration one last time to pick up recent changes
    - rename new_table to be old_table and remove old_table (or link application with the new table)

### Zero-Downtime
- Bulk Copy: Copy existing rows from the old table to the new table in batches to avoid memory or DB overload.
- Delta migration: write cron jobs to run periodically and move last amended rows to the new table
- Cutover: pick a timing where usage is minimum.
    - do delta migration
    - enable a database trigger to mirror table changes into new table
    - do another delta migration
    - link your application with the new table

Tip: Triggers add write overhead. For high-volume tables, this may slow down inserts/updates till you move to the new table

Tip: Instead of triggers, and because of this write overhead even if for short period, large scale companies cant take it so some people use database replication or binlog listeners:how high-volume systems do zero-downtime migrations at scale. but require more setup than triggers


### Validation:

There are multiple ways to validate new table is exact as old table

- number of rows matches between the two tables.
- compare aggregate like sum(total) matches between the two tables
- random row checks.. take random 100 row from here and there and compare
- check for missing primary ids between the two tables
- hash checksum for rows between old table and new table
    ```sql
    SELECT BIT_XOR(CAST(CRC32(CONCAT(id,customer_code,...)) AS UNSIGNED)) AS checksum FROM orders where id between 1 and 1000;
    SELECT BIT_XOR(CAST(CRC32(CONCAT(id,customer_code,...)) AS UNSIGNED)) AS checksum FROM orders_new  where id between 1 and 1000;
    ```

Tip: whatever validation we do there must be a time window to do the check and if safe then we can switch to the new table

Tip: Even in “zero downtime” migration, there’s usually a micro-window (seconds) for the final catch-up and validation.

Tip: With truly zero downtime, you cannot fully validate a live table in one shot, because it is constantly changing.