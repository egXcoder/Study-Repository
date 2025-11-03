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