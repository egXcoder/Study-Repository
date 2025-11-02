# Internals

## Row:
Each row is assigned a row identifier (row ID) 
- either user-defined (like a primary key in MySQL) 
- or system-defined (like a tuple ID in PostgreSQL).

## Pages:

The smallest unit of I/O in most databases. A page is a fixed-size chunk of data read from disk (not a single row). Example sizes:
- PostgreSQL: 8 KB
- MySQL (InnoDB): 16 KB

So when a database needs to read a single row, it actually loads the entire page containing that row into memory.


## I/O (Input/Output)

An I/O is one read/write operation between disk and memory. Disk I/O is expensive, so databases aim to: Minimize how many pages they read and Reuse pages already in memory (via caching). A single I/O usually fetches multiple rows at once (the whole page).

## Clustered Table
- table’s data itself is physically stored in the order of the index. (usually the primary key). 
- MySQL, SQL Server

## The Heap (Non-Clustered)

- data stored un-ordered. A full table scan means scanning all heap pages — which is slow. Hence the need for indexes to avoid scanning the entire heap. 
- PostgreSQL

## Indexes

An index is a separate data structure (stored on disk) that helps locate rows in the heap more efficiently. Most relational databases use B-trees as the underlying structure. Each index entry contains:
- The indexed value (e.g., employee_id = 40)
- A pointer (page + row ID) to the actual data in the heap

When you search for a record: 
- The database looks up the value in the index (I/O #1).
- It finds the page and row ID in the heap.
- It fetches that page from the heap (I/O #2) and extracts the row.
This is index lookup + heap lookup — faster than scanning the whole table.