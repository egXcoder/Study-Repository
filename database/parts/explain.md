### Explain vs Explain Analyze
- Explain: shows expectation of how database plans to execute a query. It reveals which access method database will use (e.g., sequential scan, index scan, parallel scan) and how much work it expects to do.

- Explain Analyzie: do the actual work, then tell you how it actually done it


### Scans Methods
- Seq Scan: reads every row in the table. Happens when there’s no useful index or you select everything.
- Index Scan: uses an index to find rows, then goes to the heap (the table) to fetch data
- Index Only Scan: uses only the index — no need to go to heap — when all required columns are covered by the index.
- Parallel Seq Scan: PostgreSQL splits the work of scanning a large table across multiple processes.
- Bitmap index scan: is a PostgreSQL optimization technique that speeds up queries that use multiple conditions on indexed columns

### Examples:
- Ex 
    - Run: `EXPLAIN SELECT * FROM grades;`
    - Output: `Seq Scan on grades  (cost=0.00..289025.15 rows=12141215 width=31)`
    - Key Concepts:
        - Startup Cost 0.00: The estimated cost (in arbitrary units) before the first row can be returned
        - Total Cost: The estimated total cost to process the entire 
        - Rows: Estimated number of rows db expects to fetch.
        - Width: Estimated average size (in bytes) of each row.
- Ex
    - Run: `EXPLAIN SELECT * FROM grades ORDER BY g;`
    - Output: `Index Scan using idx_g on grades  (cost=0.43..289.00 rows=200000000 width=31)`

- Ex
    - Run: `EXPLAIN SELECT * FROM grades ORDER BY name;`
    - Output: 
    ```sql 
    Gather Merge  (cost=1000.00..999999.00 rows=200000000 width=31)
    Workers Planned: 4
    -> Sort  (cost=500.00..520.00 rows=50000000 width=31)
        Sort Key: name
        -> Parallel Seq Scan on grades  (cost=0.00..400.00 rows=50000000 width=31)
    ```
    - Comment:
        - Read from inner to top
        - first it will do parallel seq scan on grades table which means scan all heap by 4 threads
        - then it will sort by name .. which takes like 500 to 520 to sort it
        - then it will take 999999 as a cost to gather these data

- Ex
    - Run: `EXPLAIN SELECT * FROM grades WHERE g > 90;`
    - Output: `Index Scan using idx_g on grades  (cost=0.43..15.00 rows=500 width=31) Index Cond: (g > 90)`
    - Comment: it will lookup the index then go and fetch from heap

- Ex
    - Run: `EXPLAIN SELECT g FROM grades WHERE g > 90;`
    - Output: `Index Only Scan using idx_g on grades  (cost=0.43..15.00 rows=500 width=8) Index Cond: (g > 90)`


### BitMap Index Scan

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


In MySQL’s optimizer:
- MySQL (InnoDB) doesn’t have bitmap index scans. 
- Can only use one index per table per query block (in most cases).