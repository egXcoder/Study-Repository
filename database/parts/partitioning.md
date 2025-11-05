# Partitioning


Partitioning is when a single logical table is split into smaller physical pieces (partitions).


## Why Partitioning Exists
- Partitioning exists to handle very large tables efficiently.
- When a table grows into hundreds of millions or billions of rows, several problems appear:
- Indexes become huge and slow to maintain.
- Scanning becomes expensive.
- Vacuuming / statistics collection becomes heavy.
- Backup or restore operations take forever.

select * from orders where created_at>'2025-01-01' and id > 20000

## Query

Tip: you should use `where partition_key >= 'xyz'` in all your queries .. then you can benefit from partitioning

Tip: `WHERE created_at >= '2024-01-01'` works.. while `WHERE YEAR(created_at) = 2024` Does NOT prune partitions

Tip: 
- `PRIMARY KEY (created_at,id)` index in each partition is sorted by (created_at, id)
- `PRIMARY KEY (id,created_at)` index in each partition is sorted by (id, created_at)
- if most of the times you are going to do random lookup by id then better to use `PRIMARY KEY (id,created_at)`
- if most of the times you are going to do range query on created_at then use `PRIMARY KEY (created_at,id)`


## Partitioning Types

### Range Partitioning

```sql

CREATE TABLE orders (
  id INT,
  customer VARCHAR(50),
  created_at DATE,
  PRIMARY KEY (id,created_at)   -- ✅ PRIMARY KEY includes partition column
)
PARTITION BY RANGE (YEAR(created_at)) (
  PARTITION p2022 VALUES LESS THAN (2023),
  PARTITION p2023 VALUES LESS THAN (2024),
  PARTITION pmax VALUES LESS THAN MAXVALUE
);

```

Tip: PRIMARY KEY (id,created_at) .. if you want to select by id

select * from orders where created_at>'2025-01-01' and id > 20000

### List Partitioning

```sql

CREATE TABLE customers (
  id INT,
  name VARCHAR(50),
  country_code CHAR(2),
  PRIMARY KEY (id,country_code)
)
PARTITION BY LIST COLUMNS (country_code) (
  PARTITION p_uk VALUES IN ('UK'),
  PARTITION p_us VALUES IN ('US'),
  PARTITION p_others VALUES IN ('EG', 'FR', 'IT')
);

```


### Hash Parition

Only For Integer columns

```sql

CREATE TABLE sessions (
  session_id INT PRIMARY KEY,
  user_id INT,
  PRIMARY KEY (session_id,user_id)
)
PARTITION BY HASH (user_id)
PARTITIONS 4;

```




### Key Partition

For Integer or String Columns

```sql

CREATE TABLE customers (
  id INT,
  name VARCHAR(50),
  country_code CHAR(2),
  PRIMARY KEY (id,country_code)
)
PARTITION BY KEY(country_code)
PARTITIONS 4;

```
Tip: same as Hash but for string columns


Tip: `insert into `orders` values(1,'b',null)` wont work.. because parition key must always have value

## Mysql Restrictions


### No Global Indexes

All indexes are per partition, not global across the entire table.

That’s why the unique key restriction exists.


### Primary/Unique Indexes problem

MySQL's partitioning architecture is "partition first → index second"

When you insert a row:
- MySQL determines which partition to store it in (based only on the partition key)
- Inside that partition, MySQL stores local indexes which do the validation


### Problem
```sql

CREATE TABLE users (
  id INT,
  email VARCHAR(100),
  signup_date DATE,
  UNIQUE (email)
)
PARTITION BY RANGE (YEAR(signup_date));

```
You insert this row:
id	signup_date  email	         
1	2023-02-01	 ahmed@gmail.com       

Mysql asks:
- which partition? it will be p2023
- is it unique in partition p2023? yes, okay add it

Now insert this:
id	signup_date  email
1	2024-01-01	 ahmed@gmail.com

Mysql asks:
- which partition? it will be pmax
- is it unique in partition pmax? yes, okay add it

We end up with two records of same email. which violates the Unique constrain in the big table


### Solution

Mysql could have redesigned partitioning to allow global state. so on creating the index it would create the index on each partition and in same time it would create it globally but mysql felt its too much work and it will take more space and we rather keep it simple.

Mysql has decided, lets force developers to include partition key in unique index like the below

```sql

CREATE TABLE users (
  id INT,
  email VARCHAR(100),
  signup_date DATE,
  UNIQUE (email,signup_date)
)
PARTITION BY RANGE (YEAR(signup_date)) (
  PARTITION p2022 VALUES LESS THAN (2023),
  PARTITION p2023 VALUES LESS THAN (2024),
  PARTITION pmax VALUES LESS THAN MAXVALUE
);

```

in my opinion: its bad and restrictive approach and it points to shallow implementations of partitioning in mysql

Tip: primary key are considered unique keys as well, so partition key has to be included in primary key

Tip: you can create ordinary secondary index as long as not unique with no issue
  - in mysql, it will create index on each partition
  - in postgres, it will create index on each partition + globally


## Q: when would i favour parition over composite key?

Both partitioning and indexing improve query performance — but they solve different problems.

### Composite Index
✅ Best when:
- Table size is moderate (hundreds of thousands or a few million rows).
- You query within the same dataset repeatedly `SELECT ... FROM logs WHERE created_at >= '2025-01-01';`
- You mostly care about query speed, not data management.

### Partitioning

✅ Best when:
- Table is very large (multi-million → billions rows).
- You frequently query using time ranges: `SELECT ... FROM logs WHERE created_at >= '2025-01-01';`
- Only relevant partitions are scanned. Others are skipped completely. (Partition pruning)
- You need to drop or archive old data quickly: `ALTER TABLE logs DROP PARTITION p2022;` This is instant, whereas DELETE on a huge table is slow and creates bloat. Helps with maintenance (REINDEX, VACUUM, ANALYZE per partition).

## Q: do you think is it better to create partition from start if i feel table is going to be multi million like logs? or wait till its multi million row?

If you know the table will grow continuously and indefinitely (like logs), partition it from the start.

🛑 Why not wait until it becomes huge, Once you hit tens of millions of rows:
- Migration requires creating a partitioned table and moving data. which will block writes for hours till you migrate
