## BitMap Index Scan

A bitmap index scan is a PostgreSQL optimization technique that speeds up queries that use multiple conditions on indexed columns especially when those conditions match many rows (not just a few).

- Run: `EXPLAIN SELECT * FROM grades WHERE student_id = 1001 AND term = 'Fall 2024';`

- Thinking Options:
    - Sequential Scan → would read 1M rows — too slow.
    - Index Scan on student_id → fetches 50k rows then filters by term — okay.
    - Index Scan on term → fetches 300k rows then filters by student_id — worse.
    - ✅ Bitmap Index Scan (Best) → combines both indexes efficiently.


- Output:
```sql
Bitmap Heap Scan on grades  (cost=105.25..510.85 rows=200 width=40)
  Recheck Cond: ((student_id = 1001) AND (term = 'Fall 2024'))
  ->  BitmapAnd
        ->  Bitmap Index Scan on idx_student  (cost=0.00..45.00 rows=50000 width=0)
              Index Cond: (student_id = 1001)
        ->  Bitmap Index Scan on idx_term  (cost=0.00..60.00 rows=300000 width=0)
              Index Cond: (term = 'Fall 2024')
```

- Comment:
    - it will scan index term and get the pages which have rows in bitmap
    - it will scan index student_id and get the pages which have rows in bitmap
    - it will and these bits .. to get the page numbers which here and there
    - then will go to heap and fetch these pages

- Bitmap structure:
    - bits are per row and grouped by pages for quickly comparsion
    - if there are 1m record in database, potentially you will have 1m bits
    - If a page has no matching rows, it’s not even included in the bitmap! That saves a lot of memory.
    - Page 1: bits for 200 rows → 000101100010...
    - Page 2: bits for 200 rows → 100000010010...
    - Page 3: bits for 200 rows → ...


### In MySQL’s optimizer:
- No bitmap index scans. 
- Can only use one index per table per query block (in most cases).


## Key vs Non-Key Columns in PostgreSQL Indexes

### Typical index

`CREATE INDEX idx_students_grade ON students(grade);`

`SELECT id, grade FROM students WHERE grade BETWEEN 80 AND 95;`

- PostgreSQL uses the index to find matching grades and their tuple ids
- still has to go back to the table (the heap) to fetch id
- This is known as an Index Scan

### In Postgres Non-Key column added to index

Postgres allows adding “included columns” that are stored in the index, but not used for sorting/searching.

`CREATE INDEX idx_students_grade_inc_id ON students(grade) INCLUDE (id);`

`SELECT id, grade FROM students WHERE grade BETWEEN 80 AND 95;`

- Now the index contains everything the query needs — both grade and id — 
- so PostgreSQL can serve the query without touching the table.
- This is Known as Index Only Scan


### Creating Index On Production
- in `mysql 8`, by default creating index is `not blocking`
- in `postgres`: creating index is `blocking`, but you can do `CREATE INDEX CONCURRENTLY idx_customer ON customers(customer_id);`

How it works internally:
- Phase 1 → Scans table and builds the index in the background. Writes during this time are tracked.
- Phase 2 → Scans again to catch changes that happened while building. Finalizes and validates the index.
- This double scan is why it’s slower but non-blocking.



## Bloom Filter

Let’s imagine you’re running a service that stores millions of usernames.

You want to quickly check if a username exists without hitting the database each time (because that’s slow).

### Creating bloom filter:
- Create a bit array.. Example: 10 bits → [0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
- insert username into bloom filter .. h("Ali")%10 = 9
- bloom filter will be [0, 0, 0, 0, 0, 0, 0, 0, 1, 0]

### Query:
- is ali exists? h("Ali")%10 = 9 .. ali might exist .. its better if you query the database for it
- is ahmed exists? h("Ali")%10 = 8 .. ahmed doesnt exist .. no need to query database. i am sure doesnt exist.

Tip: if bloom filters are all filled with 1 bits, then its useless, for bloom filter to be useful you need to have 0s and 1s

Tip: bloom filters acts as cheap early exit instead of going and do the heavy work. its like.. does this value might exist? should i bother and dig more?


## UUID

- Databases like ordered inserts.
- Random UUIDs (v4) cause index fragmentation, page splits, and IO thrashing if used in clustered indexes
- Sequential or semi-ordered IDs (e.g. UUIDv1, UUIDv7, ULID, Snowflake) preserve temporal order while staying globally unique.

- If you must use UUIDv4:
    - Don’t make it the clustered primary key.
    - Use an autoincrement surrogate key or timestamp-based UUID for clustering.
    - Consider periodic REINDEX / OPTIMIZE TABLE to release fragmenation

- Tip: UUIDv4 in okay as long as it’s not your clustered index (primary key).

### Q: why uuidv4 shouldbe be used as clustered index?

assume there is empty table
- add record with uuid 11223344 .. it will be added to first page
- add record with uuid 44221341 .. it will be added to first page after first record
- now record with uuid 2234415  .. it will have to live with its data in middle between record 1 and record 2
- lets extract page and see can we have the three records and we are still dont exceed page? hopefully yes
- if not which is most likely to happen. now we have to split page to able to add record in between 

### Q: assume page 1, page 2, page 3, page4 .. how can i split page 2?
- Original Structure
    - Page 1: [ 001 ... 099 ]
    - Page 2: [ 100 ... 199 ]
    - Page 3: [ 200 ... 299 ]
    - Page 4: [ 300 ... 399 ]
- Allocate a new page (call it Page 5)
- Move half the rows from Page 2 into Page 5
- Update the parent node to insert a new pointer and key boundary.
    - Page 1: [ 001 ... 099 ]
    - Page 2: [ 100 ... 149 ]
    - Page 5: [ 150 ... 199 ]   <-- newly created
    - Page 3: [ 200 ... 299 ]
    - Page 4: [ 300 ... 399 ]

So Two Main Reasons:
- Inserting records is doing too much work unncessary because of possible page split
- all secondary indexes in mysql are storing primary index as well, and uuid size is big
- Now we lose the benefit of clustered index. when we query `select * from logs limit 100` 
    - if sequentially clustered, database brings page 1,2,3 in one i/o operation into memory which helps to bring data quickly
    - now, since page 5 is in middle of page 2,3 .. we loses the advantage of bringing multiple pages in one go.

Tip: when database bring records from table, it doesnt fetch one page. but fetches multiple pages physically next to each other in one go