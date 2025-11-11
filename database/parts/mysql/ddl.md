# DDL (Data Definition Language)

Schema amending

Mysql try as best as it can to make ddl operations are non-blocking, however Whether it blocks depends on the operation, not just the default.

MySQL will try to use ALGORITHM=INPLACE or ALGORITHM=INSTANT (MySQL 8.0+) when possible. otherwise it may use COPY


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