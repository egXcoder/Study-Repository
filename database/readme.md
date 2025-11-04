# Database

## ACID [Explained Here](./parts/acid.md)

## Internals [Explained Here](./parts/internals.md)

## MVCC
- Why MVCC [Explained Here](./parts/whymvcc.md)
- Postgres [Explained Here](./parts/postgres/mvcc.md)
- Mysql [Explained Here](./parts/mysql/mvcc.md)

## Explain and Explain Analysis [Explained Here](./parts/explain.md)

## Indexes [Explained Here](./parts/indexes.md)



## Postgres is more powerful
- it can use multiple indexes on your select query while mysql always use one index
- it can use multiple worker threads to fetch the data and do operations which increase performance
- It offers hash index when you know you always going to do single lookup rather than range query