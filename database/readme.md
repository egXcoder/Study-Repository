# Database

## ACID [Explained Here](./parts/acid.md)

## Internals [Explained Here](./parts/internals.md)

## MVCC
- Why MVCC [Explained Here](./parts/whymvcc.md)
- Postgres [Explained Here](./parts/postgres/mvcc.md)
- Mysql [Explained Here](./parts/mysql/mvcc.md)

## Schema Amending
- Mysql [Explained Here](./parts/mysql/ddl.md)
- Postgres [Explained Here](./parts/postgres/ddl.md)


## Explain and Explain Analysis [Explained Here](./parts/explain.md)

## Indexes [Explained Here](./parts/indexes.md)

## Partitioning [Explained Here](./parts/partitioning.md)

## Sharding [Explained Here](./parts/sharding.md)

## Pooling [Explained Here](./parts/pooling.md)

## Replication [Explained Here](./parts/replication.md)

## Cursor [Explained Here](./parts/cursor.md)


## Data Migration [Explained Here](./parts/data_migration.php)


## Postgres
Pros over mysql:
- it can use multiple indexes on your select query while mysql always use one index
- it can use multiple worker threads to fetch the data and do operations which increase performance while mysql uses one thread
- It offers hash index which is optimized index for single lookup rather than B-tree which do range queries

- it has support for native cursor if you want to keep your memory minimum while iterate, mysql though using unbuffered query which database execute the query and hold result on its memory but send it to php piece by piece. however though i think server-side batching is better than using cursor



#### Replication:
- Replicas rely on primary db WAL which is being written anyway so it doesnt require extra disk space while mysql create another log called binary logs which tends to take more disk space


## Mysql
Pros over postgres:
- tends to use less disk space while postgress tends to consume more disk space however auto vacuum try to solve this
- if you ever want to do sharding, there is a mature tool called vitess which is the best tool out there to implement sharding
- every connection is a thread which is more lightweight and memory efficient while in postgres every connection is a process which consume cpu + more memory, but notice there should be a pooling anyway on high traffic websites then we dont keep close and reopen connections so overall its not the bad.
- range queries on a clustered index are fast because rows are stored physically together. In PostgreSQL, updates create new tuples, scattering rows across pages. This can make range queries slower, since sequential IDs may be on non-contiguous pages.

#### Amending Schema

MySQL will try to use ALGORITHM=INPLACE or ALGORITHM=INSTANT (MySQL 8.0+) when possible. Whether it blocks depends on the operation, not just the default.

| ALGORITHM | Blocking?                                       | Description                                                |
| --------- | ----------------------------------------------- | ---------------------------------------------------------- |
| `INSTANT` | ✅ No blocking for reads or writes               | Metadata-only change. (e.g., add a column without default) |
| `INPLACE` | ⚠️ Doesn't copy table, but **may block writes** | Reads allowed, writes may wait briefly.                    |
| `COPY`    | ❌ Fully blocking                                | Table copy to a new table; reads and writes blocked.       |


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



#### Replication:
- replicas follow `logical replication` using primary binary logs. replicas don't mimic exactly what happened in the primary but it replay the result which make replicas simpler and effective while in postgres replicas follow `stream physical replication` using WAL. so replicas mimic exactly what happened in the primary if you update a tuple in primary and that marked the old tuple and add a new tuple. replicas will do the same on their side which make replicas do more work than needed