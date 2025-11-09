# CAP Theorem

In a distributed system, you can only guarantee at most 2 out of these 3 properties simultaneously

- Consistency: Every read receives the most recent write (all nodes see the same data at the same time).
- Availability: Every request receives a response (success or failure) — the system never becomes “unavailable” to clients.
- Partition Tolerance: The system continues to operate even if there is a network partition (some nodes can’t talk to each other).

Tip: CAP does not apply to a single node DB → single machine has C + A + P naturally.


## Example
Imagine your database is replicated across 5 nodes
- Node 1 .. Primary
- Node 2 .. Replica
- Node 3 .. Replica
- Node 4 .. Replica
- Node 5 .. Replica

in a beautiful world, no network partition happens and all replicas are consistent and available

but assume a network partition occurs (node5 can’t communicate with node1). You must choose what to prioritize:
- Availability 
    - replicas 1 to 4 accept writes
    - replica 5 will be stale and have old state of data (inconsistency)
    - system is available though and keep replying
- Consistency
    - replicas 1 to 4 reject writes as 5 unreachable 
    - replicas 1 to 5 are consistent
    - there will be down time till node 5 get back connected (unavailability)

Tip:  Partition tolerance (P) is unavoidable in any multi-node setup. you may get nodes out service and you have to handle it either keep system working while having a replica with inconsistent state or down the system completely 


## Why?

CAP theorem is a design principle.. used for understanding trade-offs in distributed systems

When choosing or configuring a database, CAP helps you answer questions like:

Should my app prioritize availability or consistency?
    - Example: Facebook feed → small inconsistencies are okay → favor availability.
    - Example: Bank account → consistency is critical → favor consistency.

Imagine a group chat app with servers in different countries:

- Consistency → all users see the same messages, but some users may be temporarily unable to send messages if the network is slow.
- Availability → users can always send messages, but some people may see them slightly out of order.

CAP theorem explains why this trade-off exists.


## How?

## Mongo DB Default behavior
- system is up as long as majority is up .. majority = (no of nodes) / 2 + 1
    - if we have 1 primary node and 4 replicas .. majority = 3;

- if primary go down, replica from majority will get elected to be primary

- If a replica (Node 5) disconnects: Queries routed via a proxy will be sent to other available nodes

- If replica 5 gets back: you can configure if you want to read immediate from it or wait till it catch up

## Mysql/Postgres Async Replica Default
- system is up as long as primary is up
- if primary go down, no replica is elected by default
- If a replica (Node 5) disconnects: Queries routed via a proxy will be sent to other available nodes.
- if replica 5 is back, it start getting queries. so there is a chance to return stale data till it catch up


## Q: i think all of this is highly configured, so why they keep saying mongodb is better in scaling than mysql and postgres i can see mysql and postgres can do what mongo db do with little configuration?

MongoDB’s “scales better” reputation is mostly about default operational model and developer convenience, not that MySQL/Postgres cannot scale.

MySQL/Postgres often scale just as well once you put in the extra configuration and infrastructure, but it’s not automatic.