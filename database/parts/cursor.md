# Cursor

## Problem:

below query is going to fetch all student_id from database, allocate them in database memory, then send them to the application through the network. then application will hold them in memory as well.

if data is huge, 
- you will consume alot of memory
- you will consume the network as well between database and the backend

`SELECT student_id FROM grades WHERE grade BETWEEN 90 AND 100;`

## Fix:
you have to chunk the data to get them chunk per chunk rather than get all now. to do that you can use cursors. either in server side or client side. 


## Client Side Cursor:

- is redudant and not useful really, database gets all result in memory and send all to php process. 
- php process would iterate over them using cursor. 
- not useful as doesnt solve memory footprint issue and also i already can loop through the data using collections and foreach and other things


## Server Side Cursor:


### Native Cursor (Postgres only)

```sql

BEGIN; -- Required

DECLARE c CURSOR FOR SELECT student_id FROM grades WHERE grade BETWEEN 90 AND 100;

FETCH 100 FROM c;  -- returns only 100 rows
FETCH 100 FROM c;  -- next 100
-- ...
CLOSE c;
COMMIT; --Required

```

Pros:
- native database cursor following sql standards
- By default, PostgreSQL uses Read Committed isolation, but for cursors, it often behaves like snapshot at the start of the transaction. so if new rows inserted/updated, cursor won't see it

Cons:
- you cannot reuse the same DB connection for something else until iteration finishes.
- Long-running transaction holds MVCC snapshot (Postgres) or undo log entries (MySQL). which prevent cleaning of old versions to be effective
- DDL operations like ALTER TABLE may be blocked until the transaction ends.


### Unbuffered Query (Mysql Only)

the closest idea of cursor in mysql is unbuffered queries

#### Buffered Query (Default) : 

query results are immediately transferred from the MySQL Server to PHP and then are kept in the memory of the PHP process.

#### Unbuffered Query:

Unbuffered MySQL queries execute the query build the data set in mysql memory and then wait to be fetched by the php process one by one. This uses less memory on the PHP-side, but can increase the load on the server.

```php

$pdo = new PDO("mysql:host=localhost;dbname=world", 'my_user', 'my_password');
$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

$unbufferedResult = $pdo->query("SELECT Name FROM City");
foreach ($unbufferedResult as $row) {
    echo $row['Name'] . PHP_EOL;
}

```

### Laravel Implementation for Server side cursor

below php code, if run on 
- postgres it will use native cursor
- mysql it will use the mysql unbuffered query 

```php
foreach (Grade::whereBetween('grade', [90, 100])->cursor() as $grade) {
    echo $grade->student_id . " => " . $grade->grade . PHP_EOL;
    // Process the row here
}
```

```php

foreach (DB::table('grades')->where('grade', '>=', 90)->lazy() as $row) {
    // process row one by one
}

foreach (DB::table('grades')->lazy(500) as $row) {
    // process 500 rows at a time internally
}

```

Tip: lazy() is a wrapper around cursor() to give you extra power on using server side cursor


## Server-Side Batching

- Instead of fetching all rows at once (client-side) or one row at a time (cursor), the database sends a batch of N rows per request.
- The client processes the batch and then requests the next batch.
- Reduces client memory usage but avoids the overhead of fetching row by row.


### Pros:
- Lower memory usage compared to client-side buffered queries.
- More efficient than fetching row by row (reduces round trips).
- Works well for ETL jobs, exporting large tables, or APIs returning paginated results.

### Cons:
Offset-based performance issues (large tables)
- By default, Laravel implements chunk() using LIMIT/OFFSET queries:
- `SELECT * FROM grades ORDER BY id LIMIT 100 OFFSET 1000;`
- For very large tables, OFFSET N is slow, because the database still has to skip N rows internally.
- Alternative: use chunkById() to iterate using the primary key, which avoids this problem.
- Also chunkById() Ensures newly inserted rows are not included, since it iterate using the primary key



```php

DB::table('grades')->where('grade', '>=', 90)->chunk(100, function ($rows) {
    foreach ($rows as $row) {
        // process 100 rows at a time
    }
});

```

Tip: Client-side batching is when database give you all data and you put it in a collection and you process them in batches


## Q: Which is the best for data-migration?
- server-side batching is the best for data migration it balance between memory usage and processing
- if its critical for your memory to be minimum then you should go for native cursor in postgres
 
Tip: mysql cursor is not a good idea because MySQL loads the entire result set into memory on the server side when you declare a cursor. Fetching rows one by one does not reduce server memory usage, because the full result set is already held in memory.