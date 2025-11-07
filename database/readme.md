# Database

## ACID [Explained Here](./parts/acid.md)

## Internals [Explained Here](./parts/internals.md)

## MVCC
- Why MVCC [Explained Here](./parts/whymvcc.md)
- Postgres [Explained Here](./parts/postgres/mvcc.md)
- Mysql [Explained Here](./parts/mysql/mvcc.md)

## Explain and Explain Analysis [Explained Here](./parts/explain.md)

## Indexes [Explained Here](./parts/indexes.md)

## Partitioning [Explained Here](./parts/partitioning.md)

## Sharding [Explained Here](./parts/sharding.md)

## Pooling [Explained Here](./parts/pooling.md)

## Replication [Explained Here](./parts/replication.md)


## Postgres
Pros over mysql:
- it can use multiple indexes on your select query while mysql always use one index
- it can use multiple worker threads to fetch the data and do operations which increase performance while mysql uses one thread
- It offers hash index which is optimized index for single lookup rather than B-tree which do range queries
- on replicas, it dont take more disk space as replicas rely on WAL which is being written anyway while mysql create another log called binary logs which tends to take more disk space


## Mysql
Pros over postgres:
- tends to use less disk space while postgress tends to consume more disk space however auto vacuum try to solve this
- if you ever want to do sharding, there is a mature tool called vitess which is the best tool out there to implement sharding
- every connection is a thread which is more lightweight and memory efficient while in postgres every connection is a process which consume cpu + more memory, but notice there should be a pooling anyway on high traffic websites then we dont keep close and reopen connections so overall its not the bad.