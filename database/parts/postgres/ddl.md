# DDL (Data Definition Language)

Schema amending

PostgreSQL prioritizes data correctness, so most DDL operations take strong locks (ACCESS EXCLUSIVE). Some operations can be partially non-blocking using CONCURRENTLY.

| Non-Blocking                              |
| ----------------------------------------- |
| ADD COLUMN (NULL, no DEFAULT)             |
| ADD COLUMN with DEFAULT constant (PG 11+) |
| SET DEFAULT / DROP DEFAULT                |
| RENAME COLUMN                             |
| RENAME TABLE                              |


| Non-Blocking Index Operation  |
| ----------------------------- |
| CREATE INDEX CONCURRENTLY     |
| DROP INDEX CONCURRENTLY       |
| REINDEX CONCURRENTLY (PG 12+) |


| Fully Blocking                                         |
| ------------------------------------------------------ |
| MODIFY / CHANGE COLUMN TYPE                            |
| DROP COLUMN                                            |
| ADD NOT NULL constraint (without DEFAULT)              |
| ADD FOREIGN KEY                                        |
| ADD UNIQUE / PRIMARY KEY  / Index                      |
| ALTER TABLE … SET DATA TYPE (if requires rewrite)      |
| TRUNCATE TABLE                                         |
| VACUUM FULL / CLUSTER / REINDEX (without CONCURRENTLY) |
