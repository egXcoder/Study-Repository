# Apache MPM Workers (Multi-Processing Modules Workers)


## Know current MPM
`apachectl -V` know current mpm


## Available Workers
Apache has different MPMs that define how it handles concurrency:
- prefork → processes only, no threads (old, safe for non-thread-safe PHP).
- worker → multiple processes, each with multiple threads.
- event → like worker, but with smarter handling of idle keep-alive connections.

## Prefork
- Request -> Process

### Bottlenecks in Prefork
- Memory Usage
    - Each process is a full copy of Apache + modules + PHP interpreter.
    - If you have MaxRequestWorkers 200, that’s 200 heavyweight processes in memory.
    - 👉 Example:
    If one process ~50MB,
    200 processes = 10GB RAM.
    That’s huge compared to Worker/Event (which use threads).

- Concurrency
    - Prefork cannot serve more requests concurrently than the number of processes.
    - If MaxRequestWorkers = 50, only 50 users get responses simultaneously.
    - Extra requests pile up in the backlog queue (OS queue).

- CPU Overhead
    - Context switching between many processes is expensive.

- Slow with Keep-Alive
    - Prefork wastes a process for the entire Keep-Alive duration.
    - A client holding an idle connection (waiting between requests) ties up a whole process doing nothing.


### Architecture 
- auto spawn 5 processes at startup (StartServers = 5)
- always keep at least 5 processes to serve incoming requests (MinSpareServers = 5)
- dont keep more than 10 processes idle (MaxSpareServers = 10)
- dont serve more than 150 requests (MaxRequestWorkers = 150)
- when request finish and (MaxRequestsPerChild = 0) then process terminate .. if another value then process get back to pool

Tip: if we got traffic more than 150, linux kernel will keep it in queue which need to be delivered to the socket.. if os queue becomes full.. user may see “Connection reset by peer” or “Service unavailable”



## Event MPM
4 Processes + Each Process have 25 threads


### Why Event Better than Prefork
- Thread-based → much lighter than processes.
- Idle Keep-Alive connections don’t block threads: Event MPM puts them aside until real work comes in.
- lower memory footprint, higher concurrency.
- allows multi-plexing and hence http2 .. 

### Architecture
- Parent process never directly accepts or processes client traffic. It only creates the listening socket, forks children, and supervises.
- Parent forks N child processes
- Each child processes listen to socket to accept connections in parallel from os tcp backlog queue
- Connection is held by the event loop within process
- Request processing is done in worker threads

### Configuration
- ServerLimit = 4
- ThreadsPerChild = 25
- AsyncRequestWorkerFactor = 2
- MaxRequestWorkers = 100


Result:
- 4 Processes
- 25 thread per process
- `2 × 25 = 50` Each process can track idle connections (keep-alive)
- 100 max concurrent active requests Apache can handle overall


Tip: every process in the 4 process, has by default one thread (listener thread) which listen for new connections, so actually each process has 26 thread


### How Event Allow Multi-Plexing and http2
Since connection is held in a process and you can run threads in same time to serve this request and return back data once any of threads finish its work (a typical multi-plexing http2)


### How Event is Better in Keep-Alive Magic
- Worker MPM: A worker thread stays stuck as long as the connection is open (even if idle).
- Event MPM: The worker thread is released after finishing the request. The event loop continues watching the idle connection.
  - If the client sends another request, a worker thread is reassigned.
